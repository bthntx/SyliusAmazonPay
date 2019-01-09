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
use Bthn\SyliusAmazonPayPlugin\Exception\AmazonPayException;
use Bthn\SyliusAmazonPayPlugin\SetAmazonPay;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Payum;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Symfony\Component\HttpFoundation\Session\Session;


class AmazonPayAction implements ApiAwareInterface, ActionInterface
{
    protected $api = [];
    /**
     * @var AmazonPayBridgeInterface
     */
    protected $amazonPayBridge;
    /**
     * @var Payum
     */
    protected $payum;


    private $amazonOrderId;
    private $token;

    /**
     * {@inheritDoc}
     */
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
    public function __construct(AmazonPayBridgeInterface $amazonPayBridge, Payum $payum, Session $session)
    {
        $this->payum = $payum;
        $this->amazonPayBridge = $amazonPayBridge;
        $this->amazonOrderId = $session->get('amzn_order_id', '');
        $this->token = $session->get('amzn_token', '');
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        //RequestNotSupportedException::assertSupports($this, $request);
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
        $response = $amazonPay->setOrderAmount($this->amazonOrderId, $model['seller_order_id'], $model['total'],
            $model['currency']);

        if (null !== $model['orderId']) {
            /** @var mixed $response */
            $response = $amazonPay->retrieve($model['orderId'])->getResponse();
            Assert::keyExists($response->orders, 0);
            if (AmazonPayBridgeInterface::SUCCESS_API_STATUS === $response->status->statusCode) {
                $model['statusAmazonPay'] = $response->orders[0]->status;
                $request->setModel($model);
            }
            if (AmazonPayBridgeInterface::NEW_API_STATUS !== $response->orders[0]->status) {
                return;
            }
        }
         /**
         * @var TokenInterface $token
         */
        $token = $request->getToken();
        $order = $this->prepareOrder($token, $model);

        $response = $amazonPay->create($order)->getResponse();
        if ($response && AmazonPayBridgeInterface::SUCCESS_API_STATUS === $response->status->statusCode) {
            $model['orderId'] = $response->orderId;
            $request->setModel($model);
            throw new HttpRedirect($response->redirectUri);
        }
        throw AmazonPayException::newInstance($response->status);
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
            $request->getModel()['action'] === 'pay';
    }

    /**
     * @return AmazonPayBridgeInterface
     */
    public function getAmazonPayBridge()
    {
        return $this->amazonPayBridge;
    }

    /**
     * @param AmazonPayBridgeInterface $amazonPayBridge
     */
    public function setAmazonPayBridge($amazonPayBridge)
    {
        $this->amazonPayBridge = $amazonPayBridge;
    }

    private function prepareOrder(TokenInterface $token, $model)
    {
        $notifyToken = $this->createNotifyToken($token->getGatewayName(), $token->getDetails());
        $order = [];
        $order['continueUrl'] = $token->getTargetUrl();
        $order['notifyUrl'] = $notifyToken->getTargetUrl();
        $order['customerIp'] = $model['customerIp'];
        $order['description'] = $model['description'];
        $order['currencyCode'] = $model['currencyCode'];
        $order['totalAmount'] = $model['totalAmount'];
        $order['extOrderId'] = $model['extOrderId'];
        /** @var CustomerInterface $customer */
        $customer = $model['customer'];
//        Assert::isInstanceOf(
//            $customer,
//            CustomerInterface::class,
//            sprintf(
//                'Make sure the first model is the %s instance.',
//                CustomerInterface::class
//            )
//        );
//        $buyer = [
//            'email' => (string) $customer->getEmail(),
//            'firstName' => (string) $customer->getFirstName(),
//            'lastName' => (string) $customer->getLastName(),
//            'language' => $model['locale'],
//        ];
//        $order['buyer'] = $buyer;
        $order['products'] = $this->resolveProducts($model);

        return $order;
    }

    /**
     * @param $model
     *
     * @return array
     */
    private function resolveProducts($model)
    {
        if (!array_key_exists('products', $model) || count($model['products']) === 0) {
            return [
                [
                    'name' => $model['description'],
                    'unitPrice' => $model['totalAmount'],
                    'quantity' => 1,
                ],
            ];
        }

        return $model['products'];
    }

    /**
     * @param string $gatewayName
     * @param object $model
     *
     * @return TokenInterface
     */
    private function createNotifyToken($gatewayName, $model)
    {
        return $this->payum->getTokenFactory()->createNotifyToken(
            $gatewayName,
            $model
        );
    }
}
