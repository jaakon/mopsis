<?php namespace Mopsis\Renderer;

interface iRenderer
{
	public function setTemplate($template);
	public function useCache($boolean);

	public function assign($data);
	public function display();
	public function toString();
}

trait tRenderer
{
	protected $_contentType	= null;
	protected $_data		= [];
	protected $_template	= null;
	protected $_useCache	= false;

//=== PUBLIC METHODS ===========================================================

	public function setTemplate($template)
	{
		$this->_template = $template;
		return $this;
	}

	public function useCache($boolean)
	{
		$this->_useCache = $boolean;
		return $this;
	}

	public function push($key, $data)
	{
		if (!isset($this->_data[$key])) {
			$this->_data[$key] = [];
		}

		$this->_data[$key][] = $data;

		return $this;
	}

	public function assign($data)
	{
		$this->_data = array_merge($this->_data, object2array($data));
		return $this;
	}

	public function display()
	{
		if (!headers_sent()) {
			header('X-Frame-Options: SAMEORIGIN');
			header('Content-Type: '.$this->_contentType.'; charset=UTF-8');
		}

		die($this->toString());
	}

	public function toString()
	{
		return $this->_fillPlaceholder($this->_loadTemplate($this->_template));
	}
}
