<?php
namespace Mopsis\Components\Domain\Payload;

interface PayloadInterface
{
    public function add(array $data);

    public function get($key = null);

    public function getName();

    public function getRedirect();

    public function getStatus();

    public function override(PayloadInterface $payload);

    public function setRedirect($value);

    public function setStatus($value);
}
