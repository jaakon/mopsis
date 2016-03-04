<?php
namespace Mopsis\Extensions\Aura\Filter\Rule\Validate\Finance;

use IBAN\Validation\IBANValidator as Validator;

class Iban
{
    protected $ibanValidator;

    public function __construct(Validator $ibanValidator)
    {
        $this->ibanValidator = $ibanValidator;
    }

    public function __invoke($subject, $field)
    {
        $value = $subject->$field;

        if (!is_scalar($value)) {
            return false;
        }

        return (bool) $this->ibanValidator->validate(str_replace(' ', '', $value));
    }
}
