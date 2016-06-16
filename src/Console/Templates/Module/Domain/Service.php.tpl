<?php namespace App\{{MODULE}}\Domain;

use App\{{MODULE}}\Domain\{{DOMAIN}}Filter as Filter;
use App\{{MODULE}}\Domain\{{DOMAIN}}Gateway as Gateway;
use Mopsis\Components\Domain\AbstractService;
use Mopsis\Components\Domain\PayloadFactory;

class {{DOMAIN}}Service extends AbstractService
{
	protected $instanceKey   = '{{INSTANCE}}';
	protected $collectionKey = '{{INSTANCE}}s';

	public function __construct(Filter $filter, Gateway $gateway, PayloadFactory $payload)
	{
		$this->filter  = $filter;
		$this->gateway = $gateway;
		$this->payload = $payload;
	}
}
