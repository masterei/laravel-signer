<?php

namespace Masterei\Signer\Tests\Unit;

use Masterei\Signer\Maker;
use Masterei\Signer\Models\Signed;
use Masterei\Signer\Signer;
use Masterei\Signer\Tests\Models\TestUser as User;
use Masterei\Signer\Tests\TestCase;

class CleanUpRecordCommandTest extends TestCase
{
    public Maker $signer;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory(10)->create();

        $this->signer = Signer::route('test')
            ->consumable(1)
            ->authenticated(User::take(3)->get());
    }

    public function test_record_clean_up_older_than_days_stated()
    {
        // creating 5 entries without expiration
        for ($x = 0; $x < 5; $x++){
            $this->signer->make();
        }

        $this->travel(366 / 2)->days();

        // creating another record; this record must not be delete
        // when clean up command is executed
        for ($x = 0; $x < 5; $x++){
            $this->signer->make();
        }

        $this->travel(366 / 2)->days();

        $this->artisan('signer:clean-up 365')->assertSuccessful();
        $this->assertDatabaseCount(Signed::class, 5);
    }

    public function test_record_clean_up_for_expiring_urls()
    {
        for ($x = 0; $x < 5; $x++){
            $this->signer->expiration(now()->addMinute())->make();
        }

        $this->travel(2)->minutes();

        // creating another record; this record must not be delete
        // when clean up command is executed
        for ($x = 0; $x < 5; $x++){
            $this->signer->expiration(now()->addMinute())->make();
        }

        $this->artisan('signer:clean-up 365')->assertSuccessful();
        $this->assertDatabaseCount(Signed::class, 5);
    }
}
