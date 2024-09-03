<?php

declare(strict_types=1);

namespace Hofff\Contao\Content\Util;

use Contao\BackendTemplate;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Date;
use Contao\FrontendTemplate;
use Contao\Image;
use Contao\RequestToken;
use Contao\Template;
use Contao\Widget;
use Symfony\Component\HttpFoundation\RequestStack;

use function http_build_query;
use function sprintf;
use function str_replace;
use function strlen;

final class ContaoUtil
{
    public const INDEXER_STOP     = '<!-- indexer::stop -->';
    public const INDEXER_CONTINUE = '<!-- indexer::continue -->';

    /** @var list<string> */
    private static array $indexerTokens = [self::INDEXER_STOP, self::INDEXER_CONTINUE];

    public function __construct(
        private readonly TokenChecker $tokenChecker,
        private readonly ScopeMatcher $scopeMatcher,
        private readonly RequestStack $requestStack,
    ) {
    }

    public function isPublished(object $model, bool $checkBackendUser = true): bool
    {
        /** @psalm-suppress UndefinedConstant */
        if ($checkBackendUser && $this->tokenChecker->hasBackendUser()) {
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

        return self::INDEXER_STOP . $content . self::INDEXER_CONTINUE;
    }

    /** @param array<string,mixed> $data */
    public function createTemplate(string $tpl, array|null $data = null): Template
    {
        $request = $this->requestStack->getCurrentRequest();
        $class   = $request && $this->scopeMatcher->isFrontendRequest($request)
            ? FrontendTemplate::class
            : BackendTemplate::class;

        $template = new $class($tpl);
        $template->setData($data ?? []);

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
            '<a href="contao?%s" title="%s" data-title="%s" class="hofff-content-edit">%s %s</a>',
            $query,
            $title,
            $title,
            Image::getHtml($image, $title),
            $label[0],
        );
    }
}
