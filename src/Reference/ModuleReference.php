<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Reference;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model\Registry;
use Contao\ModuleModel;
use Doctrine\DBAL\Connection;
use Hofff\Contao\Content\Renderer\ModuleRenderer;
use Hofff\Contao\Content\Renderer\Renderer;
use Hofff\Contao\Content\Renderer\Select;
use Override;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ModuleReference extends RelatedReference implements CreatesRenderer, CreatesSelect
{
    use ConfigureRenderer;

    public function __construct(
        Connection $connection,
        private readonly TranslatorInterface $translator,
        private readonly ContaoFramework $contaoFramework,
    ) {
        parent::__construct($connection);
    }

    #[Override]
    public function name(): string
    {
        return 'module';
    }

    /** {@inheritDoc} */
    #[Override]
    public function backendIcon(array $row): string
    {
        return 'modules.svg';
    }

    /** {@inheritDoc} */
    #[Override]
    public function createRenderer(array $reference, array $config): Renderer
    {
        $module = Registry::getInstance()->fetch('tl_module', $reference['id']);
        if (! $module instanceof ModuleModel) {
            $module = new ModuleModel();
            $module->setRow($reference);
        }

        $renderer = new ModuleRenderer($this->contaoFramework);
        $renderer->setModule($module);

        $this->configureRenderer($renderer, $config);

        return $renderer;
    }

    /** {@inheritDoc} */
    #[Override]
    public function createSelect(array $config, int $index, string $column): Select
    {
        $moduleId = $config['module'];
        $params   = [$index, $moduleId];
        $sql      = <<<'SQL'
SELECT
    ? AS hofff_content_index,
    module.*
FROM
    tl_module
    AS module
WHERE
    module.id = ?
SQL;

        return new Select('module', $sql, $params);
    }

    /** {@inheritDoc} */
    #[Override]
    protected function backendLabelExtra(array $row, array $reference): string|null
    {
        $key   = 'FMD.' . $reference['type'] . '.0';
        $label = $this->translator->trans($key, [], 'contao_modules');

        if ($label !== $key) {
            return $label;
        }

        return $reference['type'];
    }

    #[Override]
    protected function labelColumn(): string
    {
        return 'name';
    }

    #[Override]
    protected function referenceTable(): string
    {
        return 'tl_module';
    }
}
