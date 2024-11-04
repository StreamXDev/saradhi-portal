<?php

namespace Modules\Posts\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
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
        return view('posts::admin.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('posts::admin.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
        ],[
            'title.required'    => 'Title is required',
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
            'active' => 1
        ]);

        DB::commit();

        return redirect('/admin/posts');
        
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('posts::admin.show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('posts::admin.edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //
    }
}
