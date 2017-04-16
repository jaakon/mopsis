<?php
namespace Mopsis\Extensions\Eloquent;

use Mopsis\Core\Eloquent\Model as EloquentModel;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

abstract class SluggableModel extends EloquentModel
{
    use HasSlug;

    protected $hidden = ['slug'];

    protected $sluggable = [
        'source' => 'name',
        'target' => 'slug'
    ];

    public function getSlugOptions() : SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom($this->sluggable['source'])
            ->saveSlugsTo($this->sluggable['target']);
    }
}
