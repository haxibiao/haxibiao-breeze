<?php

namespace Haxibiao\Breeze\Tests\Feature\Web;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

    }

    /**
     * @group index
     * 用户页
     */
    public function testUserPage()
    {
        $response = $this->get("/user/1");
        $response->assertStatus(200);
    }

    protected function tearDown(): void
    {

        parent::tearDown();
    }
}
