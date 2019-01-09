<?php
/**
 * Created by PhpStorm.
 * User: beathan
 * Date: 01.11.2018
 * Time: 21:10
 */

namespace Bthn\SyliusAmazonPayPlugin\Bridge;


interface AmazonPayBridgeInterface
{
    const NEW_API_STATUS = 'NEW';
    const PENDING_API_STATUS = 'PENDING';
    const COMPLETED_API_STATUS = 'COMPLETED';
    const SUCCESS_API_STATUS = 'SUCCESS';
    const CANCELED_API_STATUS = 'CANCELED';
    const COMPLETED_PAYMENT_STATUS = 'COMPLETED';
    const PENDING_PAYMENT_STATUS = 'PENDING';
    const CANCELED_PAYMENT_STATUS = 'CANCELED';
    const WAITING_FOR_CONFIRMATION_PAYMENT_STATUS = 'WAITING_FOR_CONFIRMATION';
    const REJECTED_STATUS = 'REJECTED';
    /**
     * @param $sandbox_env
     * @param $merchant_id
     * @param $access_key
     * @param $secret_key
     * @param $amzn_login
     * @param $region
     */
    public function setAuthorizationDataApi($sandbox_env, $merchant_id, $access_key, $secret_key, $amzn_login, $region);
    /**
     * @param $amazonOrderId
     */
    public function confirmOrder(string $amazonOrderId);
    /**
     * @param $amazonOrderId
     * @param $amount
     * @param $currencyCode
     */
    public function authorizePayment(string $amazonOrderId, string $amount, string $currencyCode);
    /**
     * @param $amazonOrderId
     * @param $token
     */
    public function getOrderInfo(string $amazonOrderId, string $token);
    /**
     * @param string $amazonOrderId
     * @param string $seller_order_id
     * @param string $amount
     * @param string $currencyCode
     *
     * @return object
     */
    public function setOrderAmount(string $amazonOrderId, string $seller_order_id, string $amount, string $currencyCode);
    /**
     * @param $data
     * @return null|\OpenPayU_Result
     *
     * @throws \OpenPayU_Exception
     */
    public function consumeNotification($data);
}