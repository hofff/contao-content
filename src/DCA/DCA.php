<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\DCA;

use Contao\Controller;
use Contao\Database;
use Contao\DataContainer;
use Contao\Image;
use Contao\StringUtil;
use Hofff\Contao\Content\Util\ContaoUtil;
use Hofff\Contao\Selectri\Model\Node;
use stdClass;

use function array_merge;
use function asort;
use function call_user_func_array;
use function is_array;
use function sprintf;

use const SORT_LOCALE_STRING;

class DCA
{
    /** @SuppressWarnings(PHPMD.Superglobals) */
    public static function getTemplateGroupOptionsCallback(string $prefixesKey): callable
    {
        return static function (DataContainer|null $dataContainer) use ($prefixesKey) {
            if (! $dataContainer) {
                return [];
            }

            $prefixes = &$GLOBALS['TL_DCA'][$dataContainer->table]['fields'][$dataContainer->field][$prefixesKey];
            if (! is_array($prefixes)) {
                return [];
            }

            $templates = [];
            foreach ($prefixes as $prefix) {
                $templates[] = Controller::getTemplateGroup($prefix);
            }

            return call_user_func_array('array_replace', $templates);
        };
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    public static function getLayoutSectionOptionsCallback(): callable
    {
        static $sections = null;

        return static function () use (&$sections) {
            if (isset($sections)) {
                return $sections;
            }

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
            $layout   = Database::getInstance()->query($sql);
            while ($layout->next()) {
                $custom = StringUtil::deserialize($layout->sections, true);

                foreach ($custom as $section) {
                    $sections[$section['id']] = $section['title'];
                }
            }

            asort($sections, SORT_LOCALE_STRING);

            $sections = array_merge($defaultSections, $sections);

            return $sections;
        };
    }

    public static function getArticleIcon(Node $node): string
    {
        $published = ContaoUtil::isPublished((object) $node->getData());

        return Image::getPath($published ? 'articles.gif' : 'articles_.gif');
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    public static function getArticleLabel(Node $node): string
    {
        $data = $node->getData();

        $column = $data['in_column'];
        if (isset($GLOBALS['TL_LANG']['COLS'][$column])) {
            $column = $GLOBALS['TL_LANG']['COLS'][$column];
        }

        return sprintf(
            '%s <span class="hofff-content-label">[%s] (ID %s)</span>',
            $data['title'],
            $column,
            $data['id'],
        );
    }

    public static function getPageIcon(Node $node): string
    {
        /** @psalm-var stdClass $page */
        $page = (object) $node->getData();

        return Image::getPath(Controller::getPageStatusIcon($page));
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    public static function getModuleLabel(Node $node): string
    {
        $data = $node->getData();

        $type = $data['type'];
        if (isset($GLOBALS['TL_LANG']['FMD'][$type][0])) {
            $type = $GLOBALS['TL_LANG']['FMD'][$type][0];
        }

        return sprintf(
            '%s <span class="hofff-content-label">[%s] (ID %s)</span>',
            $data['title'],
            $type,
            $data['id'],
        );
    }
}
