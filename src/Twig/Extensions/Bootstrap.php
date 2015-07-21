<?php namespace Mopsis\Twig\Extensions;

class Bootstrap extends \Twig_Extension
{
	public function getName()
	{
		return 'bootstrap';
	}

	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction('serializeAttributes', [$this, 'serializeAttributes'], ['is_safe' => ['html']])
		];
	}

	public function serializeAttributes($data, $prefix = '')
	{
		$attributes = '';

		foreach (array_filter($data) as $key => $value) {
			$attributes .= ' ' . $prefix . $key . '="' . htmlspecialchars($value, ENT_QUOTES) . '"';
		}

		return $attributes;
	}
}
