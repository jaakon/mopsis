<?php namespace App\{{MODULE}};

use App\{{MODULE}}\{{DOMAIN}}Filter as Filter;
use Mopsis\Components\Domain\AbstractService;
use Mopsis\Components\Domain\PayloadFactory;

class {{DOMAIN}}Service extends AbstractService
{
	public function __construct(Filter $filter, PayloadFactory $payload)
	{
		$this->filter  = $filter;
		$this->payload = $payload;
	}
}
