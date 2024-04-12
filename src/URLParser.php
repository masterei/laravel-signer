<?php

namespace Masterei\Signer;

use Carbon\Carbon;
use Illuminate\Support\Facades\URL;
use Spatie\Url\Url as SpatieUrl;

class URLParser
{
    /**
     * Used package to manipulate urls.
     */
    protected SpatieUrl $url;

    public function __construct(string $url, public bool $absolute)
    {
        $this->url = SpatieUrl::fromString($url);
    }

    /**
     * Static instance to initialize the class; and to assign to dependency object.
     */
    public static function fromString(string $url, bool $absolute = true): self
    {
        return new self($url, $absolute);
    }

    /**
     * Static instance for native signed route.
     */
    public static function createSignedRoute(string $routeName, array $parameters, Carbon | null $expiration, bool $absolute): self
    {
        $signedRoute = URL::signedRoute($routeName, $parameters, $expiration, absolute: $absolute);
        return self::fromString($signedRoute, $absolute);
    }

    /**
     * Returns url path.
     * E.g: https://127.0.0.1:8000/test or /test
     */
    public function getPath(): string
    {
        return $this->absolute
            ? "{$this->url->getScheme()}://{$this->url->getAuthority()}{$this->url->getPath()}"
            : $this->url->getPath();
    }

    /**
     * Returns signature parameter attribute.
     */
    public function getSignature()
    {
        return $this->url->getQueryParameter('signature');
    }

    /**
     * Returns expires parameter attribute.
     */
    public function getExpiration(): int | null
    {
        return $this->url->getQueryParameter('expires') ?? null;
    }

    /**
     * Returns parameters excluding native signed route parameter
     * to be used as information and be store in database
     * exclusive to the package.
     */
    public function getParameters(): array
    {
        return $this
            ->url
            ->withoutQueryParameter('signature')
            ->withoutQueryParameter('expires')
            ->getAllQueryParameters();
    }

    /**
     * Returns signed routes; either native or a package signed route.
     * @param bool $prefixDomain argument is to force domain into url string
     * even if path is relative.
     */
    public function url(bool $prefixDomain = false): string
    {
        // omitting signature key
        $signedUrl = $this->url->withoutQueryParameter(Config::SIGNATURE_KEY);

        // for relative path with forced domain prefix
        if($prefixDomain && !$this->absolute) {
            $currentUrl = SpatieUrl::fromString(URL::current());
            return "{$currentUrl->getScheme()}://{$currentUrl->getAuthority()}{$signedUrl}";
        }

        return $signedUrl;
    }
}
