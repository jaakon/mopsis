<?php namespace Mopsis\Core;

use \Twig_Environment as Renderer;

class View
{
	protected $renderer;

	private $_template   = null;
	private $_useCache   = false;
	private $_data       = [];
	private $_forms      = [];
	private $_extensions = [];
	private $_functions  = [];
	private $_filters    = [];

	public function __construct(Renderer $renderer, array $extensions = [])
	{
		$this->renderer    = $renderer;
		$this->_extensions = $extensions;
	}

	public function __invoke()
	{
		foreach ($this->_extensions as $extension) {
			$this->renderer->addExtension($extension);
		}

		foreach ($this->_filters as $name => $filter) {
			$this->renderer->addFilter(new \Twig_SimpleFilter($name, $filter, ['is_safe' => ['html']]));
		}

		foreach ($this->_functions as $name => $function) {
			$this->renderer->addFunction(new \Twig_SimpleFunction($name, $function, ['is_safe' => ['html']]));
		}

		if ($this->renderer->hasExtension('formbuilder')) {
			$this->renderer->getExtension('formbuilder')->setOptions(['forms' => $this->_forms]);
		}

		$html = $this->renderer->render($this->_template, $this->_data);

		while (preg_match('/<(.+?)>\s*<attribute name="(.+?)" value="(.+?)">/', $html, $m)) {
			$html = str_replace($m[0], '<' . $m[1] . ' ' . $m[2] . '="' . $m[3] . '">', $html);
		}

		return $html;
	}

	public function addExtension($extension)
	{
		$this->_extensions[] = $extension;
		return $this;
	}

	public function addFilter($name, $filter = null)
	{
		$this->_filters[$name] = $filter ?: $name;
		return $this;
	}

	public function addFunction($name, $function = null)
	{
		$this->_functions[$name] = $function ?: $name;
		return $this;
	}

	public function assign($data)
	{
		$this->_data = array_merge($this->_data, object2array($data));
		return $this;
	}

	public function clearCache()
	{
		if ($this->_useCache) {
			$this->renderer->clearCacheFiles();
		}

		return $this;
	}

	public function prefillForm($formId, \Mopsis\Validation\ValidationFacade $facade)
	{
		$this->_initializeForm($formId);

		if ($facade->isValid()) {
			return $this;
		}

		$this
			->setFormValues($formId, $facade->getRawRequest()->toArray())
			->setFormErrors($formId, $facade->getInvalidFields())
			->assign(['errors' => $facade->getErrors()]);

		return $this;
	}

	public function setFormErrors($formId, array $data)
	{
		$this->_initializeForm($formId);
		$this->_forms[$formId]['errors'] = $data;

		return $this;
	}

	public function setFormOptions($formId, array $data)
	{
		$this->_initializeForm($formId);

		foreach ($data as $select => $options) {
			$this->_forms[$formId]['options'][$select] = $options;
		}

		return $this;
	}

	public function setFormValues($formId, array $data1)
	{
		$this->_initializeForm($formId);

		foreach (array_slice(func_get_args(), 1) as $data) {
			foreach ($data as $key => $value) {
				$this->_forms[$formId]['values'] = array_merge($this->_forms[$formId]['values'], implode_objects($value, $key, '.'));
			}
		}

		return $this;
	}

	public function setTemplate($template)
	{
		$this->_template = $template.'.twig';
		return $this;
	}

	public function useCache($boolean)
	{
		$this->_useCache = $boolean;
		return $this;
	}

	private function _initializeForm($formId)
	{
		if (empty($formId)) {
			throw new \Exception('form id must not be empty');
		}

		if (!isset($this->_forms[$formId])) {
			$this->_forms[$formId] = ['values' => [], 'options' => [], 'errors' => []];
		}
	}
}
