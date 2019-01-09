<?php

declare(strict_types=1);

namespace Bthn\SyliusAmazonPayPlugin\Controller;

use Bthn\SyliusAmazonPayPlugin\SetAmazonPay;
use Payum\Core\Reply\HttpResponse;
use Sylius\Component\Core\Model\Address;
use Sylius\Component\Core\Model\Customer;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\OrderCheckoutStates;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class AmazonPayController extends Controller
{
    public function saveAuthTokenAction(Request $request): Response
    {

        $token = $request->get("access_token");
        $request->getSession()->set('amzn_token',$token);
        //Forward to amazon select address
        return $this->redirectToRoute('bthn_sylius_amazonpay_checkout');
    }

    /**
     * @param string|null $name
     *
     * @return Response
     */
    public function getCurrentOrderInfoAction(Request $request): Response
    {
        $amazonOrderId = $request->get("amazonOrderId");
        $token = $request->getSession()->get('amzn_token');
        $request->getSession()->set('amzn_order_id',$amazonOrderId);
        $order = $this->get('sylius.context.cart.session_and_channel_based')->getCart();
        $newEvent = new SetAmazonPay(null);
        $newEvent->setModel(['action'=>'getAddress','amazonOrderId'=>$amazonOrderId,'token'=>$token]);
        /** @var HttpResponse $response */
        $response = $this->get('payum')->getGateway('amazonpay')->execute($newEvent,true);
        if ($response->getStatusCode()!=200) return new JsonResponse($response);
        $responseData = $response->getContent();
        $address = new Address();
        $name = explode(' ',$responseData['address']['PhysicalDestination']['Name']);
        $address->setFirstName($name[0]);
        $address->setLastName($name[1] ?? '');
        $address->setStreet(($responseData['address']['PhysicalDestination']['AddressLine1'] ??'').' '.
            ($responseData['address']['PhysicalDestination']['AddressLine2'] ?? ''));
        $address->setCity($responseData['address']['PhysicalDestination']['City']);
        $address->setProvinceName($responseData['address']['PhysicalDestination']['StateOrRegion'] ??'');

        $address->setCountryCode($responseData['address']['PhysicalDestination']['CountryCode']);
        $address->setPostcode($responseData['address']['PhysicalDestination']['PostalCode']);
        $address->setPhoneNumber($responseData['address']['PhysicalDestination']['Phone']);

        $userEmail = $responseData['buyer']['Email'];
        $customer = $this->container->get('sylius.repository.customer')->findOneBy(['email' => $userEmail]);
        if (!$customer) {
            $customer = new Customer();
            $customer->setFirstName($name[0]);
            $customer->setLastName($name[1] ?? '');
            $customer->setEmail($userEmail);
        }


        $method = $this->get('sylius.repository.payment_method')->findOneBy(['code'=>'amazonpay']);
        $payment = new Payment();
        $payment->setMethod($method);
        $payment->setCurrencyCode($order->getCurrencyCode());

        $address->setCustomer($customer);
        /** @var Order  $order*/
        foreach ($order->getPayments() as $item ) {
            $order->removePayment($item);
        }

        $order->addPayment($payment);
        $order->setBillingAddress($address);
        $order->setShippingAddress($address);
        $order->setCustomer($customer);
        $order->setCheckoutState(OrderCheckoutStates::STATE_ADDRESSED);


        $this->get('sylius.manager.order')->flush();
        return $this->redirectToRoute('sylius_shop_checkout_select_shipping');
    }


    public function selectAmazonAddressAction()
    {
        $order = $this->get('sylius.context.cart.session_and_channel_based')->getCart();
        $newEvent = new SetAmazonPay(null);
        $newEvent->setModel('amzn_config');
        $settings = $this->get('payum')->getGateway('amazonpay')->execute($newEvent,true);
        foreach ($settings->getModel() as $key=>$v)
        {
            $config[$key]=$v;
        }
        return $this->render('BthnSyliusAmazonPayPlugin::amazonpay_select_address.html.twig',['order'=>$order,'settings'=>$config]);
    }
}
