<?php

declare(strict_types=1);

namespace Bthn\SyliusAmazonPayPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class BthnSyliusAmazonPayPlugin extends Bundle
{
    use SyliusPluginTrait;
}
