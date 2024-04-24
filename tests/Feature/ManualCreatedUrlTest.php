<?php

namespace Masterei\Signer\Tests\Feature;

use Illuminate\Database\Eloquent\Collection;
use Masterei\Signer\Maker;
use Masterei\Signer\Models\Signed;
use Masterei\Signer\Signer;
use Masterei\Signer\Tests\Models\TestUser as User;
use Masterei\Signer\Tests\TestCase;

class ManualCreatedUrlTest extends TestCase
{
    public Maker $signerConsume;

    public Collection $users;

    public Maker $signerAuthenticated;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory(10)->create();

        $this->signerConsume = Signer::route('test')->consumable(1);

        $this->users = User::take(3)->get();

        $this->signerAuthenticated = Signer::route('test')->authenticated($this->users);
    }

    public function test_consumable_as_successful_access()
    {
        $this->get($this->signerConsume->make())->assertOk();
    }

    public function test_consumable_as_already_reached_access_limit()
    {
        $url = $this->signerConsume->make();
        $this->get($url); // consuming access limit

        // must already reached the access limit
        $this->get($url)->assertForbidden();
    }

    public function test_consumable_with_parameters()
    {
        $this->get($this->signerConsume->parameters(['type' => 'manual'])->make())->assertOk();
    }

    public function test_consumable_as_relative_path()
    {
        $this->get($this->signerConsume->relative()->make())->assertOk();
    }

    public function test_consumable_but_expired_url()
    {
        $url = $this->signerConsume->expiration(now()->addMinute())->make();
        $this->travel(2)->minutes();
        $this->get($url)->assertForbidden();
    }

    public function test_no_feature_used_wherein_it_fallback_as_native_signed_url()
    {
        $url = Signer::route('test')->parameters(['type' => 'native'])->make();
        $this->assertDatabaseEmpty(Signed::class);
        $this->get($url)->assertOk();
    }

    public function test_authenticated_as_successful_access()
    {
        $this->actingAs($this->users->first())->get($this->signerAuthenticated->make())->assertOk();
    }

    public function test_authenticated_url_accessed_by_not_logged_in_user()
    {
        $this->get($this->signerAuthenticated->make())->assertUnauthorized();
    }

    public function test_authenticated_but_expired_url()
    {
        $url = $this->signerAuthenticated->expiration(now()->addMinute())->make();
        $this->travel(2)->minutes();
        $this->actingAs($this->users->first())->get($url)->assertForbidden();
    }

    public function test_overall_combined_methods()
    {
        $url = Signer::route('test')
            ->parameters(['type' => 'manual'])
            ->consumable(1)
            ->authenticated($this->users)
            ->expiration(now()->addMinute())
            ->relative()
            ->prefixDomain()
            ->make();

        $this->get($url)->assertUnauthorized();
        $this->actingAs($this->users->first())->get($url)->assertOk();

        $this->get($url)->assertForbidden();
        $this->actingAs($this->users->first())->get($url)->assertForbidden();
    }
}
