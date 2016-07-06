<?php
namespace Mopsis\Components\Domain;

use Mopsis\Core\App;
use Mopsis\Security\Csrf;

abstract class AbstractFilter
{
    const EMPTY_MESSAGE = '_NULL_';

    protected $facade;

    protected $messages;

    protected $result;

    protected $rules;

    protected $sanitizerRulesLoaded = false;

    protected $uploader;

    protected $uploaderRulesLoaded = false;

    protected $validatorRulesLoaded = false;

    //    public function __construct(Filter $facade, RulesProvider $rules, FileUploadAggregator $uploader)

    public function addRule($field, array $rule, $isRequired = true)
    {
        $filter       = $this->facade->validate($field);
        $rule['args'] = arrayWrap($rule['args']);

        if ($rule['spec'] === 'required') {
            $filter->isNotBlank();
        } elseif ($isRequired) {
            $filter->is($rule['spec'], ...$rule['args']);
        } else {
            $filter->isBlankOr($rule['spec'], ...$rule['args']);
        }

        if ($rule['message'] === false) {
            $rule['message'] = static::EMPTY_MESSAGE;
        }

        switch (strtolower($rule['mode']) ?: 'hard') {
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
        $this->result   = [];
        $this->messages = [];

        if ($this->facade->apply($data)) {
            foreach ($data as $key => $value) {
                array_set($this->result, $key, $value);
            }

            foreach (array_keys($this->rules->forValidator()) as $key) {
// checkboxes are missing otherwise
                if (!array_has($this->result, $key)) {
                    array_set($this->result, $key, false);
                }
            }

            Csrf::removeToken($this->result['csrfToken']);
            unset($this->result['csrfToken']);

            return true;
        }

        if (isset($data['csrfToken'])) {
            Csrf::removeToken($data['csrfToken']);
        }

        $this->messages = $this->removeEmptyMessages($this->facade->getFailures()->getMessages());

        return false;
    }

    protected function isUploadValid($files)
    {
        $this->result   = [];
        $this->messages = [];

        $result = $this->uploader->process($files);

        if ($result->isValid()) {
            $this->result = $result->getArrayCopy();

            return true;
        }

        $this->messages = $this->removeEmptyMessages($result->getMessages());

        return false;
    }

    protected function loadSanitizerRules($formId)
    {
        if ($this->sanitizerRulesLoaded) {
            return;
        }

        $this->sanitizerRulesLoaded = true;
        $this->rules->load($formId);

        foreach ($this->rules->forSanitizer() as $field => $rules) {
            foreach ($rules as $rule) {
                $filter = $this->facade->sanitize($field);

                $filter->toBlankOr($rule['spec'], ...$rule['args']);

                if ($rule['blank'] !== null) {
                    $filter->useBlankValue($rule['blank']);
                }
            }
        }
    }

    protected function loadUploaderRules($formId, array $prefixes)
    {
        if ($this->uploaderRulesLoaded) {
            return;
        }

        $this->uploaderRulesLoaded = true;
        $this->rules->load($formId);

        foreach ($this->rules->forUploader() as $field => $rules) {
            $uploadHandler = App::get('UploadHandler');

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
        if ($this->validatorRulesLoaded) {
            return;
        }

        $this->validatorRulesLoaded = true;
        $this->rules->load($formId);

        $this->facade->validate('csrfToken')->is('csrfToken')->asStopRule('Ungültiges oder abgelaufenes Sicherheitstoken. Bitte Formular erneut versenden.');

        foreach ($this->rules->forValidator() as $field => $rules) {
            if (!count($rules)) {
                $this->facade->validate($field)->isBlankOr('optional');
                continue;
            }

            $isRequired = array_reduce($rules, function ($isRequired, $rule) {
                return $isRequired || $rule['spec'] === 'required';
            });

            foreach ($rules as $rule) {
                $this->addRule($field, $rule, $isRequired);
            }
        }
    }

    protected function removeEmptyMessages($data)
    {
        foreach ($data as $field => $messages) {
            $data[$field] = array_filter($messages, function ($message) {
                return $message !== static::EMPTY_MESSAGE;
            });
        }

        return $data;
    }
}
