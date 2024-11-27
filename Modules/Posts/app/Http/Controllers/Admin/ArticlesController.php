<?php

namespace Modules\Posts\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
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
        $article = Article::where('id', $id)->first();
        return view('posts::admin.articles.show', compact('article'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $article = Article::where('id', $id)->first();
        return view('posts::admin.articles.edit', compact('article'));
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

        $article = Article::where('id', $id)->first();

        if(isset($input['thumb'])){
            $existing_thumb = $article->thumb;
            if($existing_thumb){
                Storage::delete('public/images/articles/'.$existing_thumb);
            }
            $thumbName = 'article_thumb_'.time().'.'.$request->thumb->extension(); 
            $request->thumb->storeAs('public/images/articles', $thumbName);
            $input['thumb'] = $thumbName;
        }else{
            $input['thumb'] = $article->thumb;
        }

        DB::beginTransaction();
        Article::where('id', $id)->update([
            'title' => $input['title'],
            'body' => $input['body'],
            'thumb' => $input['thumb'],
            'date' => $input['date'],
            'active' => isset($input['active']) ? 1: 0
        ]);

        DB::commit();

        return redirect('/admin/articles/'.$id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $article = Article::findOrFail($id);

        $existing_thumb = $article->thumb;
        if($existing_thumb){
            Storage::delete('public/images/articles/'.$existing_thumb);
            
        }
        $article->delete();

        return redirect()->back()->with(
            ['message' => 'Article Deleted']
        );
    }
}
