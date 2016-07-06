<?php
namespace Mopsis\Components\Domain;

use Exception;
use Mopsis\Security\Token;

/**
 * @property AbstractFilter     $filter
 * @property AbstractRepository $repository
 * @property PayloadFactory     $payload
 */
abstract class AbstractService
{
    protected $collectionKey = 'collection';

    protected $filter;

    protected $instanceKey = 'instance';

    protected $payload;

    protected $repository;

    public function create($formId, array $data = null)
    {
        try {
            $instance = $this->repository->newEntity();

            if ($data === null) {
                return $this->payload->newEntity([
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
                return new $this->payload->notCreated([
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
                return $this->payload->notFound(['token' => $ancestorToken]);
            }

            $instance  = $this->repository->newEntity();
            $relations = $instance->findRelations($ancestor);

            if (count($relations) !== 1) {
                throw new Exception('expected 1 relation, found ' . count($relations));
            }

            if ($data === null) {
                return $this->payload->newEntity([
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
                return new $this->payload->notCreated([
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

    public function delete($token)
    {
        try {
            $instance = $this->repository->fetchByToken($token);

            if (!$instance) {
                return $this->payload->notFound(['token' => $token]);
            }

            if (!$this->repository->delete($instance)) {
                return $this->payload->notDeleted(['instance' => $instance]);
            }

            return $this->payload->deleted(['instance' => $instance]);
        } catch (Exception $e) {
            return $this->payload->error([
                'exception' => $e,
                'token'     => $token
            ]);
        }
    }

    public function fetch($token)
    {
        try {
            $instance = $this->repository->fetchByToken($token);

            if (!$instance) {
                return $this->payload->notFound(['token' => $token]);
            }

            return $this->payload->found([$this->instanceKey => $instance]);
        } catch (Exception $e) {
            return $this->payload->error([
                'exception' => $e,
                'token'     => $token
            ]);
        }
    }

    public function fetchAll()
    {
        try {
            $collection = $this->repository->fetchAll();

            if (!$collection) {
                return $this->payload->notFound();
            }

            return $this->payload->found([$this->collectionKey => $collection]);
        } catch (Exception $e) {
            return $this->payload->error([
                'exception' => $e
            ]);
        }
    }

    public function fetchByAttributes($attributes)
    {
        try {
            $instance = $this->repository->findOne($attributes);

            if (!$instance->exists) {
                return $this->payload->notFound($attributes);
            }

            return $this->payload->found([$this->instanceKey => $instance]);
        } catch (Exception $e) {
            return $this->payload->error([
                'exception'  => $e,
                'attributes' => $attributes
            ]);
        }
    }

    public function fetchById($id)
    {
        return $this->fetchByAttributes(['id' => $id]);
    }

    public function fetchBySlug($slug)
    {
        return $this->fetchByAttributes(['slug' => $slug]);
    }

    public function noop()
    {
        try {
            return $this->payload->found([]);
        } catch (Exception $e) {
            return $this->payload->error([
                'exception' => $e
            ]);
        }
    }

    public function setAttribute($token, $key, $value)
    {
        try {
            $instance = $this->repository->fetchByToken($token);

            if (!$instance) {
                return $this->payload->notFound(['token' => $token]);
            }

            if (!$this->repository->set($instance, $key, $value)) {
                return $this->payload->notUpdated(['instance' => $instance]);
            }

            return $this->payload->updated(['instance' => $instance]);
        } catch (Exception $e) {
            return $this->payload->error([
                'exception' => $e,
                'token'     => $token,
                'key'       => $key,
                'value'     => $value
            ]);
        }
    }

    public function update($token, $formId, array $data = null)
    {
        try {
            $instance = $this->repository->fetchByToken($token);

            if (!$instance) {
                return $this->payload->notFound(['token' => $token]);
            }

            if ($data === null) {
                return $this->payload->found([
                    'instance' => $instance,
                    'formId'   => $formId
                ]);
            }

            if (!$this->filter->forUpdate($formId, $data)) {
                return $this->payload->notValid([
                    'instance'    => $instance,
                    'formId'      => $formId,
                    'errors'      => $this->filter->getMessages(),
                    'requestData' => $data
                ]);
            }

            if (!$this->repository->update($instance, $this->filter->getResult())) {
                return $this->payload->notUpdated([
                    'instance' => $instance,
                    'formId'   => $formId
                ]);
            }

            return $this->payload->updated(['instance' => $instance]);
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
