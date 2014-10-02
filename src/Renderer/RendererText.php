<?php namespace Mopsis\Renderer;

class RendererText implements iRenderer
{
    use tRenderer;

//=== PUBLIC METHODS ===========================================================

	public function __construct()
	{
		$this->_contentType = 'text/plain';
	}
}
