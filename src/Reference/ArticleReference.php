<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Reference;

use Contao\ArticleModel;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Model\Registry;
use Doctrine\DBAL\Connection;
use Hofff\Contao\Content\Renderer\ArticleRenderer;
use Hofff\Contao\Content\Renderer\Renderer;
use Hofff\Contao\Content\Renderer\Select;
use Hofff\Contao\Content\Util\ContaoUtil;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ArticleReference extends RelatedReference implements CreatesRenderer, CreatesSelect
{
    use ConfigureRenderer;

    public function __construct(
        Connection $connection,
        private readonly ContaoUtil $contaoUtil,
        private readonly TranslatorInterface $translator,
        private readonly TokenChecker $tokenChecker,
    ) {
        parent::__construct($connection);
    }

    public function name(): string
    {
        return 'article';
    }

    /** {@inheritDoc} */
    public function backendIcon(array $row): string
    {
        $reference = $this->loadReference($row);
        if ($reference === null) {
            return 'articles.svg';
        }

        return $this->contaoUtil->isPublished((object) $reference) ? 'articles.svg' : 'articles_.svg';
    }

    /** {@inheritDoc} */
    public function createRenderer(array $reference, array $config): Renderer
    {
        $model = Registry::getInstance()->fetch('tl_article', $reference['id']);
        if (! $model instanceof ArticleModel) {
            $model = new ArticleModel();
            $model->setRow($reference);
        }

        $renderer = new ArticleRenderer($this->tokenChecker);
        $renderer->setArticle($model);
        $renderer->setRenderContainer($config['render_container']);

        $this->configureRenderer($renderer, $config);

        return $renderer;
    }

    /** {@inheritDoc} */
    public function createSelect(array $config, int $index, string $column): Select
    {
        $articleId       = $config['article'];
        $params          = [$index, $articleId];
        $targetCondition = '';

        if ($config['target_section_filter']) {
            $targetCondition = 'AND article.inColumn = ?';
            $params[]        = $column;
        }

        $sql = <<<SQL
SELECT
    ? AS hofff_content_index,
    article.*
FROM
    tl_article
    AS article
WHERE
    article.id = ?
$targetCondition
SQL;

        return new Select('article', $sql, $params);
    }

    /** {@inheritDoc} */
    protected function backendLabelExtra(array $row, array $reference): string|null
    {
        $column = $reference['inColumn'];
        $key    = 'COLS.' . $column;
        $label  = $this->translator->trans($key, [], 'contao_default');

        if ($label !== $key) {
            return $label;
        }

        return $column;
    }

    protected function labelColumn(): string
    {
        return 'title';
    }

    protected function referenceTable(): string
    {
        return 'tl_article';
    }
}
