<?php
namespace Mopsis\Core;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mopsis\Contracts\PrivilegedUser;
use Mopsis\Contracts\User;

class Auth
{
    protected static $user;

    public static function attempt(array $credentials = [], $remember = false, $login = true)
    {
        $userClass = App::get('User');
        $password  = array_pull($credentials, 'password');

        if (!($user = $userClass::find($credentials))) {
            return false;
        }

// Update passwords without salt (set manually in database)
        if ($user->password === sha1($password)) {
            $user->password = sha1($user->id . config('app.key') . $password);
        }

        if ($user->password !== sha1($user->id . config('app.key') . $password)) {
            return false;
        }

        if ($login) {
            static::login($user, $remember);
        }

        return true;
    }

    public static function check()
    {
        return static::user()->exists;
    }

    public static function checkAccess($permission, $model = null, $redirect = null)
    {
        if (is_bool($permission)) {
            if ($permission) {
                return true;
            }

            if ($redirect) {
                redirect($redirect);
            }

            throw new Exception('user has no access');
        }

        if (!(static::user() instanceof PrivilegedUser)) {
            throw new Exception('user has no privileges');
        }

        if (static::user()->may($permission, $model)) {
            return true;
        }

        if ($redirect) {
            redirect($redirect);
        }

        throw new Exception('user has no "' . $permission . '" permission for model "' . $model . '"');
    }

    public static function login(User $user, $remember = false)
    {
        static::$user     = $user;
        $_SESSION['user'] = (string) $user->token;

        if ($remember) {
            App::get('Cookie')->forever('user', $user->hash);
        }
    }

    public static function logout()
    {
        App::get('Cookie')->delete('user');
        $_SESSION = [];
        session_destroy();
    }

    public static function user(): User
    {
        if (static::$user === null) {
            static::$user = static::autoload();
        }

        return static::$user;
    }

    protected static function autoload(): User
    {
        $userClass = App::get('User');

        if (isset($_SESSION['user'])) {
            try {
                return $userClass::unpack($_SESSION['user']);
            } catch (ModelNotFoundException $e) {
                unset($_SESSION['user']);
            }
        }

        if (isset($_COOKIE['user'])) {
            try {
                $user             = $userClass::unpack($_COOKIE['user']);
                $_SESSION['user'] = (string) $user->token;

                return $user;
            } catch (ModelNotFoundException $e) {
                App::get('Cookie')->delete('user');
            }
        }

        return new $userClass();
    }
}
