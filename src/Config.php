<?php

namespace Masterei\Signer;

class Config
{
    const SIGNATURE_KEY = 'signer';

    const PACKAGE_VERSION = '1.0.0';

    public static function get(string $key)
    {
        return config(self::SIGNATURE_KEY . ".$key");
    }

    public static function connection(): string
    {
        return !empty(self::get('database_connection'))
            ? self::get('database_connection')
            : config('database.default');
    }
}
