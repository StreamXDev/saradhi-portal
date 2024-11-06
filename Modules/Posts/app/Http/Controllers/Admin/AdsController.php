<?php

namespace Modules\Posts\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Modules\Posts\Models\Ad;

class AdsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ads = Ad::orderBy('order', 'asc')->paginate(25);
        return view('posts::admin.ads.index', compact('ads'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('posts::admin.ads.add');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,png,jpeg,gif,svg|max:2048',
        ]);

        if($validator->fails()){
            return Redirect::back()->withErrors($validator)->withInput();       
        }

        $input = $request->all();

        if(isset($input['image'])){
            $thumbName = 'ad_image_'.time().'.'.$request->image->extension(); 
            $request->image->storeAs('public/images/ads', $thumbName);
            $input['image'] = $thumbName;
        }

        DB::beginTransaction();
        Ad::create([
            'image' => $input['image'],
            'link' => $input['link'],
            'order' => $input['order'],
            'active' => 1
        ]);

        DB::commit();

        return redirect('/admin/ads');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        return view('posts::admin.ads.show');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('posts::admin.ads.edit');
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
