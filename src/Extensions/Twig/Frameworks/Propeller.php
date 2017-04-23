<?php
namespace Mopsis\Extensions\Twig\Frameworks;

use Mopsis\Extensions\TagBuilder;

class Propeller extends \Twig_Extension
{
    protected $validButtonClasses = [
        'active'   => 'active',
        'block'    => 'btn-block',
        'disabled' => 'disabled',
        'fab'      => 'pmd-btn-fab',
        'flat'     => 'pmd-btn-flat',
        'outline'  => 'pmd-btn-outline'
    ];

    protected $validButtonContexts = [
        'default' => 'btn-default',
        'primary' => 'btn-primary',
        'success' => 'btn-success',
        'info'    => 'btn-info',
        'warning' => 'btn-warning',
        'danger'  => 'btn-danger',
        'link'    => 'btn-link'
    ];

    protected $validButtonSizes = [
        'large' => 'btn-lg',
        'lg'    => 'btn-lg',
        'small' => 'btn-sm',
        'sm'    => 'btn-sm',
        'tiny'  => 'btn-xs',
        'xs'    => 'btn-xs'
    ];

    protected $validDropdownClasses = [
        'hover' => 'pmd-dropdown-hover'
    ];

    protected $validDropdownDirections = [
        'right'        => 'dropdown-menu-right',
        'bottom-right' => 'dropdown-menu-right',
        'top-left'     => 'pmd-dropdown-menu-top-left',
        'top-right'    => 'pmd-dropdown-menu-top-right'
    ];

    protected $validIconClasses = [
        'dark'     => 'md-dark',
        'light'    => 'md-light',
        'inactive' => 'md-inactive'
    ];

    protected $validIconSizes = [
        'large'  => 'pmd-lg',
        'lg'     => 'pmd-lg',
        'medium' => 'pmd-md',
        'md'     => 'pmd-md',
        'small'  => 'pmd-sm',
        'sm'     => 'pmd-sm',
        'tiny'   => 'pmd-xs',
        'xs'     => 'pmd-xs'
    ];

    public function getConfigForForm($link, $attributes = [])
    {
        return $this->getConfigObject(
            [
                'dataHref'   => $link,
                'dataTarget' => '#modal',
                'dataToggle' => 'modal'
            ],
            $attributes
        );
    }

    public function getConfigForLink($title, $href, $attributes = [])
    {
        return $this->getConfigObject(
            [
                'href' => $href,
                'text' => $title
            ],
            $attributes
        );
    }

    public function getConfigForModal($selector, $link = null, $attributes = [])
    {
        return $this->getConfigObject(
            [
                'href'       => $link,
                'dataTarget' => $selector,
                'dataToggle' => 'modal'
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
            new \Twig_SimpleFunction('dropdown', [
                $this,
                'getTagForDropdown'
            ], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('_form', [
                $this,
                'getConfigForForm'
            ], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('icon', [
                $this,
                'getTagForIcon'
            ], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('_link', [
                $this,
                'getConfigForLink'
            ], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('menu', [
                $this,
                'getTagForMenu'
            ], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('_modal', [
                $this,
                'getConfigForModal'
            ], ['is_safe' => ['html']])
        ];
    }

    public function getName()
    {
        return 'propeller';
    }

    public function getTagForButton($html, $link, array $attributes = [], array $classes = [])
    {
        switch (gettype($link)) {
            case 'array':
                return $this->getButton($html, $link, $attributes, $classes);
            case 'object':
                return $this->getButton($html, null, array_merge(
                    get_object_vars($link),
                    $attributes
                ), $classes);
        }

        return $this->getButton($html, $link, $attributes, $classes);
    }

    public function getTagForDropdown($html, $links, array $attributes = [], array $classes = [])
    {
        return TagBuilder::create('span')
            ->addClass('dropdown pmd-dropdown clearfix')
            ->html([
                $this->getDropdownButton($html, null, $attributes, $classes),
                $this->getDropdownList($links, $classes)
            ]);
    }

    public function getTagForIcon($symbol, array $classes = [])
    {
        return TagBuilder::create('i')
            ->addClass($this->getIconClasses($classes))
            ->html($symbol);
    }

    public function getTagForMenu($links, array $attributes = [], array $classes = [])
    {
        return $this->getTagForDropdown(
            $this->getTagForIcon('more_vert', ['dark']),
            $links,
            $attributes,
            array_merge($classes, ['fab', 'small', 'bottom-right'])
        );
    }

    protected function filterMatches($values, $validValues)
    {
        return array_diff($values, $validValues, array_keys($validValues));
    }

    protected function findMatches($values, $validValues, $default = null)
    {
        return array_values(array_intersect_key(
            $validValues,
            array_flip($values)
        )) ?: [$default];
    }

    protected function getButton($html, $link, array $attributes = [], array $classes = [])
    {
        if ($link === null) {
            $tag = TagBuilder::create('button')
                ->attr('type', 'button');
        } else {
            $tag = TagBuilder::create('a')
                ->attr('href', $link);
        }

        return $tag
            ->addClass($this->getButtonClasses($classes))
            ->attr($attributes)
            ->html($html);
    }

    protected function getButtonClasses($classes)
    {
        $context = $this->findMatches($classes, $this->validButtonContexts, 'btn-default')[0];
        $classes = $this->filterMatches($classes, $this->validButtonContexts);

        $size    = $this->findMatches($classes, $this->validButtonSizes)[0];
        $classes = $this->filterMatches($classes, $this->validButtonSizes);

        $additions = $this->findMatches($classes, $this->validButtonClasses);
        $classes   = $this->filterMatches($classes, $this->validButtonClasses);

        return implode(' ', array_filter(
            array_merge(
                ['btn', $context, $size, 'pmd-ripple-effect'],
                $additions,
                $classes
            )
        ));
    }

    protected function getConfigObject(...$data)
    {
        return (object) array_filter(array_merge(...$data));
    }

    protected function getDropdownButton($html, $link, array $attributes = [], array $classes = [])
    {
        $additions = $this->findMatches($classes, $this->validDropdownClasses);
        $classes   = $this->filterMatches($classes, $this->validDropdownClasses);
        $classes   = $this->filterMatches($classes, $this->validDropdownDirections);

        return $this->getButton($html, null, $attributes, $classes)
                    ->attr('data-toggle', 'dropdown')
                    ->addClasses($additions)
                    ->addClass('dropdown-toggle');
    }

    protected function getDropdownList(array $links, $classes = [])
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

    protected function getIconClasses($classes)
    {
        $size    = $this->findMatches($classes, $this->validIconSizes, 'pmd-sm')[0];
        $classes = $this->filterMatches($classes, $this->validIconSizes);

        $additions = $this->findMatches($classes, $this->validIconClasses);
        $classes   = $this->filterMatches($classes, $this->validIconClasses);

        return implode(' ', array_filter(
            array_merge(
                ['material-icons', $size],
                $additions,
                $classes
            )
        ));
    }

    protected function prepareLinks(array $links)
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
}
