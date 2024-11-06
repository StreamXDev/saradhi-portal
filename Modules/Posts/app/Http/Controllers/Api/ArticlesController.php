<?php

namespace Modules\Posts\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use Modules\Posts\Models\Article;

class ArticlesController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $articles = Article::where('active',1)->orderBy('order', 'asc')->paginate(15);
        foreach($articles as $key => $article){
            $articles[$key]['thumb'] = $article->thumb ? url('storage/images/articles/'. $article->thumb) : null;
        }
        $data = [
            'articles' => $articles
        ];
        return $this->sendResponse($data);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $article = Article::where('id',$id)->first();
        $article['thumb'] =  $article->thumb ?  url('storage/images/news/'. $article->thumb) : null;
        return $this->sendResponse($article);
    }
}
