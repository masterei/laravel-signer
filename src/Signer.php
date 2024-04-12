<?php

namespace Masterei\Signer;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User;

class Signer
{
    public static function route(string $name): Maker
    {
        return (new Maker())->route($name);
    }

    public static function signedRoute(string $route, array $parameters = [], Carbon | null $expiration = null, bool $absolute = true, bool $prefixDomain = false): string
    {
        $url = URLParser::createSignedRoute($route, $parameters, $expiration, absolute: $absolute);
        return $url->url($prefixDomain);
    }

    public static function temporarySignedRoute(string $route, Carbon $expiration, array $parameters = [], bool $absolute = true, bool $prefixDomain = false): string
    {
        return self::signedRoute($route, $parameters, $expiration, $absolute, $prefixDomain);
    }

    public static function consumableRoute(string $route, int $accessLimit = 1, array $parameters = [], Carbon | null $expiration = null, bool $absolute = true, bool $prefixDomain = false): string
    {
        return (new Maker())
            ->consumable($accessLimit)
            ->route($route)
            ->parameters($parameters)
            ->expiration($expiration)
            ->absolute($absolute)
            ->prefixDomain($prefixDomain)
            ->make();
    }

    public static function temporaryConsumableRoute(string $route, Carbon $expiration, int $accessLimit = 1, array $parameters = [], bool $absolute = true, bool $prefixDomain = false): string
    {
        return self::consumableRoute($route, $accessLimit, $parameters, $expiration, $absolute, $prefixDomain);
    }

    public static function authenticatedRoute(string $route, Collection | User | array | int $users, array $parameters = [], Carbon | null $expiration = null, bool $absolute = true, bool $prefixDomain = false): string
    {
        return (new Maker())
            ->authenticated($users)
            ->route($route)
            ->parameters($parameters)
            ->expiration($expiration)
            ->absolute($absolute)
            ->prefixDomain($prefixDomain)
            ->make();
    }

    public static function temporaryAuthenticatedRoute(string $route, Collection | User | array | int $users, Carbon $expiration, array $parameters = [], bool $absolute = true, bool $prefixDomain = false): string
    {
        return self::authenticatedRoute($route, $users, $parameters, $expiration, $absolute, $prefixDomain);
    }
}
