<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        //$this->middleware('permission:dashboard.view',['only' => ['index']]);
    }
    
    public function index()
    {
        return view('admin.dashboard.index');
    }

    public function sargaResult(Request $request)
    {
        $input = $request->all();

        if(isset($input['result'])){
            Storage::delete('public/result.pdf');

            $avatarName = 'result.pdf'; 
            $request->result->storeAs('public',$avatarName);

            return redirect('admin/dashboard')->with('success', 'Result updated successfully');
        }
        return redirect('admin/dashboard');

    }
}
