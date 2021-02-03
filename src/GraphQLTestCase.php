<?php

namespace Haxibiao\Breeze;

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
        $this->withExceptionHandling();
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

        $this->withExceptionHandling();

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
            array_merge($this->getRandomUserHeaders(), $headers)
        );
    }

    public function getRandomUserHeaders($user = null)
    {
        if(!$user){
            $user  = $this->getRandomUser();
        }
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
        //大部分场景，随机3个seeder出来的不同身份的用户，能发现3种角色的问题
        $user = User::whereIn('id', [1, 2, 3])->get()->random();
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
    public function getBase64ImageString()
    {
        return "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAAH6ji2bAAAABGdBTUEAALGPC/xhBQAAACBjSFJNAAB6JgAAgIQAAPoAAACA6AAAdTAAAOpgAAA6mAAAF3CculE8AAAACXBIWXMAAAsTAAALEwEAmpwYAAABWWlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPHg6eG1wbWV0YSB4bWxuczp4PSJhZG9iZTpuczptZXRhLyIgeDp4bXB0az0iWE1QIENvcmUgNS40LjAiPgogICA8cmRmOlJERiB4bWxuczpyZGY9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkvMDIvMjItcmRmLXN5bnRheC1ucyMiPgogICAgICA8cmRmOkRlc2NyaXB0aW9uIHJkZjphYm91dD0iIgogICAgICAgICAgICB4bWxuczp0aWZmPSJodHRwOi8vbnMuYWRvYmUuY29tL3RpZmYvMS4wLyI+CiAgICAgICAgIDx0aWZmOk9yaWVudGF0aW9uPjE8L3RpZmY6T3JpZW50YXRpb24+CiAgICAgIDwvcmRmOkRlc2NyaXB0aW9uPgogICA8L3JkZjpSREY+CjwveDp4bXBtZXRhPgpMwidZAAADi0lEQVQ4EZVUTUwdVRT+5s68H96jD/QBBTSSpkCAqiQEGpMSpdGnLoiRxFVj7LJdsDHRxAUmmoCuXBVdGNd21xUaU2QBiT+JUZ82NUGJYAX5USh9fcD7mxnPd2amFSsmvfPm3XvPfOc7556fa43MTvm1WhUcxvM8jD00EGyMBXy2dR3HkmlYZ+fe8auVCsxOrYT+VDNMk0lgzTuA9dTspG8sC+ulIprshCo7nusi5sTx6iNnkE1mcHn9WyH5fMp35QPNRMP4vg/HcWBD7IVDha/3jmLu2Yk7YmNbBh8uXkXFrWLq0TE0po4J59VJ3zYGSePAFy/4WiPiErjgI+col0qyvct/yA4d4PNH9QAT/WPIxlKos+wIo7NDEPXJ+ES6Fc+39+O5tseVdfzrj3CjdAs3JQCOEYARRwlcFeHTc+9iqL4dw03dqDMxeMLSUFcPh7xkFZwsJPji33fFDeT3NpSV/uozIlGLNpVySRTuPQjJDFmEUg9jJKJHDUN7xBIctx3sebVg/y8NTQNBzM2TD3bi7cdeDC0cRgojFHRWTnn6eDduF3bwwcDL+KJSCA4Z4pWx6nuY2foJ+Y1FnDuVQ0djK/wX3oOdTCLhxFRBGePGRkry+sZmXvXnV3/UeaFaRCKWwAPpDOyT5595i1LGqstJ4uOVr9BgxSQbBewUN+/kXVMYxc5l4OFhfncZC7dWRFUGZRLbAEiBSC2RsVjLcNUCrYTosMwoVvXgA5fkZrPc7zBUpDo90RTJQr0SqSORccTYP/tE4f/zJyURZpo+BT81wPr80ytjONuJrlQWBWkpW44ROHA0o0OARVIBE25L2f1e28crrf0YaOmG5fn47fYWEtJA7+8u4WEp+T4njX3f/U9WadqQTGb2zY5bxkvZHqmiXiztriEjDXOhLwf05DAdUswuf4PXfv4EzZLmMmtXdKP21sJRUhKLQtZOIl9Yw5vXruDT9R+Q6xhUmvXiNqyZcXy/+QtyJ4ZwsX0Qy8ZFS6pBCysj115MmkNLlldPTCqRcSM5SzjDbpZ17stLWNy+gbb6LPzRafRmO5DfWsLp5i4kBO+JG8TRw3Q8GVxZdCGqJc5BBem/JoIF+pdXQUEM/SrzCTsubx3ictWpE9QP6i4oWG54YMvnHRjmUSZiJCdqoEVu5xZR7EJKBWpWvhHPdRRHrUNuokfjSZcpoYI+jIRsAimFkfQezN8eCIorLk//GAAAAABJRU5ErkJggg==";
    }

}
