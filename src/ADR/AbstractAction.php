<?php namespace Mopsis\ADR;

abstract class AbstractAction
{
	const ACCESS_PRIVATE = 'private';
	const ACCESS_PUBLIC  = 'public';

	protected $access = ACCESS_PRIVATE;
	protected $service;
	protected $request;
	protected $responder;

	public function init()
	{
		if (
			$this->access === ACCESS_PUBLIC ||
			!CORE_LOGIN_MANDATORY ||
			!CORE_LOGIN_PAGE ||
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
