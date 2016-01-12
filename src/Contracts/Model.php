<?php namespace Mopsis\Contracts;

/**
 * @property int                    $id
 * @property bool                   $exists
 * @property \Mopsis\Security\Token $hash
 * @property \Mopsis\Security\Token $token
 */
interface Model
{
	public function ancestor();

	public function update($data);
	public function delete();
	public function setAttribute($key, $value);
	public function save();

	public function __toString();
}
