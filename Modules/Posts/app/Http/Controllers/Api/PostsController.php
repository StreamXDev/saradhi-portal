<?php

namespace Modules\Posts\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Posts\Models\Post;

class PostsController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::where('active',1)->orderBy('created_at', 'desc')->paginate(15);
        foreach($posts as $key => $post){
            $posts[$key]['thumb'] = $post->thumb ? url('storage/images/news/'. $post->thumb) : null;
        }
        $data = [
            'posts' => $posts
        ];
        return $this->sendResponse($data);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $post = Post::where('id',$id)->first();
        $post['thumb'] =  $post->thumb ?  url('storage/images/news/'. $post->thumb) : null;
        return $this->sendResponse($post);
    }

}
