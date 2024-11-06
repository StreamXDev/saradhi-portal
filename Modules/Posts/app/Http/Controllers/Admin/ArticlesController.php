<?php

namespace Modules\Posts\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Modules\Posts\Models\Article;

class ArticlesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articles = Article::orderBy('created_at', 'desc')->paginate(25);
        return view('posts::admin.articles.index', compact('articles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('posts::admin.articles.add');
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
            $thumbName = 'article_thumb_'.time().'.'.$request->thumb->extension(); 
            $request->thumb->storeAs('public/images/articles', $thumbName);
            $input['thumb'] = $thumbName;
        }

        DB::beginTransaction();
        Article::create([
            'title' => $input['title'],
            'body' => $input['body'],
            'thumb' => $input['thumb'],
            'order' => $input['order'],
            'active' => 1
        ]);

        DB::commit();

        return redirect('/admin/articles');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('posts::show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('posts::edit');
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
