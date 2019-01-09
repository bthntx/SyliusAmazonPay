<?php
/**
 * Created by PhpStorm.
 * User: beathan
 * Date: 01.11.2018
 * Time: 21:26
 */

namespace Bthn\SyliusAmazonPayPlugin\Action;

use Bthn\SyliusAmazonPayPlugin\Bridge\AmazonPayBridgeInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Reply\HttpResponse;
use Payum\Core\Request\Notify;
use Sylius\Component\Core\Model\PaymentInterface;
use Webmozart\Assert\Assert;

class NotifyAction implements ActionInterface, ApiAwareInterface
{

    use GatewayAwareTrait;
    private $api = [];
    /**
     * @var AmazonPayBridgeInterface
     */
    private $amazonPayBridge;
    /**
     * @param AmazonPayBridgeInterface $AmazonPayBridge
     */
    public function __construct(AmazonPayBridgeInterface $AmazonPayBridge)
    {
        $this->amazonPayBridge = $AmazonPayBridge;
    }
    /**
     * @return \Payum\Core\GatewayInterface
     */
    public function getGateway()
    {
        return $this->gateway;
    }
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
     * {@inheritdoc}
     */
    public function execute($request)
    {
        /** @var $request Notify */
        RequestNotSupportedException::assertSupports($this, $request);
        /** @var PaymentInterface $payment */
        $payment = $request->getFirstModel();
        Assert::isInstanceOf($payment, PaymentInterface::class);
        $model = $request->getModel();
        $this->amazonPayBridge->setAuthorizationDataApi(
            $this->api['environment'],
            $this->api['signature_key'],
            $this->api['pos_id']
        );
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $body = file_get_contents('php://input');
            $data = trim($body);
            try {
                $result = $this->amazonPayBridge->consumeNotification($data);
                if ($result->getResponse()->order->orderId) {

                    $order =  $this->openPayUBridge->retrieve($result->getResponse()->order->orderId);
                    if (AmazonPayBridgeInterface::SUCCESS_API_STATUS === $order->getStatus()) {
                        if (PaymentInterface::STATE_COMPLETED !== $payment->getState()) {
                            $status = $order->getResponse()->orders[0]->status;
                            $model['statusAmazonPay'] = $status;
                            $request->setModel($model);
                        }
                        throw new HttpResponse('SUCCESS');
                    }
                }
            } catch (\Exception $e) {
                throw new HttpResponse($e->getMessage());
            }
        }
    }
    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof Notify &&
            $request->getModel() instanceof \ArrayObject
            ;
    }

}