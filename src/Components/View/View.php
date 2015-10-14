<?php namespace Mopsis\Components\View;

use Aura\Web\Request;
use Mopsis\Components\Domain\AbstractFilter as Filter;
use Mopsis\Core\App;
use Twig_Environment as Renderer;

class View
{
	protected $renderer;
	protected $request;

	private $template;
	private $data       = [];
	private $forms      = [];
	private $extensions = [];
	private $functions  = [];
	private $filters    = [];

	public function __construct(Renderer $renderer, Request $request, array $extensions = [])
	{
		$this->renderer   = $renderer;
		$this->request    = $request;
		$this->extensions = $extensions;
	}

	public function __invoke()
	{
		foreach ($this->extensions as $extension) {
			$this->renderer->addExtension($extension);
		}

		foreach ($this->filters as $filter) {
			$this->renderer->addFilter($filter);
		}

		foreach ($this->functions as $function) {
			$this->renderer->addFunction($function);
		}

		$this->extensions = [];
		$this->filters    = [];
		$this->functions  = [];

		if ($this->renderer->hasExtension('formbuilder')) {
			$this->renderer->getExtension('formbuilder')->setOptions(['forms' => $this->forms]);
		}

		return $this->renderer->render($this->template, $this->data);
	}

	public function addExtension($extension)
	{
		$this->extensions[] = $extension;

		return $this;
	}

	public function addFilter($name, $filter = null)
	{
		$this->filters[] = new \Twig_SimpleFilter($name, $filter ?: $name, ['is_safe' => ['html']]);

		return $this;
	}

	public function addFunction($name, $function = null)
	{
		$this->functions[] = new \Twig_SimpleFunction($name, $function ?: $name, ['is_safe' => ['html']]);

		return $this;
	}

	public function assign($data)
	{
		$this->data = array_merge($this->data, object_to_array($data));

		return $this;
	}

	public function clearCache()
	{
		$cachePath = App::get('twig.config')['cache'];

		if (!$cachePath) {
			return $this;
		}

		$filesystem = App::make('Filesystem');

		$filesystem->getAdapter()->setPathPrefix(dirname($cachePath));
		$filesystem->deleteDir(basename($cachePath));
		$filesystem->createDir(basename($cachePath));

		return $this;
	}

	public function prefillForm($formId, Filter $filter)
	{
		$messages = $filter->getMessages();

		$this->setFormValues($formId, $this->request->post->get())->setFormErrors($formId, array_keys($messages))->assign(['errors' => array_flatten($messages)]);

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
		$this->template = $template;

		if (!pathinfo($this->template, PATHINFO_EXTENSION)) {
			$this->template .= '.twig';
		}

		return $this;
	}

	private function initializeForm($formId)
	{
		if (empty($formId)) {
			throw new \Exception('form id must not be empty');
		}

		if (!isset($this->forms[$formId])) {
			$this->forms[$formId] = [
				'values'  => [],
				'options' => [],
				'errors'  => []
			];
		}
	}
}
