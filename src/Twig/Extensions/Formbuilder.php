<?php namespace Mopsis\Twig\Extensions;

class Formbuilder extends \Twig_Extension
{
	private $_options = [];

	public function __construct(array $options)
	{
		$this->_options = $options;
	}

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
		return (new \Mopsis\Core\FormBuilder())->getForm($id, $uri, $this->_options['forms'][$id]);
	}
}
