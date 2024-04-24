<?php

namespace Masterei\Signer\Tests\Feature;

use Illuminate\Database\Eloquent\Collection;
use Masterei\Signer\Signer;
use Masterei\Signer\Tests\Database\Factories\TestUserFactory;
use Masterei\Signer\Tests\Models\TestUser as User;
use Masterei\Signer\Tests\TestCase;
use Orchestra\Testbench\Factories\UserFactory;

class AuthenticatedUrlTest extends TestCase
{
    public Collection $users;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory(10)->create();

        $this->users = User::take(3)->get();
    }

    public function test_authenticated_access()
    {
        $urlWithParameters = Signer::authenticatedRoute('test', $this->users, ['type' => 'authenticated']);
        $this->actingAs($this->users->first())->get($urlWithParameters)->assertOk();

        $urlWithoutParameters = Signer::authenticatedRoute('test', $this->users);
        $this->actingAs($this->users->first())->get($urlWithoutParameters)->assertOk();
    }

    public function test_authenticated_forbidden_access()
    {
        $url = Signer::authenticatedRoute('test', $this->users);
        $this->actingAs($this->users->first())->get($url)->assertOk();

        // unauthorized user
        $unauthorizedUser = User::whereNotIn('id', $this->users->pluck('id')->toArray())->first();
        $this->actingAs($unauthorizedUser)->get($url)->assertForbidden();
    }

    public function test_not_logged_in_user_accessing_authenticated_url()
    {
        $url = Signer::authenticatedRoute('test', $this->users);
        $this->get($url)->assertUnauthorized();
    }

    public function test_temporary_authenticated_access()
    {
        $url = Signer::temporaryAuthenticatedRoute('test', now()->addMinute(), $this->users);
        $this->actingAs($this->users->first())->get($url)->assertOk();
    }

    public function test_temporary_authenticated_expired_url()
    {
        $url = Signer::temporaryAuthenticatedRoute('test', now()->addMinute(), $this->users);

        // current access
        $this->actingAs($this->users->first())->get($url)->assertOk();

        // in future access
        $this->travel(2)->minutes();
        $this->actingAs($this->users->first())->get($url)->assertForbidden();
    }
}
