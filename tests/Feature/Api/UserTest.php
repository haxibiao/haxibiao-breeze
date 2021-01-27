<?php

namespace Haxibiao\Breeze\Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Haxibiao\Breeze\User;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */

    public function testUserRecommend()
    {
          $response = $this->get('/api/user/recommend',[
              'page' => '1'
          ]);
          $response->assertStatus(200);
    }

    public function testIndex()
    {

        $response = $this->get('api/user/index');

        $response->assertStatus(200);
    }

    public function testUser()
    {
          $response = $this->get('/api/user/2');
          $response->assertStatus(200);
    }

     public function testSearchUserName()
    {
          $response = $this->get('/api/user/name/gaoxuan');
          $response->assertStatus(200);
    }

      public function testUserImages()
    {
          $response = $this->get('/api/user/2/images');
          $response->assertStatus(200);
    }

     public function testUserVideos()
    {
          $response = $this->get('/api/user/2/videos');
          $response->assertStatus(200);
    }

     public function testUserArticles()
    {
          $response = $this->get('/api/user/2/articles');
          $response->assertStatus(200);
    }


     public function testRelatedVideos()
    {
          $response = $this->get('/api/user/2/videos/relatedVideos');
          $response->assertStatus(200);
    }

    public function testRelatedUsers()
    {
        $user =  User::inRandomOrder()
            ->first();
        $response = $this->get("/api/related-users?api_token={$user->api_token}");
        $response->assertStatus(200);
    }

     public function testUnreads()
    {
         $user =  User::inRandomOrder()
            ->first();
        $response = $this->get("/api/unreads?api_token={$user->api_token}");
        $response->assertStatus(200);
    }

     public function testUserInfo()
    {
         $user =  User::inRandomOrder()
            ->first();
        $response = $this->post("/api/user",[
              'api_token' => $user->api_token,
        ]);
        $response->assertStatus(200);
    }

    public function testUserFollow()
    {
         $user =  User::inRandomOrder()
            ->first();
        $response = $this->post("/api/user/2/follow",[
              'api_token' => $user->api_token,
        ]);
        $response->assertStatus(200);
    }

      public function testEditors()
    {
         $user =  User::inRandomOrder()
            ->first();
        $response = $this->get("/api/user/editors?api_token={$user->api_token}");
        $response->assertStatus(200);
    }

      public function testSaveAvatar()
    {
         $user =  User::inRandomOrder()
            ->first();
        $image1 = UploadedFile::fake()->image('avatar1.jpg');
        // dd($image1);
        $response = $this->post("api/user/save-avatar",[
              'api_token' => $user->api_token,
              'avatar'=>$image1,
        ]);
        $response->assertStatus(200);
    }
    

    public function testRegister()
    {
         $response = $this->post('/register', [
            'name'     => 'gaoxuan',
            'email'    => 'gaoxuan@haxibiao.com',
            'password' => '123123',
        ]);

        $response->assertStatus(302);
    }

     public function testLogin()
    {
         $response = $this->post('/login', [
            'email'    => 'gaoxuan@haxibiao.com',
            'password' => '123123',
        ]);

        $response->assertStatus(302);
    }

  
    
}
