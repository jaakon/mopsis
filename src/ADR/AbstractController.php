<?php namespace Mopsis\ADR;

use Mopsis\Exceptions\TokenException;

abstract class AbstractController
{
	const ACCESS_PRIVATE = 'private';
	const ACCESS_PUBLIC  = 'public';

	protected $access = ACCESS_PRIVATE;
	protected $request;
	protected $responder;
	protected $service;

	public function __invoke($method, Array $args = [])
	{
		try {
			return $this->$method(...$args) ?: $this->responder->__invoke();
		} catch (TokenException $e) {
			return 'The session token is no longer valid.';
		}
	}

	public function init()
	{
		$this->checkAccess();
	}

	public function checkAccess()
	{
		if ($this->access === ACCESS_PUBLIC
			|| !CORE_LOGIN_MANDATORY
			|| !CORE_LOGIN_PAGE
			|| CORE_LOGIN_PAGE === $_SERVER['ROUTE']
			|| \Mopsis\Auth::user()->exists
		) {
			return true;
		}

		if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
			redirect(CORE_LOGIN_PAGE);
		}

		redirect(CORE_LOGIN_PAGE . '?redirect=' . urlencode($_SERVER['REQUEST_URI']));
	}
}
