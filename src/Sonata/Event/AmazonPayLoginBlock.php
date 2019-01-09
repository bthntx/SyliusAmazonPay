<?php
/**
 * Created by PhpStorm.
 * User: beathan
 * Date: 02.11.2018
 * Time: 14:46
 */
declare(strict_types=1);
namespace Bthn\SyliusAmazonPayPlugin\Sonata\Event;
use Bthn\SyliusAmazonPayPlugin\SetAmazonPay;
use Payum\Core\GatewayInterface;
use Payum\Core\Payum;
use Sonata\BlockBundle\Event\BlockEvent;
use Sonata\BlockBundle\Model\Block;


use Symfony\Component\HttpFoundation\RequestStack;

class AmazonPayLoginBlock
{
    /**
     * @var string
     */
    private $template;
    /**
     * @var string
     */
    private $route;
    /**
     * @var array
     */
    private $config;


    /**
     * @param string $template
     */
    public function __construct(string $template, Payum $payum, RequestStack $request)
    {
        if (!$request->getMasterRequest()) return;
        $this->template = $template;
        /** @var GatewayInterface $gateway */
        $gateway = $payum->getGateway('amazonpay');
        $amazonPayAction = new SetAmazonPay(null);
        $amazonPayAction->setModel('amzn_config');
        foreach ($gateway->execute($amazonPayAction,true)->getModel() as $key=>$v)
        {
            $this->config[$key]=$v;
        }
        $this->route = $request->getMasterRequest()->get('_route');

    }

    /**
     * @param BlockEvent $event
     */
    public function onBlockEvent(BlockEvent $event): void
    {
        $block = new Block();
        $block->setId(uniqid('', true));

        $block->setSettings(array_replace($event->getSettings(), [
            'template' => $this->template,'resource'=>$this->config
        ]));
        $block->setType('sonata.block.service.template');
        $event->addBlock($block);
    }


    /**
     * @param BlockEvent $event
     */
    public function onHeaderBlockEvent(BlockEvent $event): void
    {
        if (strpos($this->route,'sylius_shop_cart_summary')!==0 && strpos($this->route,'bthn_sylius_amazonpay_')!==0) return;
        $block = new Block();
        $block->setId(uniqid('', true));

        $block->setSettings(array_replace($event->getSettings(), [
            'template' => $this->template,'resource'=>$this->config
        ]));
        $block->setType('sonata.block.service.template');
        $event->addBlock($block);

    }
}