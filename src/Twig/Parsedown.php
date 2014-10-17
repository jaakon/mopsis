<?php namespace Mopsis\Twig;

class Parsedown extends \Parsedown
{
	private static $instances = [];
	private $_attributes      = [];

	public static function instance($name = 'default')
	{
		if (isset(static::$instances[$name])) {
			return static::$instances[$name];
		}

		$instance = new static();

		static::$instances[$name] = $instance;

		return $instance;
	}

	public function setAttributes($blockType, array $attributes)
	{
		$this->_attributes[$blockType] = $attributes;
	}

	protected function identifyList($line, array $block = null)
	{
		return $this->_identifyBlock('List', $line, $block);
	}

	protected function identifyQuote($line, array $block = null)
	{
		return $this->_identifyBlock('Quote', $line, $block);
	}

	protected function identifyTable($line, array $block = null)
	{
		return $this->_identifyBlock('Table', $line, $block);
	}

	private function _identifyBlock($blockType, $line, array $block = null)
	{
		$block = parent::{'identify'.$blockType}($line, $block);

		if (count($block) && count($this->_attributes[$blockType])) {
			$block['element']['attributes'] = $this->_attributes[$blockType];
		}

		return $block;
	}
}
