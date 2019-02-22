<?php
/**
 * Created by PhpStorm.
 * User: ishq
 * Date: 2/22/19
 * Time: 9:26 AM
 */

namespace Vrajroham\LaravelBitpay\Network;
use Bitpay\Network\NetworkInterface;


class Testnet implements NetworkInterface
{
    public function getName()
    {
        return 'testnet';
    }

    public function getAddressVersion()
    {
        return 0x6f;
    }

    public function getApiHost()
    {
        return 'btcpay.sasalog.com';
    }

    public function getApiPort()
    {
        return 443;
    }
}

