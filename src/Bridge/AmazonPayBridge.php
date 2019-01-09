<?php
/**
 * Created by PhpStorm.
 * User: beathan
 * Date: 01.11.2018
 * Time: 21:09
 */

namespace Bthn\SyliusAmazonPayPlugin\Bridge;

use AmazonPay\Client;
use Sylius\Component\Channel\Context\ChannelContextInterface;

class AmazonPayBridge implements AmazonPayBridgeInterface
{

    private $config;

    private $hostname;
    private $channelName;


    /** @var Client $client */
    private $client;

    /**
     * AmazonPayBridge constructor.
     * @param $config
     */
    public function __construct(ChannelContextInterface $context)
    {
        $this->hostname = $context->getChannel()->getHostname();
        $this->channelName = $context->getChannel()->getName();
    }

    /**
     * {@inheritDoc}
     */


    public function validateAuthToken(string $auth_token): bool
    {

    }

    public function authorizePayment(string $amazonOrderId, string $amount, string $currencyCode)
    {
        $requestParameters = array();
        $requestParameters['currency_code'] = $currencyCode;
        $requestParameters['transaction_timeout'] = 0;
        $requestParameters['amazon_order_reference_id'] = $amazonOrderId;
        $requestParameters['authorization_reference_id'] = $amazonOrderId;
        $requestParameters['authorization_amount'] = $amount;
        $requestParameters['capture_now'] = true;
        $response = $this->client->authorize($requestParameters);

        return $response;
    }

    public function confirmOrder(string $amazonOrderId)
    {
        $requestParameters = array();
        $requestParameters['amazon_order_reference_id'] = $amazonOrderId;
        $response = $this->client->confirmOrderReference($requestParameters);

        return $response;
    }

    public function setOrderAmount(string $amazonOrderId, string $seller_order_id, string $amount, string $currencyCode)
    {


        $requestParameters = array();
        $requestParameters['store_name'] = $this->channelName;
//        $requestParameters['seller_note'] = $seller_order_id;
        $requestParameters['seller_order_id'] = $seller_order_id;
        $requestParameters['amazon_order_reference_id'] = $amazonOrderId;
        $requestParameters['amount'] = $amount;
        $requestParameters['currency_code'] = $currencyCode;
        $requestParameters['request_payment_authorization'] = true;
        $response = $this->client->setOrderReferenceDetails($requestParameters);

        return $response;
    }


    public function getOrderInfo(string $amazonOrderId, string $token)
    {
        $requestParameters = array();
        $requestParameters['amazon_order_reference_id'] = $amazonOrderId;
        $requestParameters['address_consent_token'] = $token;
        $response = $this->client->getOrderReferenceDetails($requestParameters);

        return $response;
    }

    public function setAuthorizationDataApi($sandbox_env, $merchant_id, $access_key, $secret_key, $amzn_login, $region)
    {
        $this->config['sandbox'] = ($sandbox_env === 'true') ? true : false;
        $this->config['merchant_id'] = $merchant_id;
        $this->config['access_key'] = $access_key;
        $this->config['secret_key'] = $secret_key;
        $this->config['client_id'] = $amzn_login;
        $this->config['region'] = $region;

        $this->client = new Client($this->config);
        $this->client->setSandbox($this->config['sandbox']);
    }

    /**
     * {@inheritDoc}
     */
    public function create($order)
    {

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function retrieve($orderId)
    {
//        return \OpenPayU_Order::retrieve($orderId);
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function consumeNotification($data)
    {
//        return \OpenPayU_Order::consumeNotification($data);
        return null;
    }
}