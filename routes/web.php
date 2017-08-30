<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::get('/', ['as' => 'index', 'uses' => 'IndexController@index']);

Route::get('/login_page', 'IndexController@login_page');

Route::post('/login', ['as' => 'login', 'uses' => 'IndexController@login']);

Route::get('/logout', 'IndexController@logout');

Route::get('/clients', ['as' => 'clients', 'uses' => 'IndexController@get_clients_from_local_storage']);

Route::post('/load_clients', ['as' => 'clients', 'uses' => 'IndexController@get_client_list']);

Route::get('/invoices', ['as' => 'invoices', 'uses' => 'IndexController@invoices_page']);

Route::get('/devices', ['as' => 'devices', 'uses' => 'IndexController@devices_page']);

Route::get('/sales', ['as' => 'sales', 'uses' => 'IndexController@sales_page']);

Route::get('/ach_info', ['as' => 'ach_files', 'uses' => 'IndexController@ach_info_page']);

Route::post('/invoice', 'IndexController@handle_invoice_upload');

Route::post('/send_invoices', 'IndexController@send_invoices');

Route::group(array('prefix' => 'api'), function() {
	
	Route::post('/send_invoices', 'Api\Invoices\InvoicesApiController@send_invoices');

	Route::resource('devices', 'Api\Device\DeviceApiController');
	
	Route::put('clients/{fb_client_id}', 'Api\Clients\ClientsApiController@save_or_update');
	
	Route::get('get_clients/{access_token}', 'IndexController@get_client_list');	
	
});