<?php namespace Mopsis\FormBuilder\Fields;

class Radio extends AbstractField
{
	public function setValue($value)
	{
		$this->prop('checked', $this->attr('value') == $value);
	}
}
