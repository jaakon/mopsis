<?php
namespace Mopsis\Components\Domain\Concerns;

use Exception;

trait CanDelete
{
    protected $filter;

    protected $payload;

    protected $repository;

    public function delete($token)
    {
        try {
            $instance = $this->repository->findByToken($token);

            if (!$instance) {
                return $this->payload->gone(['token' => $token]);
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
}
