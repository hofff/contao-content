<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Reference;

use Contao\Model\Registry;
use Contao\ModuleModel;
use Doctrine\DBAL\Connection;
use Hofff\Contao\Content\Renderer\ModuleRenderer;
use Hofff\Contao\Content\Renderer\Renderer;
use Hofff\Contao\Content\Renderer\Select;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ModuleReference extends RelatedReference implements CreatesRenderer, CreatesSelect
{
    use ConfigureRenderer;

    public function __construct(Connection $connection, private readonly TranslatorInterface $translator)
    {
        parent::__construct($connection);
    }

    public function name(): string
    {
        return 'module';
    }

    /** {@inheritDoc} */
    public function backendIcon(array $row): string
    {
        return 'modules.svg';
    }

    /** {@inheritDoc} */
    public function createRenderer(array $reference, array $config): Renderer
    {
        $module = Registry::getInstance()->fetch('tl_module', $reference['id']);
        if (! $module instanceof ModuleModel) {
            $module = new ModuleModel();
            $module->setRow($reference);
        }

        $renderer = new ModuleRenderer();
        $renderer->setModule($module);

        $this->configureRenderer($renderer, $config);

        return $renderer;
    }

    /** {@inheritDoc} */
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
    protected function backendLabelExtra(array $row, array $reference): string|null
    {
        $key   = 'FMD.' . $reference['type'] . '.0';
        $label = $this->translator->trans($key, [], 'contao_modules');

        if ($label !== $key) {
            return $label;
        }

        return $reference['type'];
    }

    protected function labelColumn(): string
    {
        return 'name';
    }

    protected function referenceTable(): string
    {
        return 'tl_module';
    }
}
