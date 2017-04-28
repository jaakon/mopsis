<?php
namespace Mopsis\Components\Action;

use Mopsis\Core\Auth;

abstract class AbstractAction
{
    const ACCESS_PRIVATE = 'private';

    const ACCESS_PUBLIC = 'public';

    protected $access = self::ACCESS_PRIVATE;

    protected $request;

    protected $responder;

    protected $service;

    public function checkAccess()
    {
        $loginMandatory = is_bool($this->loginMandatory) ? $this->loginMandatory : config('app.login.mandatory');

        if (!$loginMandatory || Auth::check()) {
            return true;
        }

        $loginPage = config('app.login.page');

        if ($loginPage === $this->request->url->get(PHP_URL_PATH)) {
            return true;
        }

        if (!$this->request->method->isGet() || $this->request->url->get(PHP_URL_PATH) === '/') {
            return redirect($loginPage);
        }

        $separator = parse_url($loginPage, PHP_URL_QUERY) === null ? '?' : '&';

        return redirect($loginPage . $separator . 'redirect=' . urlencode($this->request->url->get(PHP_URL_PATH)));
    }

    public function init()
    {
        if (Auth::isEnabled()) {
            $this->checkAccess();
        }
    }
}
