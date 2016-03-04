<?php
namespace Mopsis\Extensions\Aura\Web;

class ResponseSender extends \Aura\Web\ResponseSender
{
    public function __invoke()
    {
        if (!headers_sent()) {
            $this->sendStatus();
            $this->sendHeaders();
            $this->sendCookies();
        }

        $this->sendContent();
    }
}
