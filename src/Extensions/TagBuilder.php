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

	public static function create($tagName)
	{
		return new static($tagName);
	}

	public function __construct($tagName)
	{
		if (empty($tagName)) {
			throw new \InvalidArgumentException('Invalid Argument passed');
		}

		$this->tagName    = $tagName;
		$this->attributes = new Collection();
	}

	public function __toString()
	{
		return $this->toString(TagRenderMode::NORMAL);
	}

	public function addClass($class)
	{
		if (empty(trim($class))) {
			return $this;
		}

		$classes = explode(' ', $class);

		if ($this->attributes->has('class')) {
			array_push($classes, ...explode(' ', $this->attributes->get('class')));
		}

		$this->attributes->put('class', implode(' ', array_unique($classes)));

		return $this;
	}

	public function attr($key, $value = null)
	{
		if (is_array($key)) {
			return $this->mergeAttributes($key);
		}

		return $this->mergeAttribute($key, $value);
	}

	public function html($content)
	{
		return $this->setInnerHtml(array_wrap($content));
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

	protected function getAttributesAsArray($rawAttributes, $prefix = null)
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

	protected function getAttributesAsString()
	{
		return implode($this->getAttributesAsArray($this->attributes));
	}

	protected function mergeAttribute($key, $value, $replaceExisting = true)
	{
		$key = snake_case($key, '-');

		if ($replaceExisting || !$this->attributes->has($key)) {
			$this->attributes->put($key, $value);
		}

		return $this;
	}

	protected function mergeAttributes($attributes, $replaceExisting = true)
	{
		if ($attributes !== null) {
			foreach ($attributes as $key => $value) {
				$this->mergeAttribute($key, $value, $replaceExisting);
			}
		}

		return $this;
	}

	protected function setInnerHtml(array $innerHtml)
	{
		$this->innerHtml = array_reduce($innerHtml, function ($html, $item) {
			return $html . (string)$item;
		});

		return $this;
	}
}
