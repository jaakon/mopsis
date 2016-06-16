<?php namespace App\{{MODULE}}\Action;

use App\{{MODULE}}\Domain\{{DOMAIN}}Service as Service;
use App\{{MODULE}}\Responder\{{DOMAIN}}{{ACTION}}Responder as Responder;
use Aura\Web\Request;
use Mopsis\Components\Action\AbstractAction;

class {{DOMAIN}}{{ACTION}}Action extends AbstractAction
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
		$payload = $this->service->noop();
		$this->responder->setPayload($payload);

		return $this->responder->__invoke();
	}
}
