<?php namespace Mopsis\MVC;

use Aura\Accept\Accept;
use Aura\Web\Request;
use Aura\Web\Response;
use Mopsis\Core\View;

abstract class AbstractResponder
{
	protected $accept;
	protected $available     = [
		'text/html'        => '.twig',
		'application/json' => '.json'
	];
	protected $payload;
	protected $payloadData;
	protected $payloadMethod = [];
	protected $request;
	protected $response;
	protected $template;
	protected $view;

	public function __construct(Accept $accept, Request $request, Response $response, View $view)
	{
		$this->accept   = $accept;
		$this->request  = $request;
		$this->response = $response;
		$this->view     = $view;

		$this->init();
	}

	public function __invoke()
	{
		if ($this->payload === null) {
			return $this->notFound();
		}

		$class  = str_replace(__NAMESPACE__ . '\\Payload\\', '', get_class($this->payload));
		$method = $this->payloadMethod[$class] ?: 'notRecognized';

		$this->$method();

		return $this->response;
	}

	public function setPayload(PayloadInterface $payload)
	{
		$this->payload = $this->payloadData ? $this->addPayloadData($payload) : $payload;

		return $this;
	}

	public function setTemplate($template)
	{
		$this->template = $template;

		return $this;
	}

	protected function addPayloadData(PayloadInterface $payload)
	{
		$class = get_class($payload);

		return new $class(array_merge($this->payloadData, $payload->get()));
	}

	protected function init()
	{
		if (!isset($this->payloadMethod['Payload\Error'])) {
			$this->payloadMethod['Payload\Error'] = 'error';
		}

		$this->response->headers->set('X-Frame-Options', 'SAMEORIGIN');
	}

	protected function negotiateMediaType()
	{
		if (!$this->available || !$this->accept) {
			return true;
		}

		$available = array_keys($this->available);
		$media = $this->accept->negotiateMedia($available);

		if (!$media) {
			$this->response->status->set(406);
			$this->response->content->setType('text/plain');
			$this->response->content->set(implode(',', $available));

			return false;
		}

		$this->response->content->setType($media->getValue());

		return true;
	}

	protected function notRecognized()
	{
		$this->response->status->set(500);
		$this->response->content->set('Unknown domain payload status: "' . get_class($this->payload) . '"');

		return $this->response;
	}

	protected function renderView($template = null)
	{
		$contentType = $this->response->content->getType();
//		$path        = (new \ReflectionClass($this))->getNamespaceName() . '/views/';
		$extension   = $contentType ? $this->available[$contentType] : '.twig';

		$this->view
			->setTemplate(($template ?: $this->template) . $extension)
			->assign($this->payload->get());

		$this->response->content->set($this->view->__invoke());
	}
}
