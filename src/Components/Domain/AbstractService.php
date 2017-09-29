<?php
namespace Mopsis\Components\Domain;

use Exception;
use Mopsis\Security\Token;

abstract class AbstractService
{
    protected $filter;

    protected $payload;

    protected $repository;

    public function all()
    {
        try {
            $collection = $this->repository->all();

            if (!$collection) {
                return $this->payload->notFound();
            }

            return $this->payload->found(['__COLLECTION__' => $collection]);
        } catch (Exception $e) {
            return $this->payload->error([
                'exception' => $e
            ]);
        }
    }

    public function create($formId, array $data = null)
    {
        try {
            $instance = $this->repository->newInstance();

            if ($data === null) {
                return $this->payload->accepted([
                    '__INSTANCE__' => $instance,
                    'formId'       => $formId
                ]);
            }

            if (!$this->filter->forInsert($formId, $data)) {
                return $this->payload->notValid([
                    '__INSTANCE__' => $instance,
                    'formId'       => $formId,
                    'errors'       => $this->filter->getMessages(),
                    'requestData'  => $data
                ]);
            }

            if (!$this->repository->create($instance, $this->filter->getResult())) {
                return $this->payload->notCreated([
                    '__INSTANCE__' => $instance,
                    'formId'       => $formId
                ]);
            }

            return $this->payload->created(['__INSTANCE__' => $instance]);
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
                    '__INSTANCE__'  => $instance,
                    'formId'        => $formId,
                    'ancestorToken' => $ancestorToken
                ]);
            }

            if (!$this->filter->forInsert($formId, $data)) {
                return $this->payload->notValid([
                    '__INSTANCE__'  => $instance,
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
                    '__INSTANCE__'  => $instance,
                    'formId'        => $formId,
                    'ancestorToken' => $ancestorToken
                ]);
            }

            return $this->payload->created(['__INSTANCE__' => $instance]);
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
            $instance = $this->repository->findByToken($token);

            if (!$instance) {
                return $this->payload->gone(['token' => $token]);
            }

            if (!$this->repository->delete($instance)) {
                return $this->payload->notDeleted(['__INSTANCE__' => $instance]);
            }

            return $this->payload->deleted(['__INSTANCE__' => $instance]);
        } catch (Exception $e) {
            return $this->payload->error([
                'exception' => $e,
                'token'     => $token
            ]);
        }
    }

    public function find($token)
    {
        try {
            $instance = $this->repository->findByToken($token);

            if (!$instance) {
                return $this->payload->gone(['token' => $token]);
            }

            return $this->payload->found(['__INSTANCE__' => $instance]);
        } catch (Exception $e) {
            return $this->payload->error([
                'exception' => $e,
                'token'     => $token
            ]);
        }
    }

    public function findByAttributes($attributes)
    {
        try {
            $instance = $this->repository->first($attributes);

            if (!$instance->exists) {
                return $this->payload->notFound($attributes);
            }

            return $this->payload->found(['__INSTANCE__' => $instance]);
        } catch (Exception $e) {
            return $this->payload->error([
                'exception'  => $e,
                'attributes' => $attributes
            ]);
        }
    }

    public function findById($id)
    {
        return $this->findByAttributes(['id' => $id]);
    }

    public function findBySlug($slug)
    {
        return $this->findByAttributes(['slug' => $slug]);
    }

    public function noop()
    {
        return $this->payload->found();
    }

    public function setAttribute($token, $key, $value)
    {
        try {
            $instance = $this->repository->findByToken($token);

            if (!$instance) {
                return $this->payload->gone(['token' => $token]);
            }

            if (!$this->repository->set($instance, $key, $value)) {
                return $this->payload->notUpdated(['__INSTANCE__' => $instance]);
            }

            return $this->payload->updated(['__INSTANCE__' => $instance]);
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
                    '__INSTANCE__' => $instance,
                    'data'         => $data
                ]);
            }

            return $this->payload->updated(['__INSTANCE__' => $instance]);
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
                    '__INSTANCE__' => $instance,
                    'formId'       => $formId
                ]);
            }

            if (!$this->filter->forUpdate($formId, $data)) {
                return $this->payload->notValid([
                    '__INSTANCE__' => $instance,
                    'formId'       => $formId,
                    'errors'       => $this->filter->getMessages(),
                    'requestData'  => $data
                ]);
            }

            if (!$this->repository->update($instance, $this->filter->getResult())) {
                return $this->payload->notUpdated([
                    '__INSTANCE__' => $instance,
                    'formId'       => $formId
                ]);
            }

            return $this->payload->updated(['__INSTANCE__' => $instance]);
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
