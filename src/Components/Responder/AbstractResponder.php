<?php namespace Mopsis\Components\Responder;

use Aura\Accept\Accept;
use Aura\Web\Request;
use Aura\Web\Response;
use Mopsis\Components\Domain\Payload\PayloadInterface;
use Mopsis\Components\View\View;

abstract class AbstractResponder
{
	protected $accept;
	protected $available      = ['text/html' => '.twig', 'application/json' => '.json'];
	protected $payload;
	protected $payloadData;
	protected $payloadMethods = [];
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

		$method = $this->payloadMethods[$this->payload->getName()] ?: 'notRecognized';

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

	protected function getViewPath()
	{
		return preg_replace('/^\w+\\\(\w+)\\\.+$/', '$1/', get_called_class());
	}

	protected function init()
	{
		if (!isset($this->payloadMethods['Payload\Error'])) {
			$this->payloadMethods['Payload\Error'] = 'error';
		}

		$this->response->headers->set('X-Frame-Options', 'SAMEORIGIN');
	}

	protected function negotiateMediaType()
	{
		if (!$this->available || !$this->accept) {
			return true;
		}

		$available = array_keys($this->available);
		$media     = $this->accept->negotiateMedia($available);

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
		$extension   = $contentType ? $this->available[$contentType] : '.twig';

		$this->view->setTemplate($this->getViewPath() . ($template ?: $this->template) . $extension)->assign($this->payload->get());

		$this->response->content->set($this->view->__invoke());
	}
}
