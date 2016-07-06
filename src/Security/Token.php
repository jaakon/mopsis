<?php
namespace Mopsis\Security;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mopsis\Contracts\Model;
use Mopsis\Core\App;

/**
 * @property Model  $instance
 * @property string $session
 */
class Token
{
    protected $instance;

    protected $session;

    public function __construct(Model $instance, $session = null)
    {
        $this->instance = $instance;
        $this->session  = $session;
    }

    public function __toString()
    {
        return $this->generate();
    }

    public static function extract($string)
    {
        $string = (string) $string;

        if (!preg_match('/^(\w+?)(?:Model)?:(\d+):[a-f0-9]+$/i', $string, $m)) {
            return false;
        }

        try {
            $class = App::build('Domain', $m[1] . '\\Model');
        } catch (\DomainException $e) {
            $class = App::build('Model', $m[1]);
        }

        try {
            $instance = $class::findOrFail($m[2]);
        } catch (ModelNotFoundException $e) {
            return false;
        }

        if ($string !== $instance->token->generate() && $string !== $instance->hash->generate()) {
            return false;
        }

        return $instance;
    }

    public function generate()
    {
        return $this->instance . ':' . sha1(get_class($this->instance) . $this->instance->id . config('app.key') . $this->session);
    }
}
