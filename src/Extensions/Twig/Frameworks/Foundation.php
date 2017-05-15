<?php
namespace Mopsis\Extensions\Twig\Frameworks;

use Mopsis\Extensions\TagBuilder;

class Foundation extends \Twig_Extension
{
    protected $validButtonClasses = [
        'expanded' => 'expanded',
        'disabled' => 'disabled',
        'hollow'   => 'hollow',
        'dropdown' => 'dropdown'
    ];

    protected $validButtonContexts = [
        'primary'   => 'primary',
        'secondary' => 'secondary',
        'success'   => 'success',
        'info'      => 'info',
        'warning'   => 'warning',
        'danger'    => 'alert',
        'link'      => 'link'
    ];

    protected $validButtonSizes = [
        'xs' => 'tiny',
        'sm' => 'small',
        'md' => null,
        'lg' => 'large'
    ];

    protected $validIconClasses = [
        'inverse' => 'mdi-inverse',
        'spin'    => 'mdi-spin',
        'pulse'   => 'mdi-pulse',
        'fw'      => 'mdi-fw',
        'border'  => 'mdi-border'
    ];

    protected $validIconFlips = [
        'horizontal'      => 'mdi-flip-horizontal',
        'vertical'        => 'mdi-flip-vertical',
        'flip-horizontal' => 'mdi-flip-horizontal',
        'flip-vertical'   => 'mdi-flip-vertical'
    ];

    protected $validIconRotations = [
        '90'         => 'mdi-rotate-90',
        '180'        => 'mdi-rotate-180',
        '270'        => 'mdi-rotate-270',
        'rotate-90'  => 'mdi-rotate-90',
        'rotate-180' => 'mdi-rotate-180',
        'rotate-270' => 'mdi-rotate-270'
    ];

    protected $validIconSizes = [
        'lg' => 'mdi-lg',
        '2x' => 'mdi-2x',
        '3x' => 'mdi-3x',
        '4x' => 'mdi-4x',
        '5x' => 'mdi-5x'
    ];

