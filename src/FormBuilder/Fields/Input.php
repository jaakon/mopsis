<?php namespace Mopsis\FormBuilder\Fields;

class Input extends AbstractField
{
	public function setValue($value)
	{
		$this->attr('value', $value);
	}
}
