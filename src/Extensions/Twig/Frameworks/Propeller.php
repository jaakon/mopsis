<?php
namespace Mopsis\Extensions\Twig\Frameworks;

use Mopsis\Extensions\TagBuilder;

class Propeller extends \Twig_Extension
{
    /*
    public function button($text, array $links, array $button = [], array $attr = [])
    {
    $url    = array_shift($links);
    $hasUrl = $url !== false;

    $links    = array_filter($links);
    $hasLinks = !!count($links);

    if (!$button['type']) {
    $button['type'] = 'default';
    }

    if ($button['icon']) {
    $attr['title']   = $text;
    $button['width'] = 'narrow';
    $text            = $this->icon($button['icon']);
    }

    if ($button['tooltip']) {
    $attr['title'] = $button['tooltip'];
    }

    if ($hasLinks) {
    if ($hasUrl) {
    return $this->getSplitButtonDropdown($text, $url, $links, $button, $attr);
    }

    return $this->getSingleButtonDropdown($text, $links, $button, $attr);
    }

    return $this->getButton($text, $url, $button, $attr)->addClass('hidden-print');
    }

    public function modal($text, $url, array $button = [], array $options = [])
    {
    $attr = array_filter([
    'class' => $options['class'],
    'data-' => array_filter([
    'toggle'  => 'modal',
    'target'  => '#modal',
    'title'   => $text,
    'href'    => $url,
    'size'    => $options['size'] ?: 'lg',
    'submit'  => $options['submit']
    ])
    ]);

    if ($button['icon']) {
    $attr['title']   = $text;
    $button['width'] = 'narrow';
    $text            = $this->icon($button['icon']);
    }

    if ($button['tooltip']) {
    $attr['title'] = $button['tooltip'];
    }

    if ($button['type']) {
    return $this->getButton($button['text'] ?: $text, false, $button, $attr);
    }

    return $this->getButton($button['text'] ?: $text, '#', [], $attr);
    }

    protected function getButtonClasses(array $button)
    {
    if (!$button['type']) {
    return '';
    }

    $classes = 'btn btn-' . $button['type'];

    if ($button['size']) {
    $classes .= ' btn-' . $button['size'];
    }

    if ($button['width']) {
    $classes .= ' btn-' . $button['width'];
    }

    return $classes;
    }

    protected function getSingleButtonDropdown($text, array $links, array $button, array $attr)
    {
    return TagBuilder::create('div')
    ->attr($attr)
    ->addClass('btn-group hidden-print')
    ->html([
    $this->getDropdownButton($button, $text),
    $this->getDropdownList($links)
    ]);
    }

    protected function getSplitButtonDropdown($text, $url, array $links, array $button, array $attr)
    {
    return TagBuilder::create('div')
    ->attr($attr)
    ->addClass('btn-group btn-group-fixed hidden-print')
    ->html([
    isHtml((string) $url) ? $url : $this->getButton($text, $url, $button, []),
    $this->getDropdownButton($button),
    $this->getDropdownList($links)
    ]);
    }
     */

    public function fab($content, array $btnClasses = ['default', 'sm'], array $pmdClasses = [])
    {
        return TagBuilder::create('button')
            ->attr('type', 'button')
            ->addClass('btn pmd-btn-fab pmd-ripple-effect')
            ->addClasses($btnClasses, 'btn-')
            ->addClasses($pmdClasses, 'pmd-btn-')
            ->html($content);
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('fab', [
                $this,
                'fab'
            ], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('icon', [
                $this,
                'icon'
            ], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('menu', [
                $this,
                'menu'
            ], ['is_safe' => ['html']])
        ];
    }

    public function getName()
    {
        return 'propeller';
    }

    public function icon($symbol, $size = 'sm', array $mdClasses = [])
    {
        return TagBuilder::create('i')
            ->addClass('material-icons pmd-' . $size)
            ->addClasses($mdClasses, 'md-')
            ->html($symbol);
    }

    public function menu(array $links, array $btnClasses = ['default', 'sm'], array $pmdClasses = ['flat'])
    {
        return TagBuilder::create('span')
            ->addClass('dropdown pmd-dropdown clearfix')
            ->html([
                $this->getDropdownButton($btnClasses, $pmdClasses),
                $this->getDropdownList($links)
            ]);
    }

    protected function getButton($text, $url, array $attributes = [], array $classes = [])
    {
        if ($url !== false) {
            $tag = TagBuilder::create('a')
                ->attr('href', $url);
        } else {
            $tag = TagBuilder::create('button')
                ->attr('type', 'button');
        }

        return $tag
            ->attr($attributes)
            ->addClasses($classes)
            ->html($text);
    }

    protected function getDropdownButton(array $btnClasses = [], array $pmdClasses = [])
    {
        return TagBuilder::create('button')
            ->attr('type', 'button')
            ->attr('data-toggle', 'dropdown')
            ->addClass('btn pmd-btn-fab pmd-ripple-effect')
            ->addClasses($btnClasses, 'btn-')
            ->addClasses($pmdClasses, 'pmd-btn-')
            ->html($this->icon('more_vert'));
    }

    protected function getDropdownList(array $links, $direction = 'left', $pmdClasses = [])
    {
        $prefix = $direction !== 'right' ? 'pmd-' : '';

        return TagBuilder::create('ul')
            ->addClass('dropdown-menu ' . $prefix . 'dropdown-menu-' . $direction)
            ->addClasses($pmdClasses, 'pmd-dropdown-')
            ->html(array_reduce(
                $this->prepareLinks($links),
                function ($html, $link) {
                    return $html . ($link === '--' ? '<li class="divider"></li>' : '<li>' . $link . '</li>');
                }
            ));
    }

    protected function prepareLinks(array $links)
    {
        array_walk($links, function ($link) {
            if (isHtml($link)) {
                return;
            }

            if (!preg_match('/^(?:\[(.+?)\]\((.+?)\)|(.+)\|(.+?))$/', $link, $m)) {
                return;
            }

            $link = TagBuilder::create('a')
                ->attr('href', $m[2] ?: $m[4])
                ->attr('tabindex', '-1')
                ->html($m[1] ?: $m[3])
                ->toString();
        });

        return arrayTrim($links, function ($item) {
            return $item !== '--';
        });
    }
}
