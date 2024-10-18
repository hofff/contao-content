<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Reference;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Doctrine\DBAL\Connection;
use Hofff\Contao\Content\Renderer\Select;
use Hofff\Contao\Content\Util\ContaoUtil;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ArticleReference extends RelatedReference implements CreatesRenderer, CreatesSelect
{
    use CreateArticleRender;

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
