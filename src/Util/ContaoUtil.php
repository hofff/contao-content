<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Util;

use Contao\BackendTemplate;
use Contao\Date;
use Contao\FrontendTemplate;
use Contao\Image;
use Contao\ModuleLoader;
use Contao\RequestToken;
use Contao\Template;
use Contao\Widget;

use function defined;
use function http_build_query;
use function in_array;
use function sprintf;
use function str_replace;
use function strlen;

class ContaoUtil
{
    public const INDEXER_STOP     = '<!-- indexer::stop -->';
    public const INDEXER_CONTINUE = '<!-- indexer::continue -->';

    /** @var list<string> */
    private static $indexerTokens = [self::INDEXER_STOP, self::INDEXER_CONTINUE];

    /**
     * @param object $model
     * @param bool   $checkBackendUser
     *
     * @return bool
     */
    public static function isPublished($model, $checkBackendUser = true)
    {
        /** @psalm-suppress UndefinedConstant */
        if ($checkBackendUser && defined('BE_USER_LOGGED_IN') && BE_USER_LOGGED_IN) {
            return true;
        }

        $time = Date::floorToMinute();

        return $model->published
            && (! $model->start || $model->start <= $time)
            && (! $model->stop || $model->stop > $time + 60);
    }

    /**
     * @param string $content
     *
     * @return string
     */
    public static function excludeFromSearch($content)
    {
        if (! strlen($content)) {
            return $content;
        }

        $content = str_replace(self::$indexerTokens, '', $content);
        $content = self::INDEXER_STOP . $content . self::INDEXER_CONTINUE;

        return $content;
    }

    /**
     * @param string              $tpl
     * @param array<string,mixed> $data
     *
     * @return Template
     */
    public static function createTemplate($tpl, ?array $data = null)
    {
        $class    = defined('TL_MODE') && TL_MODE === 'FE' ? FrontendTemplate::class : BackendTemplate::class;
        $template = new $class($tpl);
        $data && $template->setData($data);

        return $template;
    }

    /**
     * @return string
     */
    public static function renderBackendWidget(Widget $widget)
    {
        $description = '';

        if (! $widget->hasErrors() && strlen($widget->description)) {
            $description = sprintf(
                '<p class="tl_help tl_tip">%s</p>',
                $widget->description
            );
        }

        return sprintf(
            '<div class="%s">%s%s</div>',
            $widget->tl_class,
            $widget->parse(),
            $description
        );
    }

    /**
     * @param array<string,mixed> $query
     * @param string              $image
     * @param array<int,string>   $label
     *
     * @return string
     */
    public static function generateBackendIconLink(array $query, $image, $label)
    {
        $title = sprintf($label[1], $query['id'] ?? $query['pn']);

        $query['rt'] = RequestToken::get();
        $query       = http_build_query($query, '', '&amp;');

        return sprintf(
            '<a href="contao/main.php?%s" title="%s" data-title="%s" class="hofff-content-edit">%s %s</a>',
            $query,
            $title,
            $title,
            Image::getHtml($image, $title),
            $label[0]
        );
    }

    /**
     * @deprecated
     *
     * @param string $module
     *
     * @return bool
     */
    public static function isModuleLoaded($module)
    {
        return in_array($module, ModuleLoader::getActive());
    }
}
