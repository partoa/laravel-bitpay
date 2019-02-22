<?php

namespace Vrajroham\LaravelBitpay\Commands;

use Bitpay\Bitpay;
use Bitpay\SinKey;
use Bitpay\PublicKey;
use Bitpay\PrivateKey;
use Vrajroham\LaravelBitpay\Network\Livenet;
use Vrajroham\LaravelBitpay\Network\Testnet;
use Illuminate\Console\Command;
use Bitpay\Client\BitpayException;
use Bitpay\Client\Adapter\CurlAdapter;
use Bitpay\Client\Client as BitpayClient;
use Vrajroham\LaravelBitpay\Traits\CreateKeypairTrait;

class CreateKeypair extends Command
{
    private $config;
    private $privateKey;
    private $publicKey;
    private $storageEngine;
    private $sin;
    private $client;
    private $network;
    private $adapter;
    private $pairingCode;
    private $pairingCodeLabel = 'Laravel_BitPay';
    private $token;
    private $bar;
    use CreateKeypairTrait;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-bitpay:createkeypair { pairingcode : Pairing code created on BitPay server.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create and persist keypair. Pair client with BitPay server.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->bar = $this->output->createProgressBar(10);

        $this->bar->setProgressCharacter('⚡');
        $this->bar->setBarCharacter('-');
        $this->bar->setEmptyBarCharacter(' ');

        $this->validateAndLoadConfig();

        $this->createAndPersistKeypair();

        $this->pairWithServerAndCreateToken();

        $this->writeNewEnvironmentFileWith();

        $this->laravel['config']['laravel-bitpay.token'] = $this->token;

        $this->info(" 🎉 Client paired with BitPay and token [$this->token] generated successfully. ⛳");
    }

    /**
     * Create private key and public key. Store keypair in file storgae.
     */
    public function createAndPersistKeypair()
    {
        $this->bar->advance();
        $this->info(' - Generating private key.');

        $this->privateKey = new PrivateKey($this->config['private_key']);
        $this->privateKey->generate();

        $this->bar->advance();
        $this->info(' - Private key generated.');

        $this->bar->advance();
        $this->info(' - Generating public key.');

        $this->publicKey = new PublicKey($this->config['public_key']);
        $this->publicKey->setPrivateKey($this->privateKey);
        $this->publicKey->generate();

        $this->bar->advance();
        $this->info(' - Public key generated.');

        if (in_array('__construct', get_class_methods($this->config['key_storage']))) {
            $this->storageEngine = new $this->config['key_storage']($this->config['key_storage_password']);
        } else {
            $this->storageEngine = new $this->config['key_storage']();
        }

        $this->bar->advance();
        $this->info(' - Using '.get_class($this->storageEngine).' for secure storage.');

        $this->storageEngine->persist($this->privateKey);
        $this->storageEngine->persist($this->publicKey);

        $this->bar->advance();
        $this->info(' - Keypairs stored securly.');
    }

    /**
     * Create token on server using generated keypairs and pair the client with server using pairing code.
     *
     * @throws \Bitpay\Client\BitpayException
     */
    public function pairWithServerAndCreateToken()
    {
        $this->sin = SinKey::create()->setPublicKey($this->publicKey)->generate();

        $this->bar->advance();
        $this->info(' - Created Service Identification Number (SIN Key) for client.');

        $this->client = new BitpayClient();

        if ('testnet' == $this->config['network']) {
            $this->network = new Testnet();
        } elseif ('livenet' == $this->config['network']) {
            $this->network = new Livenet();
        } else {
            $this->network = new $this->config['network']();
        }

        $this->adapter = new CurlAdapter();

        $this->client->setPrivateKey($this->privateKey);
        $this->client->setPublicKey($this->publicKey);
        $this->client->setNetwork($this->network);
        $this->client->setAdapter($this->adapter);

        $this->bar->advance();
        $this->info(' - BitPay client ready to pair.');
        $this->pairingCode = trim($this->argument('pairingcode'));

        $this->bar->advance();
        $this->info(' - Pairing with BitPay server.');

        try {
            $this->pairingCodeLabel = config('app.name').'_BitPay_Client';
            $newToken = $this->client->createToken([
                    'pairingCode' => $this->pairingCode,
                    'label' => $this->pairingCodeLabel,
                    'id' => (string) $this->sin, ]
            );
        } catch (BitpayException $bitpayException) {
            throw $bitpayException;
        }

        $this->bar->finish();
        $this->info(' - Client successfully paired with server');
        $this->token = $newToken->getToken();
    }
}
