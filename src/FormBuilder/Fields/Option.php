<?php namespace Mopsis\FormBuilder\Fields;

class Option extends AbstractField
{
	public function getValue()
	{
		return $this->hasAttr('value') ? $this->attr('value') : $this->text();
	}

	public function setValue($value)
	{
		$this->attr('value', $value);
	}
}
