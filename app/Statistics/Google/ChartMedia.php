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

namespace Fisharebest\Webtrees\Statistics\Google;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Module\ModuleThemeInterface;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Statistics\Service\ColorService;
use Fisharebest\Webtrees\Tree;

use function app;
use function count;
use function strip_tags;

/**
 * A chart showing the top used media types.
 */
class ChartMedia
{
    /**
     * @var ColorService
     */
    private $color_service;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->color_service = new ColorService();
    }

    /**
     * Create a chart of media types.
     *
     * @param array<string,int> $media The list of media types to display
     * @param string            $color_from
     * @param string            $color_to
     * @param Tree              $tree
     *
     * @return string
     */
    public function chartMedia(array $media, string $color_from, string $color_to, Tree $tree): string
    {
        $data = [
            [
                I18N::translate('Type'),
                I18N::translate('Total')
            ],
        ];

        $element = Registry::elementFactory()->make('OBJE:FILE:FORM:TYPE');

        foreach ($media as $type => $count) {
            $data[] = [
                strip_tags($element->value($type, $tree)),
                $count
            ];
        }

        $colors = $this->color_service->interpolateRgb($color_from, $color_to, count($data) - 1);

        return view('statistics/other/charts/pie', [
            'title'    => null,
            'data'     => $data,
            'colors'   => $colors,
            'language' => I18N::languageTag(),
        ]);
    }
}
