<?php

namespace Haxibiao\Breeze\Tests\Api;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
        $routeCollection = Route::getRoutes();

        foreach ($routeCollection as $value) {
            info($value->uri);
        }
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

    // public function testRelatedUsers()
    // {
    //     $response = $this->get('/related-users');
    //     $response->assertStatus(200);
    // }


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
