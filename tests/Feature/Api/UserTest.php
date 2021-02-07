<?php

namespace Haxibiao\Breeze\Tests\Feature\Api;

use App\User;
use Illuminate\Http\UploadedFile;
use Haxibiao\Breeze\GraphQLTestCase;

class UserTest extends GraphQLTestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory(
            [
                'uuid'  => "e0416b8f323a2dce",
                'name'  => "faker",
                'phone' => "15580235001",
            ]
        )->create();
    }

    /**
     * @group userApi
     * @group testUserRecommend
     */
    public function testUserRecommend()
    {
        $response = $this->get('/api/user/recommend', [
            'page' => '1',
        ]);
        $response->assertStatus(200);
    }
    /**
     * @group userApi
     * @group testIndex
     */
    public function testIndex()
    {

        $response = $this->get('api/user/index');

        $response->assertStatus(200);
    }
    /**
     * @group userApi
     * @group testUser
     */
    public function testUser()
    {
        $response = $this->get('/api/user/' . $this->user->id);
        $response->assertStatus(200);
    }
    /**
     * @group userApi
     * @group testSearchUserName
     */
    public function testSearchUserName()
    {
        $response = $this->get('/api/user/name/' . $this->user->name);
        $response->assertStatus(200);
    }

    /**
     * @group userApi
     * @group testUserImages
     */
    public function testUserImages()
    {
        $response = $this->get('/api/user/' . $this->user->id . '/images');
        $response->assertStatus(200);
    }

    /**
     * @group userApi
     * @group testUserVideos
     */
    public function testUserVideos()
    {
        $response = $this->get('/api/user/' . $this->user->id . '/videos');
        $response->assertStatus(200);
    }

    /**
     * @group userApi
     * @group testUserArticles
     */
    public function testUserArticles()
    {
        $response = $this->get('/api/user/' . $this->user->id . '/articles');
        $response->assertStatus(200);
    }

    /**
     * @group userApi
     * @group testRelatedVideos
     */
    public function testRelatedVideos()
    {
        $response = $this->get('/api/user/' . $this->user->id . '/videos/relatedVideos');
        $response->assertStatus(200);
    }

    /**
     * @group userApi
     * @group testRelatedUsers
     */
    public function testRelatedUsers()
    {

        $response = $this->get("/api/related-users?api_token={$this->user->api_token}");
        $response->assertStatus(200);
    }

    /**
     * @group userApi
     * @group testUnreads
     */
    public function testUnreads()
    {
        $response = $this->get("/api/unreads?api_token={$this->user->api_token}");
        $response->assertStatus(200);
    }

    /**
     * @group userApi
     * @group testUserInfo
     */
    public function testUserInfo()
    {
        $response = $this->post("/api/user", [
            'api_token' => $this->user->api_token,
        ]);
        $response->assertStatus(200);
    }

    /**
     * @group userApi
     * @group testUserFollow
     */
    public function testUserFollow()
    {
        $response = $this->post('/api/user/' . $this->user->id . '/follow', [
            'api_token' => $this->user->api_token,
        ]);
        $response->assertStatus(200);
    }

    /**
     * @group userApi
     * @group testEditors
     */
    public function testEditors()
    {
        $response = $this->get("/api/user/editors?api_token={$this->user->api_token}");
        $response->assertStatus(200);
    }

    /**
     * @group userApi
     * @group testSaveAvatar
     */
    public function testSaveAvatar()
    {
        /**
         * base64格式
         */
        $image =  $this->getBase64ImageString();
        $response = $this->post("api/user/save-avatar", [
            'api_token' => $this->user->api_token,
            'avatar'    => $image,
        ]);
        $response->assertStatus(200);

        /**
         * file格式
         */
        $imageFile = UploadedFile::fake()->image('avatar1.jpg');
        $image =  $this->getBase64ImageString();
        $response = $this->post("api/user/save-avatar", [
            'api_token' => $this->user->api_token,
            'avatar'    => $imageFile,
        ]);
        $response->assertStatus(200);
    }

    /**
     * @group userApi
     * @group testRegister
     */
    public function testRegister()
    {
        $response = $this
        ->actingAs($this->user)
        ->withoutMiddleware()
        ->post('/register', [
            'name'     => 'gaoxuan',
            'email'    => 'gaoxuan@haxibiao.com',
            'password' => '123123',
        ]);

        $response->assertStatus(302);
    }

    /**
     * @group userApi
     * @group testLogin
     */
    public function testLogin()
    {
            
        $response = $this->actingAs($this->user)
        ->withSession([
            '_token'=>false,
        ])
        ->withoutMiddleware()
        ->post('/login', [
            'email'    => $this->user->email,
            'password' => 'password',
        ]);
        $response->assertStatus(302);
    }

}
