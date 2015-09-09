<?php namespace Mopsis\Extensions;

use Illuminate\Support\Collection;

class TagBuilder
{
	const ATTRIBUTE_FORMAT            = ' %s="%s"';
	const ELEMENT_FORMAT_NORMAL       = '<%s%s>%s</%s>';
	const ELEMENT_FORMAT_START_TAG    = '<%s%s>';
	const ELEMENT_FORMAT_END_TAG      = '</%s>';
	const ELEMENT_FORMAT_SELF_CLOSING = '<%s%s />';

	protected $tagName;
	protected $attributes;
	protected $innerHtml;

	public function __construct($tagName, array $attributes = [], $innerHtml = null)
	{
		if (empty($tagName)) {
			throw new \InvalidArgumentException('Invalid Argument passed');
		}

		$this->tagName    = $tagName;
		$this->attributes = new Collection($attributes);
		$this->innerHtml  = (string)$innerHtml;
	}

	public function setInnerHtml($innerHtml)
	{
		$this->innerHtml = array_reduce(array_wrap($innerHtml), function ($html, $item) {
			return $html . (string)$item;
		});

		return $this;
	}

	public function mergeAttributes($attributes, $replaceExisting = true)
	{
		if ($attributes !== null) {
			foreach ($attributes as $key => $value) {
				$this->mergeAttribute($key, $value, $replaceExisting);
			}
		}

		return $this;
	}

	public function mergeAttribute($key, $value, $replaceExisting = true)
	{
		if ($replaceExisting || !$this->attributes->contains($key)) {
			$this->attributes->set($key, $value);
		}

		return $this;
	}

	public function __toString()
	{
		return $this->toString(TagRenderMode::NORMAL);
	}

	public function toString($renderMode = null)
	{
		switch ($renderMode) {
			case TagRenderMode::START_TAG:
				return sprintf(self::ELEMENT_FORMAT_START_TAG, $this->tagName, $this->getAttributesAsString());
			case TagRenderMode::END_TAG:
				return sprintf(self::ELEMENT_FORMAT_END_TAG, $this->tagName);
			case TagRenderMode::SELF_CLOSING:
				return sprintf(self::ELEMENT_FORMAT_SELF_CLOSING, $this->tagName, $this->getAttributesAsString());
			default:
				return sprintf(self::ELEMENT_FORMAT_NORMAL, $this->tagName, $this->getAttributesAsString(), $this->innerHtml, $this->tagName);
		}
	}

	private function getAttributesAsString()
	{
		return implode($this->getAttributesAsArray($this->attributes));
	}

	private function getAttributesAsArray($rawAttributes, $prefix = null)
	{
		$attributes = [];

		foreach ($rawAttributes as $key => $value) {
			if (is_array($value)) {
				array_push($attributes, ...$this->getAttributesAsArray($value, $key));
				continue;
			}

			if (empty($value)) {
				continue;
			}

			if ($value === true) {
				$value = $key;
			}

			$attributes[] = sprintf(self::ATTRIBUTE_FORMAT, $prefix . $key, htmlspecialchars($value, ENT_QUOTES));
		}

		return $attributes;
	}
}
