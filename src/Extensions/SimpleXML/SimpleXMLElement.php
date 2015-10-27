<?php namespace Mopsis\Extensions\SimpleXML;

class SimpleXMLElement
{
	use XMLInternalErrorsHelper;

	protected $element;

	public function __call($name, $arguments)
	{
		return $this->element->{$name}(...$arguments);
	}

	public function __construct($xmlData)
	{
		if (gettype($xmlData) === 'string') {
			if (is_file($xmlData)) {
				$xmlData = file_get_contents($xmlData);
			}

			try {
				$this->useXMLInternalErrors();
				$this->element = new \SimpleXMLElement($xmlData);
				$this->resetXMLInternalErrorsSetting();
			} catch (\Exception $exception) {
				$this->resetXMLInternalErrorsSetting();
				throw new XMLProcessingException($this->getLastXMLErrorMessage());
			}

			return $this->element;
		}

		if (gettype($xmlData) === 'object') {
			if ($xmlData instanceof \SimpleXMLElement) {
				$this->element = $xmlData;
			} elseif ($xmlData instanceof SimpleXMLElement) {
				$this->element = $xmlData->getWrappedElement();
			}

			return $this->element;
		}

		throw new XMLProcessingException('Unknown xmlData given to Mopsis\SimpleXMLElement. Expected a XML String or a SimpleXMLElement object.');
	}

	public function __toString()
	{
		return $this->element->__toString();
	}

	public function attr($name, $namespace = null)
	{
		$isPrefix       = ($namespace !== null);
		$attributes     = $this->element->attributes($namespace, $isPrefix);
		$attributeValue = $attributes->{$name};

		return ($attributeValue !== null) ? (string) $attributeValue : null;
	}

	public function attributes($namespace = null)
	{
		$isPrefix   = ($namespace !== null);
		$attributes = [];

		foreach ($this->element->attributes($namespace, $isPrefix) as $key => $value) {
			$attributes[(string) $key] = (string) $value;
		}

		return $attributes;
	}

	public function children()
	{
		$children = [];

		foreach ($this->element->children() as $child) {
			$children[] = $this->wrapSimpleXMLElement($child);
		}

		return $children;
	}

	public function first($path)
	{
		$elements = $this->xpath($path);

		return $elements && count($elements) ? $elements[0] : false;
	}

	public function getFirstChildByTagName($tagName)
	{
		if (!isset($this->element->{$tagName})) {
			return null;
		}

		return $this->wrapSimpleXMLElement($this->element->{$tagName});
	}

	public function getWrappedElement()
	{
		return $this->element;
	}

	public function has($path)
	{
		return !!$this->first($path);
	}

	public function registerXPathNamespace($prefix, $namespace)
	{
		return $this->element->registerXPathNamespace($prefix, $namespace);
	}

	public function removeNodesMatchingXPath($path)
	{
		$nodesToRemove = $this->element->xpath($path);

		foreach ($nodesToRemove as $nodeToRemove) {
			unset($nodeToRemove[0]);
		}
	}

	public function text($path = '.')
	{
		if (!($element = $this->first($path))) {
			return false;
		}

		return trim((string) $element) ?: null;
	}

	public function xpath($path)
	{
		if (!($elements = $this->element->xpath($path))) {
			return false;
		}

		return array_map([$this, 'wrapSimpleXMLElement'], $elements);
	}

	protected function wrapSimpleXMLElement(\SimpleXMLElement $element)
	{
		$elementAsXML = $element->asXML();

		if ($elementAsXML === false) {
			return null;
		}

		return new self($elementAsXML);
	}
}
