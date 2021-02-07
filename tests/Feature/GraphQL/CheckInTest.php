<?php

namespace Haxibiao\Breeze\Tests\Feature\GraphQL;

use App\User;
use Haxibiao\Breeze\GraphQLTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class CheckInTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory([
            'ticket' => 100,
        ])->create();

    }
    /**
     * 签到记录
     *
     * @group checkIn
     * @group testCheckInsQuery
     */
    public function testtestCheckInsQuery()
    {
        $query = file_get_contents(__DIR__ . '/CheckIn/checkInsQuery.graphql');

        $variables = [
            'days' => 1,
        ];
        $this->startGraphQL($query, $variables, $this->getRandomUserHeaders($this->user));
    }

    /**
     * 签到
     *
     * @group checkIn
     * @group testCreateCheckInMutation
     */
    public function testCreateCheckInMutation()
    {
        $query     = file_get_contents(__DIR__ . '/CheckIn/createCheckInMutation.graphql');
        $variables = [];
        $this->startGraphQL($query, $variables,$this->getRandomUserHeaders($this->user));
    }

}
