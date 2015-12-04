<?php namespace Mopsis\FormBuilder\Fields;

use Exception;
use Mopsis\FormBuilder\Contracts\Resizable;

class Textarea extends AbstractField implements Resizable
{
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
		if (!$this->hasAttr('rows') || ctype_digit($this->attr('rows'))) {
			return;
		}

		$rows = count(explode(PHP_EOL, $this->getValue()));

		if ($this->attr('rows') === 'auto') {
			$this->attr('rows', $rows);

			return;
		}

		if (preg_match('/\{(\d*),(\d*)\}/', $this->attr('rows'), $m)) {
			$this->attr('rows', between($rows, $m[1], $m[2]));

			return;
		}

		throw new Exception('invalid value "' . $this->attr('rows') . '" for attribute "rows"');
	}

	public function getValue()
	{
		return $this->text();
	}
}
