<?php

namespace Haxibiao\Breeze\Tests\Feature\GraphQL;

use App\User;
use Haxibiao\Breeze\GraphQLTestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class MeetupTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $staff;
    protected $amdin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory([
            'ticket'  => 100,
            'role_id' => User::USER_STATUS,
            'name'    => '匿名',
        ])->create();

        $this->staff = User::factory([
            'role_id' => User::STAFF_ROLE,
        ])->create();

        $this->admin = User::factory([
            'role_id' => User::ADMIN_STATUS,
        ])->create();

        $this->vest  = User::factory([
            'role_id' => User::VEST_STATUS,
        ])->create();

        $this->editor = User::factory([
            'role_id' => User::EDITOR_STATUS,
        ])->create();
    }

    /**
     * 搜索用户(依据id搜索)
     * @group meetup
     * @group testSearchUserIdQuery
     */
    public function testSearchUserIdQuery()
    {
        $query = file_get_contents(__DIR__ . '/Meetup/searchUserIdQuery.graphql');
        $headers   = $this->getRandomUserHeaders($this->staff);
        $variables = [
            'id' => $this->user->id,
        ];
        $this->startGraphQL($query, $variables, $headers);
    }

    /**
     * 添加员工客户
     * @group meetup
     * @group testAddStaffAccountMutation
     */
    public function testAddStaffAccountMutation()
    {
        $query = file_get_contents(__DIR__ . '/Meetup/addStaffAccountMutation.graphql');
        $headers   = $this->getRandomUserHeaders($this->amdin);
        $variables = [
            'staff_id' => $this->user->id,
        ];
        $this->startGraphQL($query, $variables, $headers);
    }

    /**
     * 确定成为某客户的员工
     * @group meetup
     * @group testBecomeStaffAccountMutation
     */
    public function testBecomeStaffAccountMutation()
    {
        $query = file_get_contents(__DIR__ . '/Meetup/becomeStaffAccountMutation.graphql');
        $headers   = $this->getRandomUserHeaders($this->user);
        $variables = [
            'parent_id' => $this->staff->id,
        ];
        $this->startGraphQL($query, $variables, $headers);
    }

    /**
     * 删除员工
     * @group meetup
     * @group testDeleteStaffAccountMutation
     */
    public function testDeleteStaffAccountMutation()
    {
        $query = file_get_contents(__DIR__ . '/Meetup/deleteStaffAccountMutation.graphql');
        $headers   = $this->getRandomUserHeaders($this->amdin);
        $variables = [
            'staff_id' => $this->staff->id,
        ];
        $this->startGraphQL($query, $variables, $headers);
    }

    /**
     * 员工用户列表
     * @group meetup
     * @group testStaffAccountListsQuery
     */
    public function testStaffAccountListsQuery()
    {
        $query = file_get_contents(__DIR__ . '/Meetup/staffAccountListsQuery.graphql');
        $headers   = $this->getRandomUserHeaders($this->user);
        $this->startGraphQL($query, $headers);
    }

     /**
     * 关联马甲用户
     * @group testAssociateMasterAccountMutation
     * @group meetup
     */
    public function testAssociateMasterAccountMutation()
    {
        $query = file_get_contents(__DIR__ .'/Meetup/associateMasterAccountMutation.graphql');
        $headers = $this->getRandomUserHeaders($this->user);
        $variables = [
            'vest_ids' => [$this->vest->id],
            'master_id' => $this->editor->id,
        ];
        $this->startGraphQL($query,$variables,$headers);
    }

    /**
     * 搜索用户
     * @group testSearchedUsersQuery
     * @group meetup
     */
    public function testSearchedUsersQuery()
    {
        $query = file_get_contents(__DIR__ .'/Meetup/searchedUsersQuery.graphql');
        $headers = $this->getRandomUserHeaders($this->user);
        $variables = [
            'keywords' => $this->user->name,
        ];
        $this->startGraphQL($query,$variables,$headers);
    }

    /**
     * 查询马甲用户列表
     * @group testVestUserListsQuery
     * @group meetup
     */
    public function testVestUserListsQuery()
    {
        $query = file_get_contents(__DIR__ . '/Meetup/vestUserListsQuery.graphql');
        $headers = $this->getRandomUserHeaders($this->editor);
        $variables = [
            'role_id' => 'VEST',
        ];
        $this->startGraphQL($query,$variables,$headers);
    }
}
