<?php

namespace Haxibiao\Breeze\Tests\Feature\GraphQL;

use App\User;
use Haxibiao\Breeze\GraphQLTestCase;
use Haxibiao\Breeze\Verify;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;

class UserTest extends GraphQLTestCase
{
    use DatabaseTransactions;

    protected $user;
    protected $userTo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory(
            [
                'uuid'  => "e0416b8f323a2dce",
                'phone' => "15580235001",
            ]
        )->create();
        $this->userTo = User::factory()->create();
    }
    /**
     * 拉黑用户
     *
     * @group  user
     * @group  testAddUserBlockMutation
     */
    public function testAddUserBlockMutation()
    {
        $headers   = $this->getRandomUserHeaders($this->user);
        $query     = file_get_contents(__DIR__ . '/User/addUserBlockMutation.graphql');
        $variables = [
            'id' => $this->userTo->id,
        ];
        $this->startGraphQL($query, $variables, $headers);
    }

    /**
     * 移除拉黑用户
     *
     * @group  user
     * @group  testRemoveUserBlockMutation
     */
    public function testRemoveUserBlockMutation()
    {
        $headers   = $this->getRandomUserHeaders($this->user);
        $query     = file_get_contents(__DIR__ . '/User/removeUserBlockMutation.graphql');
        $variables = [
            'id' => $this->userTo->id,
        ];
        $this->startGraphQL($query, $variables, $headers);
    }

    /**
     * 修改用户信息
     *
     * @group  user
     * @group  testUpdateUserInfoMutation
     */
    public function testUpdateUserInfoMutation()
    {
        $headers   = $this->getRandomUserHeaders($this->user);
        $query     = file_get_contents(__DIR__ . '/User/updateUserProfileMutation.graphql');
        $variables = [
            'id'   => $this->user->id,
            'data' => [
                'name'         => Str::random(5),
                'age'          => random_int(1, 20),
                'gender'       => '1',
                'email'        => '123456@haxibiao.com',
                'phone'        => '15567894244',
                'introduction' => Str::random(20),
            ],
        ];
        $this->startGraphQL($query, $variables, $headers);
    }

    /**
     * UUID登录
     * @group  user
     * @group  testAutoSignInMutation
     */
    public function testAutoSignInMutation()
    {
        $query = file_get_contents(__DIR__ . '/User/autoSignInMutation.graphql');

        //用户UUID
        $uuid = $this->user->uuid;
        $this->startGraphQL($query, [
            'UUID' => $uuid,
        ], []);

    }

    /**
     * 重置密码
     * @group  user
     * @group  testRetrievePasswordMutation
     */
    public function testRetrievePasswordMutation()
    {
        $query = file_get_contents(__DIR__ . '/User/retrievePasswordMutation.graphql');

        $code = rand(1000, 9999);
        Verify::create([
            'user_id' => $this->user->id,
            'code'    => $code,
            'channel' => 'sms',
            'account' => $this->user->phone,
            'action'  => 'RESET_PASSWORD',
        ]);
        $this->startGraphQL($query, [
            'code'        => $code,
            'phone'       => $this->user->phone,
            'newPassword' => '1122mm',
        ], []);
    }
    /**
     *账户密码登录
     * @group  user
     * @group  testSignInMutation
     */
    public function testSignInMutation()
    {
        $query = file_get_contents(__DIR__ . '/User/signInMutation.graphql');

        $this->startGraphQL($query, [
            'account'  => $this->user->account,
            'password' => 'password',
        ], []);
    }

    /**
     * 短信验证码登录
     * @group  user
     * @group  testSmsSignInMutation
     */
    public function testSmsSignInMutation()
    {
        $query = file_get_contents(__DIR__ . '/User/smsSignInMutation.graphql');

        $code = rand(1000, 9999);

        Verify::create([
            'user_id' => $this->user->id,
            'code'    => $code,
            'channel' => 'sms',
            'account' => $this->user->phone,
            'action'  => 'USER_LOGIN',
        ]);
        // 获取验证码
        $this->startGraphQL($query, [
            'code'  => $code,
            'phone' => $this->user->phone,
        ], []);
    }

    /**
     * 账户注销
     * @group  user
     * @group  testDestroyUserMutation
     */
    public function testDestoryUserMutation()
    {
        $query = file_get_contents(__DIR__ . '/User/destroyUserMutation.graphql');
        //新创建一个用户测试注销
        $destroyUser = User::factory()->create();
        $this->startGraphQL($query, [], $this->getRandomUserHeaders($destroyUser));
    }

    /**
     * 拉黑列表
     *
     * @group  user
     * @group  testShowUserBlockQuery
     */
    public function testShowUserBlockQuery()
    {
        $headers   = $this->getRandomUserHeaders($this->user);
        $query     = file_get_contents(__DIR__ . '/User/showUserBlockQuery.graphql');
        $variables = [
            'user_id' => $this->user->id,
        ];
        $this->startGraphQL($query, $variables, $headers);
    }

    /**
     * @group  user
     * @group  testUserQuery
     */
    public function testUserQuery()
    {
        $query     = file_get_contents(__DIR__ . '/User/userQuery.graphql');
        $variables = [
            'id' => $this->user->id,
        ];
        $this->startGraphQL($query, $variables, []);
    }

    /**
     * @group  user
     * @group  testMeMetaQuery
     */
    public function testMeMetaQuery()
    {
        $user      = User::find(1);
        $query     = file_get_contents(__DIR__ . '/User/meMetaQuery.graphql');
        $variables = [];
        $this->startGraphQL($query, $variables, $this->getRandomUserHeaders($this->user));
    }

    /**
     * @group  user
     * @group  testUnreadsQuery
     */
    public function testUnreadsQuery()
    {
        $query = file_get_contents(__DIR__ . '/User/UnreadsQuery.graphql');

        $this->startGraphQL($query, [], $this->getRandomUserHeaders($this->user));
    }

    protected function tearDown(): void
    {
        // Clear File
        $this->user->forceDelete();
        $this->userTo->forceDelete();
        parent::tearDown();
    }
}
