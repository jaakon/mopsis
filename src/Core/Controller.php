<?php namespace Mopsis\Core;

abstract class Controller
{
	protected $_user     = null;
	protected $_view     = null;
	protected $_facade   = null;
	protected $_logger   = null;

	public function __construct(\Mopsis\Core\User $user, \Mopsis\Renderer\iRenderer $renderer, \Mopsis\Validation\ValidationFacade $facade)
	{
		$this->_user   = $user;
		$this->_view   = $renderer;
		$this->_facade = $facade;

		$this->_init();
	}

	public function __invoke($class)
	{
		return \App::make('\\Controllers\\'.$class);
	}

	public function callMethod($method, Array $funcArgs = [])
	{
		$this->_checkAccess();
		$this->_loadValidations($method);
		$this->_setTemplate($method);

		try {
			return $this->{$method}(...$funcArgs) ?: $this->_view;
		} catch (\TokenException $e) {
			return 'The session token is no longer valid.';
		}
	}

	public function store($data)
	{
		\Mopsis\Extensions\Storage::put($data, __CLASS__);
	}

	protected function _restrictAccess($allowGooglebot = false, $accessFor = 'user')
	{
		if ($allowGooglebot && preg_match('/googlebot/i', $_SERVER['HTTP_USER_AGENT'])) {
			$host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
			if (strpos($host, '.googlebot.com') !== false && gethostbyname($host) === $_SERVER['REMOTE_ADDR']) {
				return;
			}
		}

		if ($accessFor === 'user' && $this->_user->isBound()) {
			return;
		}

		if ($accessFor === 'admin' && $this->_user->isAuthorized()) {
			return;
		}

		if (!headers_sent()) {
			header('HTTP/1.1 401 Unauthorized');
		}

		throw new \RuntimeException($accessFor.'-access-denied');
	}

	protected function _setTemplate($page)
	{
		$this->_view->setTemplate($this->_getRoute($page));
	}

	private function _addValidation($field, $rule, $message)
	{
		if (preg_match('/(\w+):(.+)/', $rule, $m)) {
			$validator = $this->_facade->addRule($field, $m[1], preg_match('/^`(.+)`$/', $m[2], $n) ? eval('return '.$n[1].';') : $m[2]);
		} else {
			$validator = $this->_facade->addRule($field, $rule);
		}

		if ($message) {
			$validator->withMessage(preg_match('/^__(.+)/', $message, $m) ? __($m[1]) : $message);
		}
	}

	private function _checkAccess()
	{
		if ($this->_user->isBound() || !CORE_LOGIN_MANDATORY || !CORE_LOGIN_PAGE || CORE_LOGIN_PAGE === $_SERVER['ROUTE']) {
			return;
		}

		$rac = new \Addendum\ReflectionAnnotatedClass($this);

		if ($rac->hasAnnotation('AccessLevel') && $rac->getAnnotation('AccessLevel')->value === 'PUBLIC') {
			return true;
		}

		if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
			redirect(CORE_LOGIN_PAGE);
		}

		redirect(CORE_LOGIN_PAGE.(strpos(CORE_LOGIN_PAGE, '?') !== false ? '&' : '?').'redirect='.urlencode($_SERVER['REQUEST_URI']));
	}

	private function _fetch($cleanUp = true)
	{
		return \Mopsis\Extensions\Storage::fetch($cleanUp);
	}

	private function _getRoute($page)
	{
		return resolve_path(getClassName(get_called_class()).'/'.$page);
	}

	private function _init()
	{
	}

	private function _loadValidations($method)
	{
		if (!defined('CORE_FORMS') || !file_exists(CORE_FORMS)) {
			return;
		}

		$route = getClassName(get_called_class()).'.'.$method;

		if (!Registry::has('forms/'.$route)) {
			return;
		}

		$this->_facade->addRule('csrf', 'error', !isset($_SESSION['csrf']) || $_POST[$_SESSION['csrf']['key']] !== $_SESSION['csrf']['value'])->withMessage('Ungültiges oder abgelaufenes Sicherheitstoken. Bitte Formular erneut versenden.');

		foreach ((new FormBuilder())->getRules(Registry::get('forms/'.$route)) as $field => $validations) {
			if (!count($validations)) {
				$this->_addValidation($field, null, null);
				continue;
			}

			if (!array_key_exists('required', $validations)) {
				if (is_string($this->_facade->getRawRequest()->{$field}) && !strlen($this->_facade->getRawRequest()->{$field})) {
					continue;
				}

				if (is_array($this->_facade->getRawRequest()->{$field}) && !count($this->_facade->getRawRequest()->{$field})) {
					continue;
				}
			}

			foreach ($validations as $rule => $message) {
				$this->_addValidation($field, $rule, $message);
			}
		}
	}
}
