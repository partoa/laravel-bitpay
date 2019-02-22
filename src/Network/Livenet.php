<?php
/**
 * Created by PhpStorm.
 * User: ishq
 * Date: 2/22/19
 * Time: 9:26 AM
 */

namespace Vrajroham\LaravelBitpay\Network;
use Bitpay\Network\NetworkInterface;

class Livenet implements NetworkInterface
{
    public function getName()
    {
        return 'livenet';
    }

    public function getAddressVersion()
    {
        return 0x00;
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

