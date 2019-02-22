<?php
/**
 * Created by PhpStorm.
 * User: ishq
 * Date: 2/22/19
 * Time: 9:25 AM
 */

namespace Vrajroham\LaravelBitpay\Network;


interface NetworkInterface
{
    /**
     * Name of network, currently on livenet and testnet
     *
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getAddressVersion();

    /**
     * The host that is used to interact with this network
     *
     * @see https://github.com/bitpay/insight
     * @see https://github.com/bitpay/insight-api
     *
     * @return string
     */
    public function getApiHost();

    /**
     * The port of the host
     *
     * @return integer
     */
    public function getApiPort();
}

