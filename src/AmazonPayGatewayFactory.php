<?php
/**
 * Created by PhpStorm.
 * User: beathan
 * Date: 01.11.2018
 * Time: 19:47
 */

namespace Bthn\SyliusAmazonPayPlugin;

use Bthn\SyliusAmazonPayPlugin\Action\CaptureAction;
use Bthn\SyliusAmazonPayPlugin\Action\ConvertPaymentAction;
use Bthn\SyliusAmazonPayPlugin\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

class AmazonPayGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config)
    {
        $config->defaults([
            'payum.factory_name' => 'amazonpay',
            'payum.factory_title' => 'AmazonPay',
            'payum.action.capture' => new CaptureAction(),
            'payum.action.convert_payment' => new ConvertPaymentAction(),
            'payum.action.status' => new StatusAction(),
        ]);
        if (false == $config['payum.api']) {
            $config['payum.default_options'] = [
                'sandbox_env' => 'true',
                'merchant_id' => '',
                'access_key' => '',
                'secret_key' => '',
                'client_id' => '',
                'region' => '',
            ];
            $config->defaults($config['payum.default_options']);
            $config['payum.required_options'] = ['sandbox_env', 'merchant_id', 'access_key','secret_key','client_id','region'];
            $config['payum.api'] = function (ArrayObject $config) {
                $config->validateNotEmpty($config['payum.required_options']);
                $amazonPayConfig = [
                    'sandbox_env' => $config['sandbox_env'],
                    'merchant_id' => $config['merchant_id'],
                    'access_key' => $config['access_key'],
                    'secret_key' => $config['secret_key'],
                    'client_id' => $config['client_id'],
                    'region' => $config['region'],
                ];
                return $amazonPayConfig;
            };
        }
    }
}