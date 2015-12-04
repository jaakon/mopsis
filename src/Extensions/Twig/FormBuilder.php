<?php namespace Mopsis\Extensions\Twig;

class FormBuilder extends \Twig_Extension
{
	protected $configurations = [];

	public function getName()
	{
		return 'formbuilder';
	}

	public function getFilters()
	{
		return [new \Twig_SimpleFilter('filter', 'array_filter', ['is_safe' => ['html']])];
	}

	public function getFunctions()
	{
		return [new \Twig_SimpleFunction('FormBuilder', [$this, 'getFormBuilder'], ['is_safe' => ['html']])];
	}

	public function getFormBuilder($formId, $uri)
	{
		$defaults = ['errors' => [], 'options' => [], 'settings' => [], 'values' => []];
		$config   = (object) array_merge($defaults, $this->configurations[$formId] ?: []);

		return app('Mopsis\FormBuilder\FormBuilder')->getForm($formId, $uri, $config);
	}

	public function setConfigurations(array $configurations)
	{
		$this->configurations = $configurations;
	}
}
