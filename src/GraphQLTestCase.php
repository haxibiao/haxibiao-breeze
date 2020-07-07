<?php

namespace Haxibiao\Base;

use App\User;
use Illuminate\Foundation\Testing\TestCase;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\CreatesApplication;

abstract class GraphQLTestCase extends TestCase
{
    use CreatesApplication;
    use MakesGraphQLRequests;

    public function startGraphQL($query, $variables = [], $header = [])
    {
        $response = $this->postGraphQL([
            'query'     => $query,
            'variables' => $variables,
        ], $header);
        $response->assertOk();

        $this->assertNull($response->json('errors'));
        $this->assertNull($response->json('error'));
        return $response;
    }

    /**
     * 未登录时测试接口
     */
    public function runGuestGQL($query, $variables = [], $headers = [])
    {
        //主要测试新版本
        //$headers = array_merge($headers, ['version' => getLatestAppVersion()]);

        $response = $this->postGraphQL([
            'query'     => $query,
            'variables' => $variables,
        ], $headers);
        $response->assertOk();
        $this->assertNull($response->json('errors'));
        return $response;
    }

    /**
     * 随机用户已登录测试接口
     */
    public function runGQL($query, $variables = [], $headers = [])
    {
        return $this->runGuestGQL(
            $query,
            $variables,
            array_merge($headers, $this->getRandomUserHeaders())
        );
    }

    public function getRandomUserHeaders()
    {
        $user  = $this->getRandomUser();
        $token = $user->api_token;

        $headers = [
            'token'         => $token,
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];

        return $headers;
    }

    public function getRandomUser()
    {
        //从最近的新用户中随机找一个，UT侧重新用户的体验问题
        $user = User::latest('id')->take(100)->get()->random();
        return $user;
    }

    public function getHeaders($user): array
    {
        $token   = $user->api_token;
        $headers = [
            'token'         => $token,
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ];
        return $headers;
    }
}
