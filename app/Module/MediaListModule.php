<?php

/**
 * webtrees: online genealogy
 * Copyright (C) 2021 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Fisharebest\Webtrees\Module;

use Aura\Router\RouterContainer;
use Fig\Http\Message\RequestMethodInterface;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Contracts\UserInterface;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\GedcomRecord;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Media;
use Fisharebest\Webtrees\Tree;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use function addcslashes;
use function app;
use function array_combine;
use function array_unshift;
use function assert;
use function dirname;
use function max;
use function min;
use function redirect;
use function route;

/**
 * Class MediaListModule
 */
class MediaListModule extends AbstractModule implements ModuleListInterface, RequestHandlerInterface
{
    use ModuleListTrait;

    protected const ROUTE_URL = '/tree/{tree}/media-list';

    /**
     * Initialization.
     *
     * @return void
     */
    public function boot(): void
    {
        $router_container = app(RouterContainer::class);
        assert($router_container instanceof RouterContainer);

        $router_container->getMap()
            ->get(static::class, static::ROUTE_URL, $this)
            ->allows(RequestMethodInterface::METHOD_POST);
    }

    /**
     * How should this module be identified in the control panel, etc.?
     *
     * @return string
     */
    public function title(): string
    {
        /* I18N: Name of a module/list */
        return I18N::translate('Media objects');
    }

    /**
     * A sentence describing what this module does.
     *
     * @return string
     */
    public function description(): string
    {
        /* I18N: Description of the “Media objects” module */
        return I18N::translate('A list of media objects.');
    }

    /**
     * CSS class for the URL.
     *
     * @return string
     */
    public function listMenuClass(): string
    {
        return 'menu-list-obje';
    }

    /**
     * @param Tree                              $tree
     * @param array<bool|int|string|array|null> $parameters
     *
     * @return string
     */
    public function listUrl(Tree $tree, array $parameters = []): string
    {
        $parameters['tree'] = $tree->name();

        return route(static::class, $parameters);
    }

    /**
     * @return array<string>
     */
    public function listUrlAttributes(): array
    {
        return [];
    }

    /**
     * @param Tree $tree
     *
     * @return bool
     */
    public function listIsEmpty(Tree $tree): bool
    {
        return !DB::table('media')
            ->where('m_file', '=', $tree->id())
            ->exists();
    }

    /**
     * Handle URLs generated by older versions of webtrees
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function getListAction(ServerRequestInterface $request): ResponseInterface
    {
        return redirect($this->listUrl($request->getAttribute('tree'), $request->getQueryParams()));
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        $user = $request->getAttribute('user');
        assert($user instanceof UserInterface);

        $data_filesystem = Registry::filesystem()->data();

        Auth::checkComponentAccess($this, ModuleListInterface::class, $tree, $user);

        // Convert POST requests into GET requests for pretty URLs.
        if ($request->getMethod() === RequestMethodInterface::METHOD_POST) {
            return redirect($this->listUrl($tree, (array) $request->getParsedBody()));
        }

        $params  = $request->getQueryParams();
        $formats = Registry::elementFactory()->make('OBJE:FILE:FORM:TYPE')->values();
        $go      = $params['go'] ?? '';
        $page    = (int) ($params['page'] ?? 1);
        $max     = (int) ($params['max'] ?? 20);
        $folder  = $params['folder'] ?? '';
        $filter  = $params['filter'] ?? '';
        $subdirs = $params['subdirs'] ?? '';
        $format  = $params['format'] ?? '';

        $folders = $this->allFolders($tree);

        if ($go === '1') {
            $media_objects = $this->allMedia(
                $tree,
                $folder,
                $subdirs === '1' ? 'include' : 'exclude',
                'title',
                $filter,
                $format
            );
        } else {
            $media_objects = new Collection();
        }

        // Pagination
        $count = $media_objects->count();
        $pages = (int) (($count + $max - 1) / $max);
        $page  = max(min($page, $pages), 1);

        $media_objects = $media_objects->slice(($page - 1) * $max, $max);

        return $this->viewResponse('modules/media-list/page', [
            'count'           => $count,
            'filter'          => $filter,
            'folder'          => $folder,
            'folders'         => $folders,
            'format'          => $format,
            'formats'         => $formats,
            'max'             => $max,
            'media_objects'   => $media_objects,
            'page'            => $page,
            'pages'           => $pages,
            'subdirs'         => $subdirs,
            'data_filesystem' => $data_filesystem,
            'module'          => $this,
            'title'           => I18N::translate('Media'),
            'tree'            => $tree,
        ]);
    }

    /**
     * Generate a list of all the folders in a current tree.
     *
     * @param Tree $tree
     *
     * @return array<string>
     */
    private function allFolders(Tree $tree): array
    {
        $folders = DB::table('media_file')
            ->where('m_file', '=', $tree->id())
            ->where('multimedia_file_refn', 'NOT LIKE', 'http:%')
            ->where('multimedia_file_refn', 'NOT LIKE', 'https:%')
            ->where('multimedia_file_refn', 'LIKE', '%/%')
            ->pluck('multimedia_file_refn', 'multimedia_file_refn')
            ->map(static function (string $path): string {
                return dirname($path);
            })
            ->uniqueStrict()
            ->sort()
            ->all();

        // Ensure we have an empty (top level) folder.
        array_unshift($folders, '');

        return array_combine($folders, $folders);
    }

