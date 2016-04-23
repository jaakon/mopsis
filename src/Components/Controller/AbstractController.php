<?php namespace Mopsis\Components\Controller;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mopsis\Components\View\View;
use Mopsis\Core\User;
use Mopsis\Core\Registry;
use Mopsis\FormBuilder\FormBuilder;
use Mopsis\Validation\ValidationFacade as Facade;

abstract class AbstractController
{
	protected $view;
	protected $facade;

	public function __construct(View $view, Facade $facade)
	{
		$this->view   = $view;
		$this->facade = $facade;
	}

	public function __invoke($class)
	{
		return \App::make('\\App\\Controllers\\' . $class);
	}

	public function callMethod($method, array $funcArgs = [])
	{
		$this->checkAccess();
		$this->loadValidations($method);
		$this->setTemplate($method);

		try {
			return $this->{$method}(...$funcArgs) ?: $this->view;
		} catch (ModelNotFoundException $e) {
			return 'The session token is no longer valid.';
		}
	}

	protected function setTemplate($page)
	{
		$this->view->setTemplate($this->getRoute($page));
	}

	private function addValidation($field, $rule, $message)
	{
		if (preg_match('/(\w+):(.+)/', $rule, $m)) {
			$validator = $this->facade->addRule($field, $m[1], preg_match('/^`(.+)`$/', $m[2], $n) ? eval('return '.$n[1].';') : $m[2]);
		} else {
			$validator = $this->facade->addRule($field, $rule);
		}

		if ($message) {
			$validator->withMessage(preg_match('/^__(.+)/', $message, $m) ? __($m[1]) : $message);
		}
	}

	protected function checkAccess()
	{
		if (CORE_LOGIN_MANDATORY && CORE_LOGIN_PAGE && CORE_LOGIN_PAGE !== $_SERVER['SCRIPT_URL'] && !\Mopsis\Core\Auth::user()->exists) {
			if (defined('static::ACCESS') && static::ACCESS === 'PUBLIC') {
				return true;
			}

			if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
				redirect(CORE_LOGIN_PAGE);
			}

			redirect(CORE_LOGIN_PAGE.(strpos(CORE_LOGIN_PAGE, '?') !== false ? '&' : '?').'redirect='.urlencode($_SERVER['REQUEST_URI']));
		}
	}

	private function getRoute($page)
	{
		return resolve_path(getClassName(get_called_class()).'/'.$page);
	}

	private function loadValidations($method)
	{
		if (!defined('CORE_FORMS') || !file_exists(CORE_FORMS)) {
			return;
		}

		$route = getClassName(get_called_class()).'.'.$method;

		if (!Registry::has('forms/'.$route)) {
			return;
		}

		$this->facade->addRule('csrf', 'error', !isset($_SESSION['csrf']) || $_POST[$_SESSION['csrf']['key']] !== $_SESSION['csrf']['value'])->withMessage('Ungültiges oder abgelaufenes Sicherheitstoken. Bitte Formular erneut versenden.');

		foreach ((new FormBuilder())->getRules(Registry::get('forms/'.$route)) as $field => $validations) {
			if (!count($validations)) {
				$this->addValidation($field, null, null);
				continue;
			}

			if (!array_key_exists('required', $validations)) {
				if (is_string($this->facade->getRawRequest()->{$field}) && !strlen($this->facade->getRawRequest()->{$field})) {
					continue;
				}

				if (is_array($this->facade->getRawRequest()->{$field}) && !count($this->facade->getRawRequest()->{$field})) {
					continue;
				}
			}

			foreach ($validations as $rule => $message) {
				$this->addValidation($field, $rule, $message);
			}
		}
	}
}
