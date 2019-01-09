<?php
/**
 * Created by PhpStorm.
 * User: beathan
 * Date: 01.11.2018
 * Time: 21:38
 */

namespace Bthn\SyliusAmazonPayPlugin\Action;

use Bthn\SyliusAmazonPayPlugin\Bridge\AmazonPayBridgeInterface;
use Payum\Core\Action\ActionInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Request\GetStatusInterface;

class StatusAction implements ActionInterface
{

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request GetStatusInterface */
        RequestNotSupportedException::assertSupports($this, $request);
        $model = ArrayObject::ensureArrayObject($request->getModel());
        $status = isset($model['statusAmazonPay']) ? $model['statusAmazonPay'] : null;
        if ((null === $status || AmazonPayBridgeInterface::NEW_API_STATUS === $status) && false === isset($model['orderId'])) {
            $request->markNew();
            return;
        }
        if (AmazonPayBridgeInterface::PENDING_API_STATUS === $status) {
            return;
        }
        if (AmazonPayBridgeInterface::CANCELED_API_STATUS === $status) {
            $request->markCanceled();
            return;
        }
        if (AmazonPayBridgeInterface::WAITING_FOR_CONFIRMATION_PAYMENT_STATUS === $status) {
            $request->markSuspended();
            return;
        }
        if (AmazonPayBridgeInterface::COMPLETED_API_STATUS === $status) {
            $request->markCaptured();
            return;
        }
        $request->markUnknown();
    }
    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return $request instanceof GetStatusInterface &&
            $request->getModel() instanceof \ArrayAccess
            ;
    }

}