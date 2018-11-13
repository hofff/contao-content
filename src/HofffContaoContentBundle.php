<?php

declare(strict_types=1);

namespace Hofff\Contao\Content;

use Hofff\Contao\Content\DependencyInjection\Compiler\AddResponseTaggerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class HofffContaoContentBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new AddResponseTaggerPass());
    }
}
