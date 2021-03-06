<?php
namespace Mopsis\Extensions\Twig\Frameworks;

use Mopsis\Extensions\TagBuilder;

class Bootstrap3 extends \Twig_Extension
{
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

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('button', [
                $this,
                'button'
            ], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('icon', [
                $this,
                'icon'
            ], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('modal', [
                $this,
                'modal'
            ], ['is_safe' => ['html']])
        ];
    }

    public function getName()
    {
        return 'bootstrap3';
    }

    public function icon($symbol, $options = [])
    {
        return TagBuilder::create('i')
            ->addClass('glyphicon glyphicon-' . $symbol);
    }

    public function modal($text, $url, array $button = [], array $options = [])
    {
        $attr = array_filter([
            'class' => $options['class'],
            'data-' => array_filter([
                'toggle' => 'modal',
                'target' => '#modal',
                'title'  => $text,
                'href'   => $url,
                'size'   => $options['size'] ?: 'lg',
                'submit' => $options['submit']
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

    protected function getButton($text, $url, array $button, array $attr)
    {
        $tag = $url !== false ? TagBuilder::create('a')->attr('href', $url) : TagBuilder::create('button')->attr('type', 'button');

        return $tag->attr($attr)->addClass($this->getButtonClasses($button))->html($text);
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

    protected function getDropdownButton(array $button, $text = null)
    {
        return TagBuilder::create('button')
            ->attr([
                'data-' => ['toggle' => 'dropdown'],
                'aria-' => [
                    'haspopup' => 'true',
                    'expanded' => 'false'
                ]
            ])
            ->addClass($this->getButtonClasses($button))
            ->addClass('dropdown-toggle dropdown-menu-right')
            ->html($text . '&emsp;<span class="caret"></span>');
    }

    protected function getDropdownList(array $links)
    {
        return TagBuilder::create('ul')
            ->addClass('dropdown-menu dropdown-menu-right')
            ->html(array_reduce(
                $this->prepareLinks($links),
                function ($html, $link) {
                    return $html . ($link === '--' ? '<li class="divider"></li>' : '<li>' . $link . '</li>');
                }
            )
            );
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

    protected function prepareLinks($links)
    {
        foreach ($links as &$link) {
            if (isHtml($link)) {
                continue;
            }

            if (preg_match('/^(?:\[(.+?)\]\((.+?)\)|(.+)\|(.+?))$/', $link, $m)) {
                $link = TagBuilder::create('a')
                    ->attr('href', $m[2] ?: $m[4])
                    ->html($m[1] ?: $m[3])
                    ->toString();
            }
        }

        return arrayTrim($links, function ($item) {
            return $item !== '--';
        });
    }
}
