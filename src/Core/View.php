<?php namespace Mopsis\Core;

use \Twig_Environment as Renderer;

class View
{
	protected $renderer;

	private $template;
	private $useCache   = false;
	private $data       = [];
	private $forms      = [];
	private $extensions = [];
	private $functions  = [];
	private $filters    = [];

	public function __construct(Renderer $renderer, array $extensions = [])
	{
		$this->renderer   = $renderer;
		$this->extensions = $extensions;
	}

	public function __invoke()
	{
		foreach ($this->extensions as $extension) {
			$this->renderer->addExtension($extension);
		}

		foreach ($this->filters as $name => $filter) {
			$this->renderer->addFilter(new \Twig_SimpleFilter($name, $filter, ['is_safe' => ['html']]));
		}

		foreach ($this->functions as $name => $function) {
			$this->renderer->addFunction(new \Twig_SimpleFunction($name, $function, ['is_safe' => ['html']]));
		}

		$this->extensions = [];
		$this->filters    = [];
		$this->functions  = [];

		if ($this->renderer->hasExtension('formbuilder')) {
			$this->renderer->getExtension('formbuilder')->setOptions(['forms' => $this->forms]);
		}

		$html = $this->renderer->render($this->template, $this->data);

		while (preg_match('/<(.+?)>\s*<attribute name="(.+?)" value="(.+?)">/', $html, $m)) {
			$html = str_replace($m[0], '<' . $m[1] . ' ' . $m[2] . '="' . $m[3] . '">', $html);
		}

		return $html;
	}

	public function addExtension($extension)
	{
		$this->extensions[] = $extension;
		return $this;
	}

	public function addFilter($name, $filter = null)
	{
		$this->filters[$name] = $filter ?: $name;
		return $this;
	}

	public function addFunction($name, $function = null)
	{
		$this->functions[$name] = $function ?: $name;
		return $this;
	}

	public function assign($data)
	{
		$this->data = array_merge($this->data, object2array($data));
		return $this;
	}

	public function clearCache()
	{
		if ($this->useCache) {
			$this->renderer->clearCacheFiles();
		}

		return $this;
	}

	public function prefillForm($formId, \Mopsis\Validation\ValidationFacade $facade)
	{
		$this->initializeForm($formId);

		if ($facade->isValid()) {
			return $this;
		}

		$this
			->setFormValues($formId, $facade->getRawRequest()->toArray())
			->setFormErrors($formId, $facade->getInvalidFields())
			->assign(['errors' => $facade->getErrors()]);

		return $this;
	}

	public function setFormErrors($formId, ...$data)
	{
		$this->initializeForm($formId);
		$this->forms[$formId]['errors'] = array_merge($this->forms[$formId]['errors'], ...$data);

		return $this;
	}

	public function setFormOptions($formId, ...$data)
	{
		$this->initializeForm($formId);
		$this->forms[$formId]['options'] = array_merge($this->forms[$formId]['options'], ...$data);

		return $this;
	}

	public function setFormValues($formId, ...$data)
	{
		$this->initializeForm($formId);
		$this->forms[$formId]['values'] = array_merge($this->forms[$formId]['values'], ...$data);

		return $this;
	}

	public function setTemplate($template)
	{
		$this->template = preg_replace('/^App\\\/', '', $template);
		return $this;
	}

	public function useCache($boolean)
	{
		$this->useCache = $boolean;
		return $this;
	}

	private function initializeForm($formId)
	{
		if (empty($formId)) {
			throw new \Exception('form id must not be empty');
		}

		if (!isset($this->forms[$formId])) {
			$this->forms[$formId] = ['values' => [], 'options' => [], 'errors' => []];
		}
	}
}
