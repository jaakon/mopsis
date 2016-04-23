<?php namespace Mopsis\Extensions\Eloquent;

use Mopsis\Core\PrivilegedUser;

class Collection extends \Illuminate\Database\Eloquent\Collection
{
	protected $privilege;

	public function __get($key)
	{
		return $this->hasGetMutator($key) ? $this->mutateAttribute($key) : null;
	}

	public function __isset($key)
	{
		return $this->hasGetMutator($key);
	}

	public function accessibleFor(PrivilegedUser $user, $privilege = null)
	{
		return $this->filter(function ($item) use ($user, $privilege) {
			return $user->may($privilege ?: $this->privilege, $item);
		});
	}

	public function getLengthAttribute()
	{
		return $this->count();
	}

	public function sanitize(array $ids)
	{
		return array_intersect($ids, $this->lists('id')->toArray());
	}

	public function shrink(array $map)
	{
		return $this->filter(function ($item) use ($map) {
			foreach ($map as $key => $value) {
				if ($item->{$key} !== $value) {
					return false;
				}
			}

			return true;
		});
	}

	public function whereIn($property, array $values)
	{
		return $this->filter(function ($item) use ($property, $values) {
			return in_array($item->{$property}, $values);
		});
	}

	public function whereNot($key, $value, $strict = true)
	{
		return $this->filter(function ($item) use ($key, $value, $strict) {
			return $strict ? data_get($item, $key) !== $value
				: data_get($item, $key) != $value;
		});
	}

	protected function hasGetMutator($key)
	{
		return method_exists($this, 'get' . studly_case($key) . 'Attribute');
	}

	protected function mutateAttribute($key)
	{
		return $this->{'get' . studly_case($key) . 'Attribute'}();
	}
}
