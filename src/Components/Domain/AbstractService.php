<?php
namespace Mopsis\Components\Domain;

use Exception;
use Mopsis\Components\Domain\Concerns\CanCreate;
use Mopsis\Components\Domain\Concerns\CanDelete;
use Mopsis\Components\Domain\Concerns\CanUpdate;

abstract class AbstractService
{
    use CanCreate, CanUpdate, CanDelete;

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

            return $this->payload->found(['collection' => $collection]);
        } catch (Exception $e) {
            return $this->payload->error([
                'exception' => $e
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

            return $this->payload->found(['instance' => $instance]);
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

            return $this->payload->found(['instance' => $instance]);
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
}
