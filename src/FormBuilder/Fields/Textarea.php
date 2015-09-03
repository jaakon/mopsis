<?php namespace Mopsis\FormBuilder\Fields;

class Textarea extends AbstractField
{
	public function getValue()
	{
		return $this->text();
	}

	public function setValue($value)
	{
		if (is_array($value)) {
			$value = implode(PHP_EOL, $value);
		}

		if ($this->attr('data-encoding') === 'base64') {
			$value = base64_encode($value);
		}

		$this->text($value);
	}

	public function updateSize()
	{
		if ($this->attr('rows') === 'auto') {
			$this->attr('rows', count(explode(PHP_EOL, $this->getValue())) + 1);
		}
	}
}
