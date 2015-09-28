<?php namespace Mopsis\Extensions\Twig;

use Mopsis\Extensions\TagBuilder;

class Bootstrap extends \Twig_Extension
{
	public function getName()
	{
		return 'bootstrap';
	}

	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction('button', [$this, 'button'], ['is_safe' => ['html']]),
			new \Twig_SimpleFunction('modalButton', [$this, 'modalButton'], ['is_safe' => ['html']]),
			new \Twig_SimpleFunction('modalLink', [$this, 'modalLink'], ['is_safe' => ['html']]),
			new \Twig_SimpleFunction('singleButtonDropdown', [$this, 'singleButtonDropdown'], ['is_safe' => ['html']]),
			new \Twig_SimpleFunction('splitButtonDropdown', [$this, 'splitButtonDropdown'], ['is_safe' => ['html']]),
		];
	}

	public function button($title, $uri, array $button = [], array $attributes = [])
	{
		$attributes['class'] = $this->getButtonClasses($button, $attributes['class']);

		if (empty($uri)) {
			$attributes['type'] = 'button';
		} else {
			$attributes['href'] = $uri;
		}

		return new TagBuilder(empty($uri) ? 'button' : 'a', $attributes, $title);
	}

	public function modalButton($uri, $title, array $button = [], array $options = [])
	{
		return new TagBuilder($button['type'] ? 'button' : 'a', [
			'class' => $this->getButtonClasses($button),
			'title' => $button['tooltip'],
			'data-' => [
				'toggle'  => 'modal',
				'target'  => '#modal',
				'title'   => $title,
				'href'    => $uri,
				'size'    => $options['size'] ?: 'lg',
				'on-hide' => $options['onHide'],
				'submit'  => $options['submit']
			]
		], $button['text'] ?: $title);
	}

	public function modalLink($uri, $title, array $options = [])
	{
		return new TagBuilder('a', [
			'href'  => '#',
			'class' => $options['class'],
			'title' => $options['tooltip'],
			'data-' => [
				'toggle'  => 'modal',
				'target'  => '#modal',
				'title'   => $title,
				'href'    => $uri,
				'size'    => $options['size'] ?: 'lg',
				'on-hide' => $options['onHide'],
				'submit'  => $options['submit']
			]
		], $options['text'] ?: $title);
	}

	public function singleButtonDropdown($title, array $links, array $button = [])
	{
		return new TagBuilder('div', [
			'class' => 'btn-group hidden-print'
		], [
			$this->dropdownButton($title, $button),
			$this->dropdownList($links)
		]);
	}

	public function splitButtonDropdown($title, $uri, array $links, array $button = [])
	{
		return new TagBuilder('div', [
			'class' => 'btn-group btn-group-fixed hidden-print'
		], [
			new TagBuilder('a', [
				'class' => $this->getButtonClasses($button, 'dropdown-toggle'),
				'href'  => $uri
			], $title),
			$this->dropdownButton(null, $button),
			$this->dropdownList($links)
		]);
	}

	protected function dropdownButton($title, array $button = [])
	{
		return new TagBuilder('button', [
			'class' => $this->getButtonClasses($button, 'dropdown-toggle'),
			'data-' => [
				'toggle' => 'dropdown'
			],
			'aria-' => [
				'haspopup' => 'true',
				'expanded' => 'false'
			],
		], [
			trim($title . ' <span class="caret"></span>')
		]);
	}

	protected function dropdownList(array $links)
	{
		$links = array_filter($links);

		if (!count($links)) {
			return;
		}

		return '<ul class="dropdown-menu">' . array_reduce($links, function ($html, $link) {
			return $html . ($link === '--' ? '<li class="divider"></li>' : '<li>' . $link . '</li>');
		}) . '</ul>';
	}

	protected function getButtonClasses($button, $additionalClasses = null)
	{
		return implode(' ', array_filter([
			'btn',
			'btn-' . ($button['type'] ?: 'default'),
			$button['size'] ? 'btn-' . $button['size'] : null,
			$button['class'],
			$additionalClasses,
			'hidden-print'
		]));
	}
}
