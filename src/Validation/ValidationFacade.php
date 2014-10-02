<?php namespace Mopsis\Validation;

class ValidationFacade
{
	private $_coordinator	= null;
	private $_validators	= [];
	private $_hasValidated	= false;
	private $_request		= null;

	public function __construct(Request\BasicRequest $request)
	{
		$this->_request = $request;
	}

	public function addValidator(BasicValidator $validator, $group = null)
	{
		if ($group === null) {
			return $this->_validators[] = $validator;
		}

		if (!isset($this->_validators[$group])) {
			$this->_validators[$group] = [];
		}

		return $this->_validators[$group][] = $validator;
	}

	public function validate($request)
	{
		if ($this->_coordinator === null) {
			$this->_coordinator = $this->createCoordinator($request, new Request\CleanRequest);
		}

		if (!count($this->_validators)) {
			return true;
		}

		foreach ($this->_validators as $validator) {
			if (is_array($validator)) {
				foreach ($validator as $subvalidator) {
					if (!$subvalidator->validate($this->_coordinator)) {
						break;
					}
				}
			} else {
				$validator->validate($this->_coordinator);
			}
		}

		$this->_validators = [];
	}

	public function createCoordinator($raw, $clean)
	{
		return new ValidationCoordinator($raw ?: new Request\RawRequest, $clean);
	}

	public function isValid()
	{
		$this->validate($this->_request);
		return !count($this->_coordinator->getErrors());
	}

	public function getCleanRequest()
	{
		return $this->isValid() ? $this->_coordinator->getCleanRequest() : false;
	}

	public function getRawRequest()
	{
		return $this->_request;
	}

	public function getErrors()
	{
		return $this->_coordinator->getErrors();
	}

	public function getInvalidFields()
	{
		return $this->_coordinator->getInvalidFields();
	}

	public function addRule($fieldname, $rule = null)
	{
		return $this->addValidator($this->_createValidatorByRule($fieldname, $rule, array_slice(func_get_args(), 2)), $fieldname);
	}

	private function _createValidatorByRule($fieldname, $rule, $args)
	{
		switch ($rule) {
			case 'required':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\RequiredValue()),
					'"'.$fieldname.'" is required.'
				);
				break;
			case 'matches':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\RegExp($args[0], false)),
					'the value for "'.$fieldname.'" is invalid.'
				);
				break;
			case 'isEmail':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\RegExp('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i', false)),
					'"'.$fieldname.'" must contain a valid e-mail address.'
				);
				break;
			case 'isUrl':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\RegExp('/^(http|ftp|https):\/\/[\w\-_]+(\.[\w\-_]+)+([\w\-\.,@?^=%&amp;:\/~\+#]*[\w\-\@?^=%&amp;\/~\+#])?$/i', false)),
					'"'.$fieldname.'" must contain a valid url.'
				);
				break;
			case 'isDate':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\RegExp('/^(0?[1-9]|[1-2]\d|3[0-1])\.(0?[1-9]|1[0-2])\.(19|20)?\d{2}$/', false)),
					'"'.$fieldname.'" must contain a valid date.'
				);
				break;
			case 'isFullDate':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\RegExp('/^(0?[1-9]|[1-2]\d|3[0-1])\.(0?[1-9]|1[0-2])\.(1[1-9]|20)\d{2}$/', false)),
					'"'.$fieldname.'" must contain a valid date.'
				);
				break;
			case 'isInteger':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\RegExp('/^-?[0-9]+$/', false)),
					'"'.$fieldname.'" must contain a valid integer.'
				);
				break;
			case 'isFloat':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\RegExp('/^-?0?([1-9][0-9]*)?(,[0-9]+)?$/', false)),
					'"'.$fieldname.'" must contain a valid float.'
				);
				break;
			case 'isMoney':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\RegExp('/^-?[0-9]{1,3}([0-9]*|(\.[0-9]{3})*)(,[0-9]{2})?$/', false)),
					'"'.$fieldname.'" must contain a valid float.'
				);
				break;
			case 'isTicketId':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\RegExp('/^\d{4,5}|\w{3,}\-\d{1,3}$/', false)),
					'"'.$fieldname.'" must contain a valid ticket id.'
				);
				break;
			case 'isTime':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\RegExp('/^(0?[0-9]|1[0-9]|2[0-3]):([0-5][0-9])$/', false)),
					'"'.$fieldname.'" must contain a valid time.'
				);
				break;
			case 'isZipCode':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\RegExp('/^\d{5}$/', false)),
					'"'.$fieldname.'" must contain a valid zip code.'
				);
				break;
			case 'hasLength':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\ValueLength($args[0], $args[0])),
					'"'.$fieldname.'" must have '.$args[0].' characters.'
				);
				break;
			case 'minLength':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\ValueLength($args[0], false)),
					'"'.$fieldname.'" must have at least '.$args[0].' characters.'
				);
				break;
			case 'maxLength':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\ValueLength(false, $args[0])),
					'"'.$fieldname.'" must not have more than '.$args[0].' characters.'
				);
				break;
			case 'hasValue':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\EqualsValue($args[0])),
					'"'.$fieldname.'" must be equal to "'.$args[0].'".'
				);
				break;
			case 'minValue':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\ValueInRange($args[0], false)),
					'"'.$fieldname.'" must not be less than "'.$args[0].'".'
				);
				break;
			case 'maxValue':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\ValueInRange(false, $args[0])),
					'"'.$fieldname.'" must not be more than "'.$args[0].'".'
				);
				break;
			case 'isTokenOf':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\LoadCoreObject($args[0])),
					'"'.$fieldname.'" must be a valid token of '.$args[0].'.'
				);
				break;
			case 'isUnique':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\CountCoreObjects($args[0], $fieldname)),
					'"'.$fieldname.'" must be unique.'
				);
				break;
			case 'isValidPushBulletApiKey':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\PushBulletApiKey()),
					'"'.$fieldname.'" must be a valid PushBullet API Key.'
				);
				break;
			case 'isValidTwitterUser':
				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\TwitterUser($args[0])),
					'"'.$fieldname.'" must be a valid twitter username.'
				);
				break;
			case 'requires':
				return new BasicValidator(
					new Specification\DependentFields($fieldname, $args[0]),
					'"'.$fieldname.'" requires "'.$args[0].'".'
				);
				break;
			case 'equals':
				return new BasicValidator(
					new Specification\EqualFields($fieldname, $args[0]),
					'"'.$fieldname.'" must match "'.$args[0].'".'
				);
				break;
			case 'minElements':
				return new BasicValidator(
					new Specification\ArrayField($fieldname, new Specification\ArrayCount($args[0], false)),
					'"'.$fieldname.'" must have at least '.$args[0].' elements.'
				);
				break;
			case 'maxElements':
				return new BasicValidator(
					new Specification\ArrayField($fieldname, new Specification\ArrayCount(false, $args[0])),
					'"'.$fieldname.'" must not have more then '.$args[0].' elements.'
				);
				break;
			case 'error':
				return new BasicValidator(
					new Specification\Dummy($fieldname, !$args[0]),
					'the value for "'.$fieldname.'" is invalid.'
				);
				break;
			case null:
				return new BasicValidator(
					new Specification\Dummy($fieldname),
					null
				);
				break;
			default:
				if (!preg_match('/^\/.+\/\w*$/', $rule)) {
					throw new \Exception('invalid validation rule: "'.$rule.'"');
				}

				return new BasicValidator(
					new Specification\SingleField($fieldname, new Specification\RegExp($rule)),
					'the value for "'.$fieldname.'" is invalid.'
				);
				break;
		}

		return null;
	}
}