    /**
     * Generate a list of all the media objects matching the criteria in a current tree.
     *
     * @param Tree   $tree       find media in this tree
     * @param string $folder     folder to search
     * @param string $subfolders either "include" or "exclude"
     * @param string $sort       either "file" or "title"
     * @param string $filter     optional search string
     * @param string $format     option OBJE/FILE/FORM/TYPE
     *
     * @return Collection<Media>
     */
    private function allMedia(Tree $tree, string $folder, string $subfolders, string $sort, string $filter, string $format): Collection
    {
        $query = DB::table('media')
            ->join('media_file', static function (JoinClause $join): void {
                $join
                    ->on('media_file.m_file', '=', 'media.m_file')
                    ->on('media_file.m_id', '=', 'media.m_id');
            })
            ->where('media.m_file', '=', $tree->id());

        if ($folder === '') {
            // Include external URLs in the root folder.
            if ($subfolders === 'exclude') {
                $query->where(static function (Builder $query): void {
                    $query
                        ->where('multimedia_file_refn', 'NOT LIKE', '%/%')
                        ->orWhere('multimedia_file_refn', 'LIKE', 'http:%')
                        ->orWhere('multimedia_file_refn', 'LIKE', 'https:%');
                });
            }
        } else {
            // Exclude external URLs from the root folder.
            $query
                ->where('multimedia_file_refn', 'LIKE', $folder . '/%')
                ->where('multimedia_file_refn', 'NOT LIKE', 'http:%')
                ->where('multimedia_file_refn', 'NOT LIKE', 'https:%');

            if ($subfolders === 'exclude') {
                $query->where('multimedia_file_refn', 'NOT LIKE', $folder . '/%/%');
            }
        }

        // Apply search terms
        if ($filter !== '') {
            $query->where(static function (Builder $query) use ($filter): void {
                $like = '%' . addcslashes($filter, '\\%_') . '%';
                $query
                    ->where('multimedia_file_refn', 'LIKE', $like)
                    ->orWhere('descriptive_title', 'LIKE', $like);
            });
        }

        if ($format) {
            $query->where('source_media_type', '=', $format);
        }

        switch ($sort) {
            case 'file':
                $query->orderBy('multimedia_file_refn');
                break;
            case 'title':
                $query->orderBy('descriptive_title');
                break;
        }

        return $query
            ->get()
            ->map(Registry::mediaFactory()->mapper($tree))
            ->uniqueStrict()
            ->filter(GedcomRecord::accessFilter());
    }
}
