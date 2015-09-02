<?php namespace Mopsis\Components\Controller;

use Aura\Web\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mopsis\Components\Controller\Filter;
use Mopsis\Components\View\View;
use Mopsis\Core\App;
use Mopsis\Core\Auth;
use Mopsis\Core\Registry;
use Mopsis\FormBuilder\FormBuilder;

abstract class AbstractController
{
	protected $view;
	protected $filter;

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

		$response = App::make('Aura\Web\Response');

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

	public function __construct(View $view, Filter $filter)
	{
		$this->view   = $view;
		$this->filter = $filter;

		$this->checkAccess();
	}

	protected function checkAccess()
	{
		if (defined('static::ACCESS') && static::ACCESS === 'PUBLIC') {
			return true;
		}

		if (Auth::user()->exists) {
			return true;
		}

		if (!CORE_LOGIN_MANDATORY || !CORE_LOGIN_PAGE || CORE_LOGIN_PAGE === $_SERVER['SCRIPT_URL']) {
			return true;
		}

		if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
			redirect(CORE_LOGIN_PAGE);
		}

		redirect(CORE_LOGIN_PAGE.(strpos(CORE_LOGIN_PAGE, '?') !== false ? '&' : '?').'redirect='.urlencode($_SERVER['REQUEST_URI']));
	}

	protected function getRoute($page)
	{
		return resolve_path(class_basename($this). '/' . $page);
	}

	protected function setTemplate($page)
	{
		$this->view->setTemplate($this->getRoute($page));
	}
}
