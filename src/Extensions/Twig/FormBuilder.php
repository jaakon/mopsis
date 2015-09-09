<?php namespace Mopsis\Extensions\Twig;

class FormBuilder extends \Twig_Extension
{
	protected $options = [];

	public function getName()
	{
		return 'formbuilder';
	}

	public function getFilters()
	{
		return [
			new \Twig_SimpleFilter('filter', 'array_filter', ['is_safe' => ['html']])
		];
	}

	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction('FormBuilder', [$this, 'getFormBuilder'], ['is_safe' => ['html']])
		];
	}

	public function getFormBuilder($id, $uri)
	{
		return app('Mopsis\FormBuilder\FormBuilder')->getForm($id, $uri, $this->options['forms'][$id]);
	}

	public function setOptions(array $options)
	{
		$this->options = $options;
	}
}
