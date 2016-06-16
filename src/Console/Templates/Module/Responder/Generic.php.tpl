<?php namespace App\{{MODULE}}\Responder;

use App\AbstractBaseResponder;

class {{DOMAIN}}{{ACTION}}Responder extends AbstractBaseResponder
{
	protected $template       = '{{TEMPLATE}}';
	protected $payloadMethods = [
		'Payload\Found'    => 'found',
		'Payload\NotFound' => 'notFound'
	];

	public function found()
	{
		if ($this->negotiateMediaType()) {
			$this->renderView();
		}
	}
}
