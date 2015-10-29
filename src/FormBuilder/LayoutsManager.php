<?php namespace Mopsis\FormBuilder;

use Mopsis\Extensions\SimpleXML\SimpleXMLElement;
use Mopsis\Extensions\SimpleXML\XMLProcessingException;

class LayoutsManager
{
	protected $xml;
	protected $strict;
	protected $layout;
	protected $layoutId;

	public function __construct($xmlData, $strict = false)
	{
		$this->xml = (new SimpleXMLElement($xmlData))->first('layouts');

		if (!$this->xml) {
			throw new XMLProcessingException('layouts cannot be found in xmlData');
		}

		$this->strict = $strict;
	}

	public function __invoke($layoutId)
	{
		if ($layoutId !== $this->layoutId) {
			$this->layout   = $this->load($layoutId);
			$this->layoutId = $layoutId;
		}

		return $this;
	}

	public function getHtml($type)
	{
		$layout = $this->layout[$type];

		if (is_array($layout)) {
			return $layout['before'] . ($layout['element'] ?: '{' . $type . '.content}') . $layout['after'];
		}

		if ($this->strict) {
			throw new XMLProcessingException('layout for element "' . $type . '" cannot be found in xmlData');
		}

		return '<div class="' . $type . '">{' . $type . '.content}</div>';
	}

	public function getHtmlForItem($type, $part = null)
	{
		$layout = array_merge(
			$this->layout['items']['default'],
			$this->layout['items'][$type] ?: []
		);

		if ($part !== null) {
			return $layout[$part];
		}

		return $layout['before'] . $layout['element'] . $layout['after'];
	}

	protected function load($layoutId, array $anchestors = [])
	{
		$xml = $this->xml->first('layout[@id="' . $layoutId . '"]');

		if (!$xml) {
			throw new XMLProcessingException('layout "' . $layoutId . '" cannot be found in xmlData');
		}

		$layout  = [];
		$extends = $xml->attr('extends');

		if ($extends) {
			if (in_array($extends, $anchestors)) {
				throw new XMLProcessingException('loop detected while extending "' . $layoutId . '"');
			}

			$anchestors[] = $layoutId;
			$layout       = $this->load($extends, $anchestors);
		}

		foreach ($xml->children() as $node) {
			$tagName = $node->getName();

			if (!isset($layout[$tagName])) {
				$layout[$tagName] = [];
			}

			if ($tagName === 'items') {
				$layout[$tagName] = array_merge($layout[$tagName], $this->loadItems($node));
				continue;
			}

			$layout[$tagName] = array_merge($layout[$tagName], [
				'before' => $node->text('before'),
				'after'  => $node->text('after')
			]);
		}

		return $layout;
	}

	protected function loadItems(SimpleXMLElement $xml)
	{
		$layout = [];

		foreach ($xml->children() as $node) {
			$tagName          = $node->getName();
			$layout[$tagName] = [];

			foreach ($node->children() as $part) {
				$layout[$tagName][$part->getName()] = $part->text();
			}
		}

		return $layout;
	}
}
