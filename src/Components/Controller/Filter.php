<?php namespace Mopsis\Components\Controller;

use \Mopsis\Core\App;

class Filter extends \Mopsis\Components\Domain\AbstractFilter
{
	public function getResult($key = null)
	{
		return $key ? $this->result[$key] : $this->result;
	}

	public function isValid($formId, array $data = null)
	{
		$this->loadValidatorRules($formId);
		$this->loadSanitizerRules($formId);

		return $this->isDataValid($data ?: App::make('Aura\Web\Request')->post->get());
	}
}
