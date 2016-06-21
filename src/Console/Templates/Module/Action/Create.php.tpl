<?php namespace App\{{MODULE}}\Action;

use App\{{MODULE}}\{{DOMAIN}}Service as Service;
use App\{{MODULE}}\Responder\{{DOMAIN}}CreateResponder as Responder;
use Aura\Web\Request;
use Mopsis\Components\Action\AbstractAction;

class {{DOMAIN}}CreateAction extends AbstractAction
{
	public function __construct(Request $request, Service $service, Responder $responder)
	{
		$this->request   = $request;
		$this->service   = $service;
		$this->responder = $responder;

		$this->init();
	}

	public function __invoke()
	{
		$data    = $this->request->method->isPost() ? $this->request->post->get() : null;
		$payload = $this->service->create('{{INSTANCE}}', $data);
		$this->responder->setPayload($payload);

		return $this->responder->__invoke();
	}
}
