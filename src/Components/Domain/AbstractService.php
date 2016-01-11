<?php namespace Mopsis\Components\Domain;

use Exception;
use Mopsis\Security\Token;

/**
 * @property AbstractFilter  $filter
 * @property AbstractGateway $gateway
 * @property PayloadFactory  $payload
 */
abstract class AbstractService
{
	protected $filter;
	protected $gateway;
	protected $payload;
	protected $instanceKey   = 'instance';
	protected $collectionKey = 'collection';

	public function create($formId, array $data = null)
	{
		try {
			$instance = $this->gateway->newEntity();

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

			if (!$this->gateway->create($instance, $this->filter->getResult())) {
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

			$instance  = $this->gateway->newEntity();
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

			if (!$this->gateway->create($instance, $this->filter->getResult())) {
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
			$instance = $this->gateway->fetchByToken($token);

			if (!$instance) {
				return $this->payload->notFound(['token' => $token]);
			}

			if (!$this->gateway->delete($instance)) {
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
			$instance = $this->gateway->fetchByToken($token);

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
			$collection = $this->gateway->fetchAll();

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

	public function fetchBySlug($slug)
	{
		return $this->fetchByAttributes(['slug' => $slug]);
	}

	public function fetchByAttributes($attributes)
	{
		try {
			$instance = $this->gateway->findOne($attributes);

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
			$instance = $this->gateway->fetchByToken($token);

			if (!$instance) {
				return $this->payload->notFound(['token' => $token]);
			}

			if (!$this->gateway->set($instance, $key, $value)) {
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
			$instance = $this->gateway->fetchByToken($token);

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

			if (!$this->gateway->update($instance, $this->filter->getResult())) {
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
