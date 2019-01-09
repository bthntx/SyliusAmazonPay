<?php
/**
 * Created by PhpStorm.
 * User: beathan
 * Date: 01.11.2018
 * Time: 21:31
 */

namespace Bthn\SyliusAmazonPayPlugin\Action;

use AmazonPay\ResponseParser;
use Bthn\SyliusAmazonPayPlugin\Bridge\AmazonPayBridgeInterface;
use Bthn\SyliusAmazonPayPlugin\SetAmazonPay;
use Payum\Core\Bridge\Spl\ArrayObject;

use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Payum;
use Payum\Core\Reply\HttpResponse;


class AmazonPayGetAddressAction extends AmazonPayAction
{
    public function setApi($api)
    {
        if (!is_array($api)) {
            throw new UnsupportedApiException('Not supported.');
        }
        $this->api = $api;
    }
    /**
     * @param AmazonPayBridgeInterface $openPayUBridge
     * @param Payum $payum
     */
    public function __construct(AmazonPayBridgeInterface $amazonPayBridge, Payum $payum)
    {
        $this->payum = $payum;
        $this->amazonPayBridge = $amazonPayBridge;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {

        $sandbox_env = $this->api['sandbox_env'];
        $merchant_id = $this->api['merchant_id'];
        $access_key = $this->api['access_key'];
        $secret_key = $this->api['secret_key'];
        $amzn_login = $this->api['client_id'];
        $region = $this->api['region'];
        $amazonPay = $this->getAmazonPayBridge();
        $amazonPay->setAuthorizationDataApi($sandbox_env, $merchant_id, $access_key, $secret_key, $amzn_login, $region);
        $model = ArrayObject::ensureArrayObject($request->getModel());
        /** @var ResponseParser $response */
        $response = $amazonPay->getOrderInfo($model['amazonOrderId'], $model['token'])->toArray();
        if (!array_key_exists('ResponseStatus',$response) || $response['ResponseStatus']!=200) {
            throw new HttpResponse(null,400);
        }
        $orderInfo['address'] = $response['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Destination'];
        $orderInfo['buyer'] = $response['GetOrderReferenceDetailsResult']['OrderReferenceDetails']['Buyer'];

        throw new HttpResponse($orderInfo);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof SetAmazonPay &&
            $request->getModel() instanceof \ArrayObject &&
            array_key_exists('action', $request->getModel()) &&
            $request->getModel()['action'] === 'getAddress';
    }


}