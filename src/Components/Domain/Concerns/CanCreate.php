<?php
namespace Mopsis\Components\Domain\Concerns;

use Exception;
use Mopsis\Security\Token;

trait CanCreate
{
    protected $filter;

    protected $payload;

    protected $repository;

    public function create($formId, array $data = null)
    {
        try {
            $instance = $this->repository->newInstance();

            if ($data === null) {
                return $this->payload->accepted([
                    'instance' => $instance,
                    'formId'   => $formId
                ]);
            }

            if (!$this->filter->forInsert($formId, $data)) {
                return $this->payload->notValid([
                    'instance'    => $instance,
                    'formId'      => $formId,
                    'errors'      => $this->filter->getMessages(),
                    'requestData' => $data
                ]);
            }

            if (!$this->repository->create($instance, $this->filter->getResult())) {
                return $this->payload->notCreated([
                    'instance' => $instance,
                    'formId'   => $formId
                ]);
            }

            return $this->payload->created(['instance' => $instance]);
        } catch (Exception $e) {
            return $this->payload->error([
                'exception' => $e,
                'formId'    => $formId,
                'data'      => $data
            ]);
        }
    }

    public function createChild($ancestorToken, $formId, array $data = null)
    {
        try {
            $ancestor = Token::extract($ancestorToken);

            if (!$ancestor) {
                return $this->payload->gone(['token' => $ancestorToken]);
            }

            $instance  = $this->repository->newInstance();
            $relations = $instance->findRelations($ancestor);

            if (count($relations) !== 1) {
                throw new Exception('expected 1 relation, found ' . count($relations));
            }

            if ($data === null) {
                return $this->payload->accepted([
                    'instance'      => $instance,
                    'formId'        => $formId,
                    'ancestorToken' => $ancestorToken
                ]);
            }

            if (!$this->filter->forInsert($formId, $data)) {
                return $this->payload->notValid([
                    'instance'      => $instance,
                    'formId'        => $formId,
                    'ancestorToken' => $ancestorToken,
                    'errors'        => $this->filter->getMessages(),
                    'requestData'   => $data
                ]);
            }

            $relation = array_pop($relations);
            $instance->$relation()->associate($ancestor);

            if (!$this->repository->create($instance, $this->filter->getResult())) {
                return $this->payload->notCreated([
                    'instance'      => $instance,
                    'formId'        => $formId,
                    'ancestorToken' => $ancestorToken
                ]);
            }

            return $this->payload->created(['instance' => $instance]);
        } catch (Exception $e) {
            return $this->payload->error([
                'exception'     => $e,
                'formId'        => $formId,
                'ancestorToken' => $ancestorToken,
                'data'          => $data
            ]);
        }
    }
}
