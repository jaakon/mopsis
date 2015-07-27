<?php namespace Mopsis\MVC;

use Aura\Filter\SubjectFilter as Filter;
use FileUpload\FileUpload;
use FileUpload\FileUploadAggregator;
use Mopsis\FormBuilder\FormBuilder;
use Mopsis\FormBuilder\UploadValidator;

abstract class AbstractFilter
{
	protected $facade;
	protected $formbuilder;
	protected $uploader;
	protected $result;
	protected $messages;

//	public function __construct(Filter $facade, Formbuilder $formbuilder, FileUploadAggregator $uploader)
	public function __construct(Filter $facade, Formbuilder $formbuilder)
	{
		$this->facade      = $facade;
		$this->formbuilder = $formbuilder;
//		$this->uploader    = $uploader;
	}

	public function forInsert($formId, $data)
	{
		$this->loadValidatorRules($formId);
		$this->loadSanitizerRules($formId);

		return $this->isDataValid($data);
	}

	public function forUpdate($formId, $data)
	{
		$this->loadValidatorRules($formId);
		$this->loadSanitizerRules($formId);

		return $this->isDataValid($data);
	}

	public function forUpload($formId, $files, array $prefixes = [])
	{
		$this->loadUploaderRules($formId, $prefixes);

		return $this->isUploadValid($files);
	}

	public function getMessages()
	{
		return $this->messages;
	}

	public function getResult()
	{
		return $this->result;
	}

	protected function isDataValid($data)
	{
		$this->result = null;
		$this->messages = null;

		if ($this->facade->apply($data)) {
			$this->result = $data;
			unset($this->result[$_SESSION['csrf']['key']]);

			return true;
		}

		$this->messages = $this->facade->getFailures()->getMessages();

		return false;
	}

	protected function isUploadValid($files)
	{
		$this->result = null;
		$this->messages = null;

		$result = $this->uploader->process($files);

		if ($result->isValid()) {
			$this->result = $result->getArrayCopy();

			return true;
		}

		$this->messages = $result->getMessages();

		return false;
	}

	protected function loadSanitizerRules($formId)
	{
		foreach ($this->formbuilder->getSanitizerRules($formId) as $field => $rules) {
			foreach ($rules as $rule) {
				$filter = $this->facade->sanitize($field);

				$filter->toBlankOr($rule['spec'], $rule['args']);

				if ($rule['blank'] !== null) {
					$filter->useBlankValue($rule['blank']);
				}
			}
		}
	}

	protected function loadUploaderRules($formId, array $prefixes)
	{
		foreach ($this->formbuilder->getUploaderRules($formId) as $field => $rules) {
			$uploadHandler = \App::make(UploadHandler::class);

			if (isset($prefixes[$field]) || isset($prefixes['*'])) {
				$uploadHandler->setPrefix($prefixes[$field] ?: $prefixes['*']);
			}

			foreach ($rules as $rule) {
				$uploadHandler->addRule($rule['spec'], json_decode($rule['args'], true), $rule['message'], $rule['label']);
			}

			$this->uploader->addHandler($field, $uploadHandler);
		}
	}

	protected function loadValidatorRules($formId)
	{
		$this->facade->validate($_SESSION['csrf']['key'])->isNot('blank')->asStopRule();
		$this->facade->validate($_SESSION['csrf']['key'])->is('equalToValue', $_SESSION['csrf']['value'])->asStopRule();
		$this->facade->useFieldMessage($_SESSION['csrf']['key'], 'Ungültiges oder abgelaufenes Sicherheitstoken. Bitte Formular erneut versenden.');

		foreach ($this->formbuilder->getValidatorRules($formId) as $field => $rules) {
			if (!count($rules)) {
				$this->facade->validate($field)->is('optional');
				continue;
			}

			$isRequired = false;

			foreach ($rules as $rule) {
				$filter = $this->facade->validate($field);

				if ($rule['spec'] === 'required') {
					$filter->isNot('blank');
					$isRequired = true;
				} elseif ($isRequired) {
					$filter->is($rule['spec'], $rule['args']);
				} else {
					$filter->isBlankOr($rule['spec'], $rule['args']);
				}

				switch (strtolower($rule['mode'])) {
					case 'soft':
						$filter->asSoftRule($rule['message']);
						break;
					case 'hard':
						$filter->asHardRule($rule['message']);
						break;
					case 'stop':
						$filter->asStopRule($rule['message']);
						break;
					default:
						throw new \Exception('Invalid Failure Mode: "' . $rule['mode'] . '"');
				}
			}
		}
	}
}
