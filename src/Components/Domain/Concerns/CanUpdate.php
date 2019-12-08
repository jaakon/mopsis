<?php
namespace Mopsis\Components\Domain\Concerns;

use Exception;

trait CanUpdate
{
    protected $filter;

    protected $payload;

    protected $repository;

    public function setAttribute($token, $key, $value)
    {
        try {
            $instance = $this->repository->findByToken($token);

            if (!$instance) {
                return $this->payload->gone(['token' => $token]);
            }

            if (!$this->repository->set($instance, $key, $value)) {
                return $this->payload->notUpdated(['#instance' => $instance]);
            }

            return $this->payload->updated(['#instance' => $instance]);
        } catch (Exception $e) {
            return $this->payload->error([
                'exception' => $e,
                'token'     => $token,
                'key'       => $key,
                'value'     => $value
            ]);
        }
    }

    public function setAttributes($token, $data)
    {
        try {
            $instance = $this->repository->findByToken($token);

            if (!$instance) {
                return $this->payload->gone(['token' => $token]);
            }

            if (!$this->repository->update($instance, $data)) {
                return $this->payload->notUpdated([
                    '#instance' => $instance,
                    'data'      => $data
                ]);
            }

            return $this->payload->updated(['#instance' => $instance]);
        } catch (Exception $e) {
            return $this->payload->error([
                'exception' => $e,
                'token'     => $token,
                'data'      => $data
            ]);
        }
    }

    public function update($token, $formId, array $data = null)
    {
        try {
            $instance = $this->repository->findByToken($token);

            if (!$instance) {
                return $this->payload->gone(['token' => $token]);
            }

            if ($data === null) {
                return $this->payload->accepted([
                    '#instance' => $instance,
                    'formId'    => $formId
                ]);
            }

            if (!$this->filter->forUpdate($formId, $data)) {
                return $this->payload->notValid([
                    '#instance'   => $instance,
                    'formId'      => $formId,
                    'errors'      => $this->filter->getMessages(),
                    'requestData' => $data
                ]);
            }

            if (!$this->repository->update($instance, $this->filter->getResult())) {
                return $this->payload->notUpdated([
                    '#instance' => $instance,
                    'formId'    => $formId
                ]);
            }

            return $this->payload->updated(['#instance' => $instance]);
        } catch (Exception $e) {
            return $this->payload->error([
                'exception' => $e,
                'formId'    => $formId,
                'token'     => $token,
                'data'      => $data
            ]);
        }
    }
}
