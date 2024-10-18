<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Reference;

use Contao\Controller;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Hofff\Contao\Content\Renderer\Select;
use Hofff\Contao\Content\Util\LanguageRelationDetector;
use Hofff\Contao\Content\Util\QueryUtil;
use Hofff\Contao\LanguageRelations\LanguageRelations;

use function array_merge;
use function array_values;

final class PageReference extends RelatedReference implements CreatesSelect, CreatesRenderer, ProvidesOrderClause
{
    use CreateArticleRender;

    public function __construct(
        Connection $connection,
        private readonly LanguageRelationDetector $langRelationDetector,
        private readonly TokenChecker $tokenChecker,
    ) {
        parent::__construct($connection);
    }

    public function name(): string
    {
        return 'page';
    }

    /** {@inheritDoc} */
    public function backendIcon(array $row): string
    {
        $reference = $this->loadReference($row);
        if ($reference === null) {
            return 'regular.svg';
        }

        /** @psalm-suppress ArgumentTypeCoercion */
        return Controller::getPageStatusIcon((object) $reference);
    }

    /** {@inheritDoc} */
    public function createSelect(array $config, int $index, string $column): Select
    {
        /** @psalm-suppress PossiblyUndefinedArrayOffset */
        $pageId = $config['page'];

        if ($config['translate'] && isset($GLOBALS['objPage']) && $this->langRelationDetector->isActive()) {
            $root      = $GLOBALS['objPage']->rootId;
            $relations = LanguageRelations::getRelations((int) $pageId);
            $pageId    = $relations[$root] ?: $pageId;
        }

        $params = [$index, $pageId];

        $targetCondition = '';
        if ($config['target_section_filter']) {
            $targetCondition = 'AND article.inColumn = ?';
            $params[]        = $column;
        }

        $sourceCondition = '';
        $sourceSections  = StringUtil::deserialize($config['source_sections'], true);
        if ($sourceSections !== []) {
            $wildcards       = QueryUtil::wildcards($sourceSections);
            $sourceCondition = 'AND article.inColumn IN (' . $wildcards . ')';
            $params          = array_values(array_merge($params, $sourceSections));
        }

        $sql = <<<SQL
SELECT
    ? AS hofff_content_index,
    article.*
FROM
    tl_article
    AS article
WHERE
    article.pid = ?
$targetCondition
$sourceCondition
SQL;

        return new Select('page', $sql, $params);
    }

    public function orderClause(): string
    {
        return 'hofff_content_index, sorting';
    }

    protected function labelColumn(): string
    {
        return 'title';
    }

    protected function referenceTable(): string
    {
        return 'tl_page';
    }
}
