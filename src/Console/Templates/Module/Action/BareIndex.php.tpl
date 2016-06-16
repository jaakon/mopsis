<?php
namespace App\{ {MODULE }}\Action;

use Aura\Web\Request;
use Mopsis\Components\Action\AbstractAction;

class
{
}IndexAction extends AbstractAction {
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
