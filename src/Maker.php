<?php

namespace Masterei\Signer;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\User;
use Masterei\Signer\Models\Signed;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Maker
{
    protected int $accessLimit = 0;

    protected array $users = [];

    protected string $routeName = '';

    protected array $parameters = [];

    protected Carbon | null $expiredAt = null;

    protected bool $absolute = true;

    protected bool $prefixDomain = false;

    protected bool $withConsumable = false;

    protected bool $withAuthenticate = false;

    public function consumable(int $limit): self
    {
        $this->withConsumable = true;

        $this->accessLimit = $limit;
        return $this;
    }

    public function authenticated(Collection | User | array | int $users): self
    {
        $this->withAuthenticate = true;

        if($users instanceof User){
            $this->users[] = $users->id;
        } elseif ($users instanceof Collection){
            $this->users = $users->pluck('id')->toArray();
        }
        elseif (is_array($users)){
            $this->users = $users;
        } else {
            $this->users[] = $users;
        }

        return $this;
    }

    public function route(string $name): self
    {
        $this->routeName = $name;
        return $this;
    }

    public function parameters(array $parameters): self
    {
        $this->parameters = $parameters;
        return $this;
    }

    public function expiration(Carbon | null $expiration): self
    {
        $this->expiredAt = $expiration;
        return $this;
    }

    public function relative(bool $boolean = true): self
    {
        $this->absolute = $boolean;
        return $this;
    }

    public function prefixDomain(bool $boolean = true): self
    {
        $this->prefixDomain = $boolean;
        return $this;
    }

    protected function getAccessLimitData(): array
    {
        if(!$this->accessLimit){
            return [];
        }

        return [
            'consumable' => [
                'remaining_access' => $this->accessLimit,
                'access_limit' => $this->accessLimit
            ]
        ];
    }

    protected function getUsersData(): array
    {
        if(empty($this->users)) {
            return [];
        }

        return [
            'auth' => [
                'users' => $this->users
            ]
        ];
    }

    protected function signatureParameter(): array
    {
        if(!$this->withConsumable && !$this->withAuthenticate){
            return [];
        }

        return array(Config::SIGNATURE_KEY => Str::random(32));
    }

    protected function signedRoute(string $routeName, array $parameters, Carbon | null $expiration, bool $absolute, bool $prefixDomain): string
    {
        $data = array_merge($this->getAccessLimitData(), $this->getUsersData());
        $url = URLParser::createSignedRoute($routeName, array_merge($parameters, $this->signatureParameter()), $expiration, absolute: $absolute);

        // only store to database if any of the feature is being used
        if($this->withConsumable || $this->withAuthenticate){
            Signed::create([
                'path' => $url->getPath(),
                'signature' => $url->getSignature(),
                'expired_at' => $url->getExpiration(),
                'parameters' => [
                    'query' => $url->getParameters(),
                    'data' => array_merge([
                        'route' => $routeName,
                        'absolute' => $absolute,
                        'prefix_domain' => $prefixDomain
                    ], $data),
                ]
            ]);
        }

        return $url->url($prefixDomain);
    }

    public function make(): string
    {
        return $this->signedRoute($this->routeName, $this->parameters, $this->expiredAt, $this->absolute, $this->prefixDomain);
    }
}
