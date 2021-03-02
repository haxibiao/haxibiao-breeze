<?php

namespace Haxibiao\Breeze\Tests\Feature\Web;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class IndexTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @group index
     * 网站首页
     */
    public function testIndexPage()
    {
        $response = $this->get("/");
        $response->assertStatus(200);
    }

    protected function tearDown(): void
    {

        parent::tearDown();
    }
}
