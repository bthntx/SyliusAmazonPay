<?php
/**
 * Created by PhpStorm.
 * User: beathan
 * Date: 01.11.2018
 * Time: 21:02
 */

namespace Bthn\SyliusAmazonPayPlugin\Action;

    use Bthn\SyliusAmazonPayPlugin\SetAmazonPay;
    use Payum\Core\Action\ActionInterface;
    use Payum\Core\Bridge\Spl\ArrayObject;
    use Payum\Core\Exception\RequestNotSupportedException;
    use Payum\Core\GatewayAwareInterface;
    use Payum\Core\GatewayAwareTrait;
    use Payum\Core\Request\Capture;
    use Payum\Core\Security\TokenInterface;
    use Sylius\Component\Core\Model\Order;

    final class CaptureAction implements ActionInterface, GatewayAwareInterface
{
    use GatewayAwareTrait;
    /**
     * {@inheritdoc}
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);
        $model = $request->getModel();
        ArrayObject::ensureArrayObject($model);
        /** @var Order $order */
        $order = $request->getFirstModel()->getOrder();
        $total = substr((string)$order->getTotal(),0,-2).'.'.substr((string)$order->getTotal(),-2,2);
        $model['locale'] = $this->getFallbackLocaleCode($order->getLocaleCode());
        $model['action'] = 'pay';
        $model['total'] = $total;
        $model['seller_order_id'] = $order->getNumber();
        $model['currency'] = $order->getCurrencyCode();
        $amazonPayAction = $this->getAmazonPayAction($request->getToken(), $model);
        $this->getGateway()->execute($amazonPayAction);
    }
    /**
     * {@inheritdoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Capture &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }
    /**
     * @return \Payum\Core\GatewayInterface
     */
    public function getGateway()
    {
        return $this->gateway;
    }
    /**
     * @param TokenInterface $token
     * @param ArrayObject $model
     *
     * @return SetAmazonPay
     */
    private function getAmazonPayAction(TokenInterface $token, ArrayObject $model)
    {
        $amazonPayAction = new SetAmazonPay($token);
        $amazonPayAction->setModel($model);
        return $amazonPayAction;
    }
    private function getFallbackLocaleCode($localeCode)
    {
        return explode('_', $localeCode)[0];
    }
}