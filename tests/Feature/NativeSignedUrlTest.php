<?php

namespace Masterei\Signer\Tests\Feature;

use Masterei\Signer\Models\Signed;
use Masterei\Signer\Signer;
use Masterei\Signer\Tests\TestCase;

class NativeSignedUrlTest extends TestCase
{
    public function test_signed_url()
    {
        $url = Signer::signedRoute('test', ['type' => 'native']);
        $this->assertDatabaseEmpty(Signed::class);
        $this->withMiddleware('auth')->get($url)->assertOk();
    }

    public function test_relative_signed_url()
    {
        $url = Signer::signedRoute('test.relative', ['type' => 'relative'], absolute: false);
        $this->get($url)->assertOk();
    }

    public function test_using_strict_to_disable_native_validation()
    {
        $url = Signer::signedRoute('test.strict', ['type' => 'strict']);
        $this->get($url)->assertForbidden();
    }

    public function test_temporary_signed_url()
    {
        $url = Signer::temporarySignedRoute('test', now()->addMinute(), ['type' => 'temporary']);
        $this->get($url)->assertOk();
    }

    public function test_expired_temporary_signed_url()
    {
        $url = Signer::temporarySignedRoute('test', now()->addMinute(), ['type' => 'temporary']);
        $this->travel(2)->minutes();
        $this->get($url)->assertForbidden();
    }
}
