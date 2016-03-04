<?php
namespace Mopsis\Components\Responder;

use Mopsis\Core\App;

abstract class AbstractWebResponder extends AbstractResponder
{
    protected function created()
    {
        $this->response->status->setCode(201);
    }

    protected function deleted()
    {
        $this->response->status->setCode(204);
    }

    protected function error()
    {
        $exception = $this->payload->get('exception');

        App::get('ErrorHandler')->handleException($exception);

        $this->response->status->setCode(500);
        $this->response->content->set($exception->getMessage());
        //        $this->response->content->set(static_page(500));
    }

    protected function newEntity()
    {
        $this->response->status->setCode(202);
    }

    protected function notCreated()
    {
        $this->response->status->setCode(503);
        $this->response->content->set(static_page(503));
    }

    protected function notDeleted()
    {
        $this->response->status->setCode(503);
        $this->response->content->set(static_page(503));
    }

    protected function notFound()
    {
        $this->response->status->setCode(404);
        $this->response->content->set(static_page(404));
    }

    protected function notUpdated()
    {
        $this->response->status->setCode(503);
        $this->response->content->set(static_page(503));
    }

    protected function notValid()
    {
        $this->response->status->setCode(422);
    }

    protected function seeOther()
    {
        $this->response->redirect->seeOther($this->payload->get('redirect') ?: $this->request->referer);
    }

    protected function updated()
    {
        $this->response->status->setCode(205);
    }
}
