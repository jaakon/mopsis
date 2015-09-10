<?php namespace Mopsis\Components\Controller;

use Aura\Web\Request;
use Aura\Web\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mopsis\Components\View\View;
use Mopsis\Core\App;
use Mopsis\Core\Auth;

abstract class AbstractController
{
	protected $request;
	protected $filter;
	protected $view;

	protected $loginMandatory;

	public function __construct(Request $request, Filter $filter, View $view)
	{
		$this->request = $request;
		$this->filter  = $filter;
		$this->view    = $view;

		$this->init();
	}

	public function __call($method, $funcArgs)
	{
		$this->setTemplate($method);

		if (!method_exists($this, $method)) {
			throw new \Exception('invalid method "' . $method . '" for class "' . get_called_class() . '"');
		}

		try {
			$result = $this->$method(...$funcArgs) ?: 200;
		} catch (ModelNotFoundException $e) {
			return 'The session token is no longer valid.';
		}

		if ($result instanceof Response) {
			return $result;
		}

		$response = App::get('Aura\Web\Response');

		switch (gettype($result)) {
			case 'integer':
				$response->status->setCode($result);
				$response->content->set($this->view->__invoke());
				break;
			case 'array':
				$response->status->setCode($result[0]);
				$response->content->set($result[1]);
				break;
			default:
				throw new \Exception('invalid return type "' . gettype($result) . '"');
		}

		return $response;
	}

	public function init()
	{
		$this->checkAccess();
	}

	protected function checkAccess()
	{
		$loginMandatory = is_bool($this->loginMandatory) ? $this->loginMandatory : config('app.login.mandatory');

		if (!$loginMandatory || Auth::check()) {
			return true;
		}

		$loginPage = config('app.login.page');

		if ($loginPage === $this->request->url->get(PHP_URL_PATH)) {
			return true;
		}

		if (!$this->request->method->isGet()) {
			redirect($loginPage);
		}

		redirect($loginPage . '?redirect=' . urlencode($this->request->url->get(PHP_URL_PATH)));
	}

	protected function setTemplate($page)
	{
		$class = App::identify($this);

		$this->view->setTemplate($class[0] . '/views/' . $page);
	}
}
