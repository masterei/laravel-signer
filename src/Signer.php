<?php

namespace Masterei\Signer;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User;

class Signer
{
    /**
     * Manual way to create signed URL which returns a Maker class
     * that is responsible for generating the said URL.
     */
    public static function route(string $name): Maker
    {
        return (new Maker())->route($name);
    }

    /**
     * Just a wrapper method of the native Signed::signedRoute().
     */
    public static function signedRoute(string $route, array $parameters = [], Carbon | null $expiration = null, bool $absolute = true, bool $prefixDomain = false): string
    {
        $url = URLParser::createSignedRoute($route, $parameters, $expiration, absolute: $absolute);
        return $url->url($prefixDomain);
    }

    /**
     * Just a wrapper method of the native Signed::temporarySignedRoute().
     */
    public static function temporarySignedRoute(string $route, Carbon $expiration, array $parameters = [], bool $absolute = true, bool $prefixDomain = false): string
    {
        return self::signedRoute($route, $parameters, $expiration, $absolute, $prefixDomain);
    }

    /**
     * Method used to create signed URL that can only be accessed by limited number of times.
     */
    public static function consumableRoute(string $route, int $accessLimit = 1, array $parameters = [], Carbon | null $expiration = null, bool $absolute = true, bool $prefixDomain = false): string
    {
        return (new Maker())
            ->consumable($accessLimit)
            ->route($route)
            ->parameters($parameters)
            ->expiration($expiration)
            ->relative($absolute)
            ->prefixDomain($prefixDomain)
            ->make();
    }

    /**
     * Method used to create consumable signed URL that expires after a specified amount of time.
     */
    public static function temporaryConsumableRoute(string $route, Carbon $expiration, int $accessLimit = 1, array $parameters = [], bool $absolute = true, bool $prefixDomain = false): string
    {
        return self::consumableRoute($route, $accessLimit, $parameters, $expiration, $absolute, $prefixDomain);
    }

    /**
     * Method used to create signed URL that can only be access by certain specified user/s.
     */
    public static function authenticatedRoute(string $route, Collection | User | array | int $users, array $parameters = [], Carbon | null $expiration = null, bool $absolute = true, bool $prefixDomain = false): string
    {
        return (new Maker())
            ->authenticated($users)
            ->route($route)
            ->parameters($parameters)
            ->expiration($expiration)
            ->relative($absolute)
            ->prefixDomain($prefixDomain)
            ->make();
    }

    /**
     * Method used to create authenticated signed URL that expires after a specified amount of time.
     */
    public static function temporaryAuthenticatedRoute(string $route, Carbon $expiration, Collection | User | array | int $users, array $parameters = [], bool $absolute = true, bool $prefixDomain = false): string
    {
        return self::authenticatedRoute($route, $users, $parameters, $expiration, $absolute, $prefixDomain);
    }
}
