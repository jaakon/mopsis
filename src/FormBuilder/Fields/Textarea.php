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
}