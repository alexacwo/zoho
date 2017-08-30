<?php

namespace App\Http\Controllers;

use DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;

class OAuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    { 
    }

    /**
     * Freshbooks login
     *
     * @return \Illuminate\Http\Response
     */
    public function fb_login()
    {
		$code = Input::get('code', false);
		
		if ($code != false) {
			$curl = curl_init();
				
			$auth_params = array(
				'grant_type' => 'authorization_code',
				'client_secret' => '1b00ec792a8ed44c091c14b9c98b7ae98696565a982c6b2eb084e3dd795db64d',
				'code' => $code,
				'client_id' => 'e6197267a03afab0492b2b3366b105e1b93473b15c2f1f01bfe88d60298bf315',
				'redirect_uri' => 'https://dev.motoparts.su/tasks/freshbooks/fb_login/'
			);

			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://api.freshbooks.com/auth/oauth/token",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => json_encode($auth_params),
			  CURLOPT_HTTPHEADER => array(
				"api-version: alpha",
				"cache-control: no-cache",
				"content-type: application/json",
				"postman-token: 471a0741-8466-2e3f-0006-8b9c3794ef9d"
			  ),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);

			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {
			  $access_token_object = json_decode($response); 
			  if (property_exists($access_token_object, 'access_token')) {
				$access_token = $access_token_object->access_token;
			  }
			}
			
			if (isset($access_token)) {
				session(['access_token' => $access_token]); 
				 
				return redirect()->route('index'); 
			} else {
				return view('index');				
			}
		}
		
    }

}
