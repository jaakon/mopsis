<?php
namespace Mopsis\Contracts;

/**
 * @property int                    $id
 * @property bool                   $exists
 * @property \Mopsis\Security\Token $hash
 * @property \Mopsis\Security\Token $token
 * @method ancestor()
 * @method update(array $attributes = [], array $options = [])
 * @method delete()
 * @method setAttribute($key, $value)
 * @method save()
 */
interface Model
{
    public function __toString();
}
