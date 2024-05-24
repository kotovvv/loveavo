<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Hash;
use App\Models\Provider;
use App\Models\User;
use Session;

class loginController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function index()
  {
    //
  }
  public function login(Request $request)
  {
    $provider = Provider::where('name', $request->name)->first();

    if ($provider && Hash::check($request->password, $provider['password'])) {
      $provider->role_id = 4;
      return response()->json([
        'status'   => 'success',
        'user' =>  $provider,
      ]);
    }
    if (Auth::attempt(['name' => $request->name, 'password' => $request->password])) {
      $user                  = Auth::user();
      if (session()->get('office_id') === $user['office_id']) {
        $ses =  'Has session';
      } else {
        session()->put('office_id', $user['office_id']);
        session()->put('user_id', $user['id']);
        $ses =  'Created session';
      }
      return response()->json([
        'status'   => 'success',
        'user' => $user,
        'ses' => $ses
      ]);
    } else {
      return response()->json([
        'status' => 'error',
        'user'   => 'Unauthorized Access'
      ]);
    }
  }

  public function session(Request $request)
  {

    $data = $request->all();

    if (isset($data['id']) && session('user_id') == $data['id'] && session('office_id') === User::where('id', (int) $data['id'])->value('office_id')) {
      return 'has';
    } else {
      session(['office_id' => $data['office_id']]);
      session(['user_id' => $data['id']]);
      session(['role_id' => $data['role_id']]);
      return 'create';
    }
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request  $request
   * @return \Illuminate\Http\Response
   */
  public function store(Request $request)
  {
    //
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
