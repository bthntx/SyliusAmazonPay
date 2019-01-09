<?php

namespace Bthn\SyliusAmazonPayPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @author Khudolii Sergii <sk@bthn.trade>
 */
final class AmazonPayGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sandbox_env', ChoiceType::class, [
                'choices' => [
                    'bthn.amazonpay_plugin.sandbox' => 'true',
                    'bthn.amazonpay_plugin.secure' => 'false',
                ],
                'label' => 'bthn.amazonpay_plugin.sandbox_env',
            ])
            ->add('region', ChoiceType::class, [
                'choices' => [
                    'bthn.amazonpay_plugin.us' => 'us',
                    'bthn.amazonpay_plugin.uk' => 'uk',
                    'bthn.amazonpay_plugin.de' => 'de',
                    'bthn.amazonpay_plugin.jp' => 'jp',
                ],
                'label' => 'bthn.amazonpay_plugin.region',
            ])
            ->add('client_id', TextType::class, [
                'label' => 'bthn.amazonpay_plugin.client_id',
                'constraints' => [
                    new NotBlank([
                        'message' => 'bthn.amazonpay_plugin.gateway_configuration.client_id.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('merchant_id', TextType::class, [
                'label' => 'bthn.amazonpay_plugin.merchant_id',
                'constraints' => [
                    new NotBlank([
                        'message' => 'bthn.amazonpay_plugin.gateway_configuration.merchant_id.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('access_key', TextType::class, [
                'label' => 'bthn.amazonpay_plugin.access_key',
                'constraints' => [
                    new NotBlank([
                        'message' => 'bthn.amazonpay_plugin.gateway_configuration.access_key.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ])
            ->add('secret_key', TextType::class, [
                'label' => 'bthn.amazonpay_plugin.secret_key',
                'constraints' => [
                    new NotBlank([
                        'message' => 'bthn.amazonpay_plugin.gateway_configuration.secret_key.not_blank',
                        'groups' => ['sylius'],
                    ]),
                ],
            ]);
    }
}