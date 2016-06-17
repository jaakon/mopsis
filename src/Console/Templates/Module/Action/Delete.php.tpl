<?php namespace App\{{MODULE}}\Action;

use App\{{MODULE}}\{{DOMAIN}}Service as Service;
use App\{{MODULE}}\Responder\{{DOMAIN}}DeleteResponder as Responder;
use Aura\Web\Request;
use Mopsis\Components\Action\AbstractAction;

class {{DOMAIN}}DeleteAction extends AbstractAction
{
	public function __construct(Request $request, Service $service, Responder $responder)
	{
		$this->request   = $request;
		$this->service   = $service;
		$this->responder = $responder;

		$this->init();
	}

	public function __invoke($token)
	{
		$payload = $this->service->delete($token);
		$this->responder->setPayload($payload);

		return $this->responder->__invoke();
	}
}
