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

final class ContaoUtil
{
    public const INDEXER_STOP     = '<!-- indexer::stop -->';
    public const INDEXER_CONTINUE = '<!-- indexer::continue -->';

    /** @var list<string> */
    private static array $indexerTokens = [self::INDEXER_STOP, self::INDEXER_CONTINUE];

    public static function isPublished(object $model, bool $checkBackendUser = true): bool
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

    public static function excludeFromSearch(string $content): string
    {
        if (! strlen($content)) {
            return $content;
        }

        $content = str_replace(self::$indexerTokens, '', $content);
        $content = self::INDEXER_STOP . $content . self::INDEXER_CONTINUE;

        return $content;
    }

    /** @param array<string,mixed> $data */
    public static function createTemplate(string $tpl, array|null $data = null): Template
    {
        $class    = defined('TL_MODE') && TL_MODE === 'FE' ? FrontendTemplate::class : BackendTemplate::class;
        $template = new $class($tpl);
        $data && $template->setData($data);

        return $template;
    }

    public static function renderBackendWidget(Widget $widget): string
    {
        $description = '';

        if (! $widget->hasErrors() && strlen($widget->description)) {
            $description = sprintf(
                '<p class="tl_help tl_tip">%s</p>',
                $widget->description,
            );
        }

        return sprintf(
            '<div class="%s">%s%s</div>',
            $widget->tl_class,
            $widget->parse(),
            $description,
        );
    }

    /**
     * @param array<string,mixed> $query
     * @param array<int,string>   $label
     */
    public static function generateBackendIconLink(array $query, string $image, array $label): string
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
            $label[0],
        );
    }

    /** @deprecated */
    public static function isModuleLoaded(string $module): bool
    {
        return in_array($module, ModuleLoader::getActive());
    }
}
