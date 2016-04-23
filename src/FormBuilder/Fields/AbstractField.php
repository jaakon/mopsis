<?php namespace Mopsis\FormBuilder\Fields;

use FluentDOM\Query;

class AbstractField extends Query
{
	public function val($value = null)
	{
		if (!func_num_args()) {
			return $this->getValue();
		}

		return $this->setValue($value);
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
