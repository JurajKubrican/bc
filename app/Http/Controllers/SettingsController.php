<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Place;
use App\User;

class SettingsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
      $user = Auth::user();
      $home = $user->home()->first();
      $props = [
        'search' => false,
      ];
      return view('settings')
        ->with('props',$props)
        ->with('home',$home);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      $user = Auth::user();
      $post = $request->all();

      if(!empty($post['search-data'])){
        $data = json_decode($post['search-data']);

        if( empty($data->canonicalName) ){
        return redirect('/settings');
        }
        $place = Place::findOrCreate($data);

        $user->home()->save($place);

      }elseif(!empty($post['password'])){
        if(empty($post['password-configm']))
        return redirect('/settings');

        if($post['password']!==$post['password-confirm'])
        return redirect('/settings');

        $user->setPassword($post['password']);
      }



      //$user->follows()->delete($place);

      //dd($user->follows(),Auth::check());

      return redirect('/settings');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
