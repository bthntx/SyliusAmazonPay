<?php
/**
 * Created by PhpStorm.
 * User: beathan
 * Date: 01.11.2018
 * Time: 21:31
 */

namespace Bthn\SyliusAmazonPayPlugin\Action;

use Bthn\SyliusAmazonPayPlugin\Exception\AmazonPayException;
use Bthn\SyliusAmazonPayPlugin\Bridge\AmazonPayBridgeInterface;
use Bthn\SyliusAmazonPayPlugin\Reply\ConfigReply;
use Bthn\SyliusAmazonPayPlugin\SetAmazonPay;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Reply\BaseModelAware;
use Payum\Core\Reply\HttpRedirect;
use Payum\Core\Security\TokenInterface;
use Sylius\Component\Core\Model\CustomerInterface;
use Webmozart\Assert\Assert;
use Payum\Core\Payum;


class AmazonPayConfigAction implements ApiAwareInterface, ActionInterface
{
    private $api = [];
    /**
     * @var Payum
     */
    private $payum;
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
    public function __construct(Payum $payum)
    {
        $this->payum = $payum;
    }
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        //RequestNotSupportedException::assertSupports($this, $request);
        $config['sandbox_env'] = $this->api['sandbox_env'];
        $config['merchant_id'] = $this->api['merchant_id'];
        $config['access_key'] = $this->api['access_key'];
        $config['secret_key'] = $this->api['secret_key'];
        $config['client_id'] = $this->api['client_id'];
        $config['region'] = $this->api['region'];
        throw (new ConfigReply($config));
    }
    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {

        return
            $request instanceof SetAmazonPay &&
            $request->getModel() === 'amzn_config'
            ;
    }




}