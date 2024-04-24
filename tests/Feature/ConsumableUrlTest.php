<?php

namespace Masterei\Signer\Tests\Feature;

use Masterei\Signer\Signer;
use Masterei\Signer\Tests\TestCase;

class ConsumableUrlTest extends TestCase
{
    public function test_consumable_access()
    {
        $urlWithParameters = Signer::consumableRoute('test', 1, ['type' => 'consumable']);
        $this->get($urlWithParameters)->assertOk();

        $urlWithoutParameters = Signer::consumableRoute('test', 1);
        $this->get($urlWithoutParameters)->assertOk();
    }

    public function test_consumable_forbidden_access()
    {
        $accessLimit = rand(2, 5);
        $url = Signer::consumableRoute('test', $accessLimit);

        $response = $this->get($url);
        $response->assertOk();

        // consuming all access limit
        for ($x = 0; $x < $accessLimit; $x++) {
            $response = $this->get($url);
        }

        $response->assertForbidden();
    }

    public function test_temporary_consumable_access()
    {
        $url = Signer::temporaryConsumableRoute('test', now()->addMinute(), 1, ['type' => 'consumable']);
        $this->get($url)->assertOk();
    }

    public function test_temporary_consumable_forbidden_access()
    {
        $accessLimit = rand(2, 5);
        $url = Signer::temporaryConsumableRoute('test', now()->addMinute(), $accessLimit);

        $response = $this->get($url);
        $response->assertOk();

        // consuming all access limit
        for ($x = 0; $x < $accessLimit; $x++) {
            $response = $this->get($url);
        }

        $response->assertForbidden();
    }

    public function test_temporary_consumable_expired_url()
    {
        $url = Signer::temporaryConsumableRoute('test', now()->addMinute(), 1);

        $this->travel(2)->minutes();
        $this->get($url)->assertForbidden();
    }
}
