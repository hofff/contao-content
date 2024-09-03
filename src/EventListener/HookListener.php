<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\EventListener;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Framework\ContaoFramework;

use function array_replace;
use function in_array;
use function sprintf;

final class HookListener
{
    private const DATA_CONTAINERS = [
        'tl_content' => '{type_legend},type'
            . '%s'
            . ';{protected_legend:hide},protected'
            . ';{expert_legend:hide},guests,cssID,space'
            . ';{invisible_legend:hide},invisible,start,stop',
        'tl_module' =>  '{title_legend},name,type'
            . '%s'
            . ';{protected_legend:hide},protected'
            . ';{expert_legend:hide},guests,cssID,space',
    ];

    public function __construct(private readonly ContaoFramework $framework)
    {
    }

    /** Dynamically allow tl_hofff_content for each module which uses tl_content. */
    #[AsHook('initializeSystem')]
    public function onInitializeSystem(): void
    {
        foreach ($GLOBALS['BE_MOD'] as $category => $modules) {
            foreach ($modules as $module => $config) {
                if (! in_array('tl_content', $config['tables'] ?? [])) {
                    continue;
                }

                $GLOBALS['BE_MOD'][$category][$module]['tables'][] = 'tl_hofff_content';
            }
        }
    }

    #[AsHook('loadDataContainer')]
    public function onLoadDataContainer(string $name): void
    {
        if (! isset(self::DATA_CONTAINERS[$name])) {
            return;
        }

        $controller = $this->framework->getAdapter(Controller::class);
        $controller->loadLanguageFile('hofff_content');
        $controller->loadDataContainer('hofff_content');

        $GLOBALS['TL_DCA'][$name]['palettes']['hofff_content_references'] = sprintf(
            self::DATA_CONTAINERS[$name],
            $GLOBALS['TL_DCA']['hofff_content']['palettes']['default'],
        );

        $GLOBALS['TL_DCA'][$name]['fields'] = array_replace(
            $GLOBALS['TL_DCA'][$name]['fields'],
            $GLOBALS['TL_DCA']['hofff_content']['fields'],
        );
    }

    #[AsHook('isVisibleElement')]
    public function onIsVisibleElement(object $row, bool $visible): bool
    {
        return $visible && ! $row->hofff_content_hide;
    }
}