    public function getConfigForModal($id, $link = null, $attributes = [])
    {
        if ($link === null) {
            return $this->getConfigObject(
                [
                    'dataOpen' => $id
                ],
                $attributes
            );
        }

        return $this->getConfigObject(
            [
                'dataOpenExtended' => $id,
                'href'             => $link
            ],
            $attributes
        );
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('button', [
                $this,
                'getTagForButton'
            ], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('icon', [
                $this,
                'getTagForIcon'
            ], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('_modal', [
                $this,
                'getConfigForModal'
            ], ['is_safe' => ['html']])
        ];
    }

    public function getName()
    {
        return 'foundation';
    }

    public function getTagForButton($html, $link, array $attributes = [], array $classes = [])
    {
        if ($classes === [] && !arrayIsAssoc($attributes)) {
            $classes    = $attributes;
            $attributes = [];
        }

        if (gettype($link) === 'object') {
            return $this->getButton($html, null, array_merge(
                get_object_vars($link),
                $attributes
            ), $classes);
        }

        return $this->getButton($html, $link, $attributes, $classes);
    }

    public function getTagForIcon($symbol, array $classes = [])
    {
        return TagBuilder::create('i')
            ->addClass($this->getIconClasses($classes))
            ->addClass('material-icons')
            ->html(str_replace(' ', '_', $symbol));
    }

    protected function filterMatches(&$values, $validValues, $default = null)
    {
        $matches = $this->findMatches($values, $validValues, $default);
        $values  = array_diff($values, $validValues, array_keys($validValues));

        return $matches;
    }

    protected function findMatches($values, $validValues, $default)
    {
        return array_values(array_intersect_key(
            $validValues,
            array_flip($values)
        )) ?: [$default];
    }

    protected function getButton($html, $link, array $attributes = [], array $classes = [])
    {
        if (isset($attributes['href'])) {
            $link = $attributes['href'];
            unset($attributes['href']);
        }

        if ($link === null) {
            $tag = TagBuilder::create('button')
                ->attr('type', 'button');
        } else {
            $tag = TagBuilder::create('a')
                ->attr('href', $link);
        }

        if (isset($attributes['tooltip'])) {
            $attributes['title']       = $attributes['tooltip'];
            $attributes['tooltip']     = null;
            $attributes['dataTooltip'] = true;
        }

        return $tag
            ->addClass($this->getButtonClasses($classes))
            ->attr($attributes)
            ->html($html);
    }

    protected function getButtonClasses($classes)
    {
        $classes   = explode(' ', implode(' ', $classes));
        $context   = $this->filterMatches($classes, $this->validButtonContexts, 'secondary')[0];
        $size      = $this->filterMatches($classes, $this->validButtonSizes)[0];
        $additions = $this->filterMatches($classes, $this->validButtonClasses);

        return implode(' ', array_filter(
            array_merge(
                ['button', $context, $size],
                $additions,
                $classes
            )
        ));
    }

    /*
    protected $validDropdownClasses = [
    'hover' => 'pmd-dropdown-hover'
    ];

    protected $validDropdownDirections = [
    'right'        => 'dropdown-menu-right',
    'bottom-right' => 'dropdown-menu-right',
    'top-left'     => 'pmd-dropdown-menu-top-left',
    'top-right'    => 'pmd-dropdown-menu-top-right'
    ];

    protected function getConfigForLink($title, $href, $attributes = [])
    {
    return $this->getConfigObject(
    [
    'href' => $href,
    'text' => $title
    ],
    $attributes
    );
    }

    protected function getTagForDropdown($html, $links, array $attributes = [], array $classes = [])
    {
    return TagBuilder::create('span')
    ->addClass('dropdown pmd-dropdown clearfix')
    ->html([
    $this->getDropdownButton($html, null, $attributes, $classes),
    $this->getDropdownList($links, $classes)
    ]);
    }

    protected function getTagForMenu($links, array $attributes = [], array $classes = [])
    {
    return $this->getTagForDropdown(
    $this->getTagForIcon('more_vert', ['dark']),
    $links,
    $attributes,
    array_merge($classes, ['fab', 'small', 'bottom-right'])
    );
    }

    private function getDropdownButton($html, $link, array $attributes = [], array $classes = [])
    {
    $additions = $this->findMatches($classes, $this->validDropdownClasses);
    $classes   = $this->filterMatches($classes, $this->validDropdownClasses);
    $classes   = $this->filterMatches($classes, $this->validDropdownDirections);

    return $this->getButton($html, null, $attributes, $classes)
    ->attr('data-toggle', 'dropdown')
    ->addClasses($additions)
    ->addClass('dropdown-toggle');
    }

    private function getDropdownList(array $links, $classes = [])
    {
    $direction = $this->findMatches($classes, $this->validDropdownDirections, '')[0];

    return TagBuilder::create('ul')
    ->addClasses(['dropdown-menu', $direction])
    ->html(array_reduce(
    $this->prepareLinks($links),
    function ($html, $link) {
    return $html . ($link === '--' ? '<li class="divider"></li>' : '<li>' . $link . '</li>');
    }
    ));
    }

    private function prepareLinks(array $links)
    {
    $links = array_map(function ($link) {
    if (gettype($link) === 'object') {
    return TagBuilder::create('a')
    ->attr('href', '#')
    ->attr('tabindex', '-1')
    ->attr(get_object_vars($link))
    ->html($link->text ?: $link->title)
    ->toString();
    }

    if (isHtml($link) || !preg_match('/^(?:\[(.+?)\]\((.+?)\)|(.+)\|(.+?))$/', $link, $m)) {
    return $link;
    }

    return TagBuilder::create('a')
    ->attr('href', $m[2] ?: $m[4])
    ->attr('tabindex', '-1')
    ->html($m[1] ?: $m[3])
    ->toString();
    }, $links);

    return arrayTrim($links, function ($item) {
    return $item !== '--';
    });
    }
     */
    private function getConfigObject(...$data)
    {
        return (object) array_filter(array_merge(...$data));
    }

    private function getIconClasses($classes)
    {
        $size      = $this->filterMatches($classes, $this->validIconSizes)[0];
        $rotations = $this->filterMatches($classes, $this->validIconRotations)[0];
        $flips     = $this->filterMatches($classes, $this->validIconFlips);
        $additions = $this->filterMatches($classes, $this->validIconClasses);

        return implode(' ', array_filter(
            array_merge(
                [$size, $rotations],
                $flips,
                $additions,
                $classes
            )
        ));
    }
}
