<?php

namespace Modules\Posts\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Posts\Models\Ad;

class AdsController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $ads = Ad::where('active',1)->orderBy('order', 'asc')->paginate(10);
        foreach($ads as $key => $ad){
            $ads[$key]['image'] = $ad->image ? url('storage/images/ads/'. $ad->image): null;
        }
        $data = [
            'ads' => $ads,

        ];
        return $this->sendResponse($data);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $ad = Ad::where('id',$id)->first();
        $ad['image'] = $ad->image ? url('storage/images/ads/'. $ad->image) : null;
        return $this->sendResponse($ad);
    }
}
