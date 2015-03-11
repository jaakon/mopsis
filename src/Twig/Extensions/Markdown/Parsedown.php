<?php
namespace Mopsis\Twig\Extensions\Markdown;

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

	public function __construct()
	{
		$this->BlockTypes['{'][] = 'Placeholder';
	}

	public function setAttributes($blockType, array $attributes)
	{
		$this->_attributes[$blockType] = $attributes;
	}

	protected function blockPlaceholder($line)
	{
		if (preg_match('/^\{(.+?)\}:[ ]*(.+?)[ ]*$/', $line['text'], $matches)) {
			$this->DefinitionData['Placeholder'][$matches[1]] = $matches[2];
			return ['hidden' => true];
		}
	}

	protected function unmarkedText($text)
	{
		$text = parent::unmarkedText($text);

		if (isset($this->DefinitionData['Placeholder'])) {
			foreach ($this->DefinitionData['Placeholder'] as $key => $value) {
				$pattern = '/\{'.preg_quote($key, '/').'\}/i';
				$text = preg_replace($pattern, $value, $text);
			}
		}

		return $text;
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
		$block = parent::{'identify' . $blockType}($line, $block);

		if (count($block) && count($this->_attributes[$blockType])) {
			$block['element']['attributes'] = $this->_attributes[$blockType];
		}

		return $block;
	}
}
