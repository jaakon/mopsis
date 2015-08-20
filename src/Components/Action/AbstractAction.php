<?php namespace Mopsis\Components\Action;

abstract class AbstractAction
{
	const ACCESS_PRIVATE = 'private';
	const ACCESS_PUBLIC  = 'public';

	protected $access = self::ACCESS_PRIVATE;
	protected $request;
	protected $responder;
	protected $service;

	public function init()
	{
		$this->checkAccess();
	}

	public function checkAccess()
	{
		if (
			!CORE_LOGIN_MANDATORY ||
			!CORE_LOGIN_PAGE ||
			$this->access === self::ACCESS_PUBLIC ||
			CORE_LOGIN_PAGE === $_SERVER['ROUTE'] ||
			\Mopsis\Auth::user()->exists
		) {
			return true;
		}
		if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
			redirect(CORE_LOGIN_PAGE);
		}

		redirect(CORE_LOGIN_PAGE.'?redirect='.urlencode($_SERVER['REQUEST_URI']));
	}
}
