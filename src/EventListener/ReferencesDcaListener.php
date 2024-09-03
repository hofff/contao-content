<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\EventListener;

use Contao\BackendTemplate;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\Image;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Hofff\Contao\Content\Reference\ReferenceRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Terminal42\DcawizardBundle\Widget\DcaWizard;

use function array_merge;
use function asort;

use const SORT_LOCALE_STRING;

final readonly class ReferencesDcaListener
{
    public function __construct(
        private Connection $connection,
        private RequestStack $requestStack,
        private ReferenceRegistry $referenceRegistry,
    ) {
    }

    #[AsHook('loadDataContainer')]
    public function onLoadDataContainer(string $name): void
    {
        if ($name !== 'tl_hofff_content') {
            return;
        }

        $module = $this->requestStack->getCurrentRequest()?->query->get('do');
        if ($module === 'themes') {
            $GLOBALS['TL_DCA']['tl_hofff_content']['config']['ptable']                = 'tl_module';
            $GLOBALS['TL_DCA']['tl_hofff_content']['list']['sorting']['headerFields'] = ['name', 'pid'];
        } else {
            $GLOBALS['TL_DCA']['tl_hofff_content']['config']['ptable']                = 'tl_content';
            $GLOBALS['TL_DCA']['tl_hofff_content']['list']['sorting']['headerFields'] = ['headline', 'tstamp'];
        }
    }

    /**
     * @param list<array<string,mixed>> $records
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function referencesList(array $records, string|int $rowId, DcaWizard $wizard): string
    {
        $template = new BackendTemplate('be_hofff_content_list');

        $template->rows              = $records;
        $template->operations        = $wizard->getActiveRowOperations();
        $template->generateOperation = $wizard->generateRowOperation(...);
        $template->generateLabel     = $this->onLabel(...);

        return $template->parse();
    }

    /** @param array<string, mixed> $row */
    #[AsCallback('tl_hofff_content', 'list.label.label')]
    public function onLabel(array $row): string
    {
        $reference = $this->referenceRegistry->get($row['type']);

        return Image::getHtml($reference->backendIcon($row)) . ' ' . $reference->backendLabel($row);
    }

    /**
     * @return array<string, mixed>
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    #[AsCallback('tl_hofff_content', 'fields.source_sections.options')]
    public function layoutSectionOptionsCallback(): array
    {
        $defaultSections = [];

        foreach (
            [
                'header',
                'left',
                'right',
                'main',
                'footer',
            ] as $section
        ) {
            $defaultSections[$section] = $GLOBALS['TL_LANG']['COLS'][$section];
        }

        $sections = [];
        $sql      = 'SELECT sections FROM tl_layout WHERE sections != \'\'';
        $result   = $this->connection->executeQuery($sql)->fetchAllAssociative();

        foreach ($result as $layout) {
            $custom = StringUtil::deserialize($layout['sections'], true);

            foreach ($custom as $section) {
                if (! $section['id']) {
                    continue;
                }

                $sections[$section['id']] = $section['title'];
            }
        }

        asort($sections, SORT_LOCALE_STRING);

        return array_merge($defaultSections, $sections);
    }

    /** @return list<string> */
    #[AsCallback('tl_hofff_content', 'fields.type.options')]
    public function typeOptions(): array
    {
        return $this->referenceRegistry->names();
    }
}
