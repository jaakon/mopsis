<?php namespace Mopsis\Eloquent;

class Collection extends \Illuminate\Database\Eloquent\Collection
{
	public function length()
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

	public function whereUserHasPrivilege(\Mopsis\Core\User $user, $privilege)
	{
		return $this->filter(function ($item) use ($user, $privilege) {
			return $user->isAllowedTo($privilege, $item);
		});
	}
}
