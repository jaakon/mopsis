<?php namespace Mopsis\FormBuilder\Fields;

use FluentDOM\Query;

class AbstractField extends Query
{
	public function val($value = null)
	{
		return func_num_args() ? $this->setValue($value) : $this->getValue();
	}

	public function getValue()
	{
		return $this->attr('value');
	}

	protected function escapeHtml($string)
	{
		return htmlspecialchars($string, ENT_COMPAT | ENT_HTML5, 'UTF-8', false);
	}

	protected function prop($name, $enabled = null)
	{
		if ($enabled === null) {
			return $this->hasAttr($name);
		}

		$this->removeAttr($name);

		if ($enabled) {
			$this->attr($name, true);
		}
	}
}
