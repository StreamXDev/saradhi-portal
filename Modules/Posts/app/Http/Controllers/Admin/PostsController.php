<?php

namespace Modules\Posts\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Modules\Posts\Models\Post;

class PostsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::orderBy('created_at', 'desc')->paginate(25);
        return view('posts::admin.news.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('posts::admin.news.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
        ]);

        if($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput();       
        }

        $input = $request->all();

        if(isset($input['thumb'])){
            $thumbName = 'post_thumb_'.time().'.'.$request->thumb->extension(); 
            $request->thumb->storeAs('public/images/news', $thumbName);
            $input['thumb'] = $thumbName;
        }

        DB::beginTransaction();
        Post::create([
            'title' => $input['title'],
            'body' => $input['body'],
            'thumb' => $input['thumb'],
            'location' => $input['location'],
            'date' => $input['date'],
            'active' => isset($input['active']) ? 1: 0
        ]);

        DB::commit();

        return redirect('/admin/posts');
        
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $post = Post::where('id', $id)->first();
        return view('posts::admin.news.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $post = Post::where('id', $id)->first();
        return view('posts::admin.news.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
        ]);

        if($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput();       
        }

        $input = $request->all();

        $post = Post::where('id', $id)->first();

        if(isset($input['thumb'])){
            $existing_thumb = $post->thumb;
            if($existing_thumb){
                Storage::delete('public/images/news/'.$existing_thumb);
            }
            $thumbName = 'post_thumb_'.time().'.'.$request->thumb->extension(); 
            $request->thumb->storeAs('public/images/news', $thumbName);
            $input['thumb'] = $thumbName;
        }else{
            $input['thumb'] = $post->thumb;
        }

        DB::beginTransaction();
        Post::where('id', $id)->update([
            'title' => $input['title'],
            'body' => $input['body'],
            'thumb' => $input['thumb'],
            'location' => $input['location'],
            'date' => $input['date'],
            'active' => isset($input['active']) ? 1: 0
        ]);

        DB::commit();

        return redirect('/admin/posts/'.$id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        $existing_thumb = $post->thumb;
        if($existing_thumb){
            Storage::delete('public/images/news/'.$existing_thumb);
            
        }
        $post->delete();

        return redirect()->back()->with(
            ['message' => 'Post Deleted']
        );
    }
}
