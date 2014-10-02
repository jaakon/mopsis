<?php namespace Mopsis\Renderer;

class RendererJson implements iRenderer
{
	use tRenderer;

	private $_status	= null;
	private $_message	= null;

//=== PUBLIC METHODS ===========================================================

	public function __construct()
	{
		$this->_contentType = 'application/json';
	}

	public function setResponse($status, $message)
	{
		$this->_status	= $status;
		$this->_message	= $message;

		return $this;
	}

	public function toString()
	{
		return json_encode(array_filter([
			'status'	=> $this->_status ?: 200,
			'message'	=> $this->_message ?: 'OK',
			'data'		=> $this->_data,
		]));
	}
}
