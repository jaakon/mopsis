<?php
namespace Mopsis\Components\Responder;

use Mopsis\Core\App;

abstract class AbstractWebResponder extends AbstractResponder
{
    protected function error()
    {
        $exception = $this->payload->get('exception');

        App::get('ErrorHandler')->handleException($exception);

        $this->response->content->set($exception->getMessage());
    }

    protected function notCreated()
    {
        $this->response->content->set(staticPage(503));
    }

    protected function notDeleted()
    {
        $this->response->content->set(staticPage(503));
    }

    protected function notFound()
    {
        $this->response->content->set(staticPage(404));
    }

    protected function notImplemented()
    {
        $this->response->content->set(staticPage(501));
    }

    protected function notUpdated()
    {
        $this->response->content->set(staticPage(503));
    }

    protected function seeOther()
    {
        $this->response->redirect->seeOther($this->payload->getRedirect());
    }
}
