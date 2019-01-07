<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\DependencyInjection\Compiler;

use Hofff\Contao\Content\Action\ContentReferencesAction;
use Hofff\Contao\Content\Action\ModuleReferencesAction;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

final class AddResponseTaggerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('fos_http_cache.http.symfony_response_tagger')) {
            return;
        }

        if ($container->hasDefinition(ContentReferencesAction::class)) {
            $definition = $container->getDefinition(ContentReferencesAction::class);
            $definition->setArgument(4, new Reference('fos_http_cache.http.symfony_response_tagger'));
        }

        if ($container->hasDefinition(ModuleReferencesAction::class)) {
            $definition = $container->getDefinition(ModuleReferencesAction::class);
            $definition->setArgument(4, new Reference('fos_http_cache.http.symfony_response_tagger'));
        }
    }
}
