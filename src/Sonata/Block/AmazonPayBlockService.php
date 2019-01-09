<?php
/**
 * Created by PhpStorm.
 * User: beathan
 * Date: 02.11.2018
 * Time: 20:04
 */

namespace Bthn\SyliusAmazonPayPlugin\Sonata\Block;


use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\TemplateBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;


class AmazonPayBlockService extends TemplateBlockService
{

    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {

        return $this->renderResponse($blockContext->getTemplate(), [
            'block' => $blockContext->getBlock(),
            'settings' => $blockContext->getSettings(),
        ], $response);
    }


    public function configureSettings(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'url'      => false,
            'title'    => 'Insert the rss title',
            'template' => '@SonataBlock/Block/block_core_rss.html.twig',
        ));
    }


}