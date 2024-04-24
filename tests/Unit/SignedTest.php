<?php

namespace Masterei\Signer\Tests\Unit;

use Masterei\Signer\Models\Signed;
use Masterei\Signer\Signer;
use Masterei\Signer\Tests\TestCase;
use Masterei\Signer\URLParser;

class SignedTest extends TestCase
{
    public function test_generated_signed_url_is_equal_to_reconstructed_url()
    {
        $signedUrl = Signer::route('test')
            ->parameters(['type' => 'manual'])
            ->relative()
            ->expiration(now()->addDay())
            ->authenticated([rand(1, 10)])
            ->consumable(rand(1, 10))
            ->prefixDomain(true)
            ->make();

        $parsedUrl = URLParser::fromString($signedUrl);

        $reconUrl = Signed::findValidSignature($parsedUrl->getSignature());
        $this->assertEquals($signedUrl, $reconUrl->url());
    }
}
