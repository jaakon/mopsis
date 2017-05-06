<?php
namespace Mopsis\Components\Action;

use Mopsis\Core\Auth;

abstract class AbstractAction
{
    const ACCESS_PRIVATE = 'private';

    const ACCESS_PUBLIC = 'public';

    protected $access;

    protected $request;

    protected $responder;

    protected $service;

    public function redirectToLogin()
    {
        if (!Auth::isEnabled()) {
            return;
        }

        if ($this->access === null && !config('app.login.mandatory')) {
            return;
        }

        if ($this->access === self::ACCESS_PUBLIC || Auth::check()) {
            return;
        }

        $loginPage = config('app.login.page');

        if ($loginPage === $this->request->url->get(PHP_URL_PATH)) {
            return;
        }

        if (!$this->request->method->isGet() || $this->request->url->get(PHP_URL_PATH) === '/') {
            return redirect($loginPage);
        }

        $separator = parse_url($loginPage, PHP_URL_QUERY) === null ? '?' : '&';

        return redirect($loginPage . $separator . 'redirect=' . urlencode($this->request->url->get(PHP_URL_PATH)));
    }

    public function init()
    {
    }
}
