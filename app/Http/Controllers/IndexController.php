<?php

namespace App\Http\Controllers;

use DB;

use App\Client;
use App\Device;
use App\Sale;
use App\AchLog;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use App\Http\Controllers\Controller;
use App\Repositories\DeviceRepository;

use Maatwebsite\Excel\Facades\Excel;

class IndexController extends Controller
{
	 /**
     * The device repository instance.
     *
     * @var DeviceRepository
     */
    protected $devices;
	
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    { 
		/*
		jesse@pahoda.com
		V@nd@!ize
		https://accounts.zoho.com/u/h#sessions/userauthtoken
		*/
		
		$this->organization_id = "647302684";
		
		$this->authtoken_create_url = "https://accounts.zoho.com/apiauthtoken/nb/create";
		$this->list_clients_url = "https://books.zoho.com/api/v3/contacts?";
		$this->get_single_client_url = "https://books.zoho.com/api/v3/contacts/";
		$this->create_invoices_url = "https://books.zoho.com/api/v3/invoices?";
		
		$this->zoho_clients_file = storage_path('files/zoho_clients/zoho_clients_list.txt');
			
    }
	
    /**
     * Frontpage
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    { 	 
		return view('pages.index');		 
    }

    /**
     * Login_page
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function login_page(Request $request)
    { 	
		session(['login_content' => 1]);
		 
		return view('pages.login_page');			 
    }
	
    /**
     * Login via ZOHO Books
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
		$data = array(
			'SCOPE' => 'ZohoBooks/booksapi',
			'EMAIL_ID' => $request->input('email'),
			'PASSWORD' => $request->input('password')
		);
		
		$curl = curl_init();
		
		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->authtoken_create_url,
			CURLOPT_POST => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_POSTFIELDS => http_build_query($data)
			));
		
		$response = curl_exec($curl); 
		$err = curl_error($curl);

		curl_close($curl);
						
		if ($err) {
			echo "cURL Error #:" . $err;
			echo 'There was some error during authentication. (Curl)';
		} else {
			$pos = strpos($response, 'AUTHTOKEN=');
			if ($pos) {
				$substring = substr($response, $pos + 10); 
				preg_match('/^(\S*)\s/',$substring,$output);
				$authtoken = substr($output[0],0,strlen($output[0])-1);
				
				
				//echo "Auth token:" . $authtoken;
				
				$request->session()->forget('login_content');
				
				session(['authtoken' => $authtoken]);
				return redirect()->route('index');
		
			} else {
				echo 'There was some error during authentication.';
				echo '<br>Probably wrong credentials or exceeded authtokens limit.';				
				echo '<br>You can remove old authtokens <a href="https://accounts.zoho.eu/u/h#sessions/userauthtoken">here</a>';
			}			
		}	 
    }
	
    /**
     * Logout
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    { 	
		$request->session()->forget('authtoken');
		
		return redirect()->route('index'); 	 
    }
		
    /**
     * Invoices
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function invoices_page(Request $request)
    { 		
		return view('pages.invoices_page');					
    }
	
    /**
     * Devices
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function devices_page(Request $request)
    { 
			 
			/*$auth_data = array(
				'authtoken' => session('authtoken'),
				'organization_id' => $this->organization_id 
			);
$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL =>  "https://books.zoho.com/api/v3/invoices/663126000001645845/status/sent?",
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => http_build_query($auth_data), 
				CURLOPT_RETURNTRANSFER => 1
			));   
			
			$res = curl_exec($curl); 
			$err = curl_error($curl);

			curl_close($curl);
			
			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {
var_dump($res);	
			}
			*/

			
		$auth_data = array(
			'authtoken' => session('authtoken'),
			'organization_id' => $this->organization_id
		);
		
		$access_token = session('access_token');
		
		$devices = Device::all();
		
		//Get client list from Zoho Books	
		//$client_list = $this->get_client_list();
		$client_list = $this->get_clients_from_local_storage();
		
		return view('pages.devices_page', [
			'access_token' => $access_token,
			'devices' => $devices,
			'client_list' => $client_list
		]);		 
    }
	
    /**
     * Sales
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function sales_page(Request $request)
    { 		
		$access_token = session('access_token');
		
		$sales = Sale::with('device')
			->orderBy('id', 'desc')
			->get();
		 
		return view('pages.sales_page', [
			'access_token' => $access_token,
			'sales' => $sales
		]);		 
    }
	
    /**
     * Sales
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function ach_info_page(Request $request)
    { 	/*		$auth_data = array(
			'authtoken' => session('authtoken'),
			'organization_id' => $this->organization_id
		);
		
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL =>
			"https://books.zoho.com/api/v3/invoices/663126000001645845?authtoken=eb8e6b71165069ab4613a0d391530225&organization_id=647302684",
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_RETURNTRANSFER => 1
		));   
		
		$res = curl_exec($curl); 
		$err = curl_error($curl);

		curl_close($curl);
						
		if ($err) {
			echo "cURL Error #:" . $err;
			return 'Client list failed to download.';
		} else { 
		echo '<pre>';
			var_dump(json_decode($res));
			
		}	*/	
		$access_token = session('access_token');
		
		$ach_files = AchLog::orderBy('date', 'DESC')->get();
		 		 		
		$clients = Client::all()->keyBy('fb_client_id');	
		
		return view('pages.ach_info_page', [
			'access_token' => $access_token,
			'ach_files' => $ach_files,
			'clients' => $clients
		]);		 
    }
	
    /**
     * Get the list of clients from ZOHO Books
     * @return \Illuminate\Http\Response
     */
    public function get_clients_from_local_storage()
    { 
		$clients_content = file_get_contents($this->zoho_clients_file);
		$clients_array = json_decode($clients_content);
		return $clients_array;
	}
	
    /**
     * Get the list of clients from ZOHO Books
     * @return \Illuminate\Http\Response
     */
    public function get_client_list()
    { 	
		//https://books.zoho.eu/api/v3/contacts?authtoken=489594db68ed1320b1c7ecf4a6c9d17d&organization_id=20060098044
		
		$clients_array = array();
		for ($i = 1; $i<=18;$i++) {
			$auth_data = array(
				'page' => $i,
				'per_page' => 100,
				'authtoken' => session('authtoken'),
				'organization_id' => $this->organization_id
			);
			
			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->list_clients_url . http_build_query($auth_data),
				CURLOPT_CUSTOMREQUEST => "GET",
				CURLOPT_RETURNTRANSFER => 1
			));   
			
			$response = curl_exec($curl); 
			$err = curl_error($curl);

			curl_close($curl);
							
			if ($err) {
				echo "cURL Error #:" . $err;
				return 'Client list failed to download.';
			} else {
				$client_list = json_decode($response);
				 
				if($client_list->code === 0) { 				
					if (property_exists($client_list, 'contacts')) {	
						$clients = $client_list->contacts;
						
						foreach ($clients as $index => $client) {
							if ($client->contact_type == 'vendor') {
								unset($clients[$index]);
							} else {
								$clients_array[] = $client;
							}								
						}
									
					} else return 'Some error occured.';	
				} else {				
					return 'Some error occured. Code: ' . $client_list->code . ', message: ' . $client_list->message;
				}
			}
		}
	
		usort ($clients_array, function($a, $b) {
			return strcmp($a->company_name, $b->company_name);
		});
		
		if (file_put_contents($this->zoho_clients_file, json_encode($clients_array))) {
			return response()->json([
				'result' => 'Client list updated successfully.'
			]);
		}
	}
	
    /**
     * Get a single client from ZOHO books
     * @param  ZOHO books Client id $zb_client_id
     * @return \Illuminate\Http\Response
     */
    public function get_single_client($zb_client_id)
    { 	
		$auth_data = array(
			'authtoken' => session('authtoken'),
			'organization_id' => $this->organization_id
		);
		
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->get_single_client_url . $zb_client_id . "?" . http_build_query($auth_data),
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_RETURNTRANSFER => 1
		));   
		
		$response = curl_exec($curl); 
		$err = curl_error($curl);

		curl_close($curl);
						
		if ($err) {
			echo "cURL Error #:" . $err;
			return 'Client info failed to download.';
		} else {
			$client_response = json_decode($response); 
 
			if (property_exists($client_response, 'code') && $client_response->code == 0) {				
				if (property_exists($client_response, 'contact')) {	
					 
					 return $client_response->contact;
					 
				} else return 'Some error occured.';	
			} else return 'Some error occured: ' . $client_response->message;
		}		
    }
	
    /**
     * Get organization by client id
     * @param  ZohoBooks Client Id $fb_client_id
     * @param  Client list $client_list
     * @return \Illuminate\Http\Response
     */
    public function get_client_name_by_id($fb_client_id, $client_list)
    {
		foreach($client_list as $client) {
			if ($client->contact_id == $fb_client_id) return $client->contact_name;
		} 		
	}
		
    /**
     * Search through array of arrays
     * @param  Array $array
     * @return true or false
     */
    public function search_array($twod_array, $value)
    {
		foreach ($twod_array as $array) {
			if ($array["serial"] == $value) return true;
		}
		return false;	
	}
	
    /**
     * Create invoice
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function handle_invoice_upload(Request $request)
    { 	
		$devices = Device::all();
 		
		$devicesToUpload = array();
		$rowsToInvoices = array();
		
		if (Input::hasFile( 'excel_invoice' )) {
			
			$devicesCollection = Device::all();
			$serial_numbers_array = array();
			
			// First check if the device from the sheet is already in the database and if there are any that are not, add these rows to $devicesToUpload array
			// When going through rows we also assign rows with devices that are already in the database to the second array - $rowsToInvoices
			// After it we check if $devicesToUpload is empty. In this case we don't have to go through the rows two times
			Excel::load(Input::file('excel_invoice'), function ($reader) use($devicesCollection, &$devicesToUpload, &$rowsToInvoices, &$serial_numbers_array) {
				
				$results = $reader->get();
	
				foreach ($reader->toArray() as $row) {
					 
					if ($devicesCollection->contains('serial', $row['serial'])) {							
						$rowsToInvoices[] = $row;
					} else if ($row['serial'] != "" && !in_array($row['serial'], $serial_numbers_array)) {
						$devicesToUpload[] = $row;
						$serial_numbers_array[] = $row['serial'];
					}
				} 
			});
			
			$access_token = session('access_token');
			
			//Get client list from Freshbooks	
			//$client_list = $this->get_client_list($access_token);
			$client_list = $this->get_clients_from_local_storage($access_token);
				
			if (!empty($devicesToUpload)) {
				// There are some new devices				
			 
				return view('pages.devices_upload', [
					'access_token' => $access_token,
					'devices_to_upload' => $devicesToUpload,
					'client_list' => $client_list
				]);
			} else {
				
				$client_names_array = array();
				$unique_devices_array = array();
				
				// There are no new devices, upload an invoice
				foreach ($rowsToInvoices as &$row) {
					
					if (!array_key_exists($row['serial'], $unique_devices_array)) {
						$unique_devices_array[$row['serial']] = array(
							'make' => $row['make'],
							'model' => $row['model'],
							'contract_id' => $row['contract_id'],
							'city' => $row['city'],
							'stateprovince' => $row['stateprovince'],
							'mono_beg' => $row['mono_beg'],
							'mono_end' => $row['mono_end'],
							'color_beg' => $row['color_beg'],
							'color_end' => $row['color_end'],
							'monocolor1_beg' => $row['monocolor1_beg'],
							'monocolor1_end' => $row['monocolor1_end'],
							'colorlevel2_beg' => $row['colorlevel2_beg'],
							'colorlevel2_end' => $row['colorlevel2_end'],
							'colorlevel3_beg' => $row['colorlevel2_beg'],
							'colorlevel3_end' => $row['colorlevel3_end'],
							'device_total' => $row['device_total'],
							'mono_usage' => 0,
							'color_usage' => 0,
							'monocolor_usage' => 0,
							'color2_usage' => 0,
							'color3_usage' => 0,
						);						
					
					
						$existing_device = $devicesCollection->filter(function($item) use ($row) {
							return $item->serial == $row['serial'];
						})->first();
					
						//We need to send the id of a customer to Freshbooks , but we also need to show its name in the admin panel
						if (array_key_exists ($existing_device->fb_client_id, $client_names_array)) {
							$unique_devices_array[$row['serial']]['customer_title'] = $client_names_array[$existing_device->fb_client_id];
						} else {
							$unique_devices_array[$row['serial']]['customer_title'] = $this->get_client_name_by_id($existing_device->fb_client_id, $client_list);
							$client_names_array[$existing_device->fb_client_id] = $unique_devices_array[$row['serial']]['customer_title'];
						}
						$unique_devices_array[$row['serial']]['customer'] = $existing_device->fb_client_id;
											
						$client = Client::where('fb_client_id', $existing_device->fb_client_id)->first();
						
						$unique_devices_array[$row['serial']]['add_ach'] = (!empty($client)) ? $client->add_ach : 0;
						$unique_devices_array[$row['serial']]['routing_no'] = (!empty($client)) ? $client->routing_no : "";
						$unique_devices_array[$row['serial']]['bank_account'] = (!empty($client)) ? $client->bank_account : "";
						
					}
					
					$unique_devices_array[$row['serial']]['mono_usage'] += (int) $row['mono_usage'];
					$unique_devices_array[$row['serial']]['color_usage'] += (int) $row['color_usage'];
					$unique_devices_array[$row['serial']]['monocolor_usage'] += (int) $row['monocolor1_usage'];
					$unique_devices_array[$row['serial']]['color2_usage'] += (int) $row['colorlevel2_usage'];
					$unique_devices_array[$row['serial']]['color3_usage'] += (int) $row['colorlevel3_usage'];
					
				}
				
				session(['unique_devices' => $unique_devices_array]); 
 				
				return view('pages.upload_invoice', [
					'access_token' => $access_token,
					'unique_devices' => $unique_devices_array
				]);
			}			
			
		} else { 
			return view('pages.invoices_page', [
				'access_token' => $access_token,
				'devices' => $devices
			]);
		}
    }	
	
    /**
     * Send invoices
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function get_zip_code($street, $city, $state)
    { 
		// Google allows 50 calls per second, therefore we delay execution for 2 milliseconds
		usleep(2000);
		$google_app_key = "AIzaSyBPGS6iUl2MiKoBxythY7CJ-PdlF0SDHCU";
		
		$address_string = '';
		if (!is_null($street)) $address_string .= $street;
		if (!is_null($city)) $address_string .= $city;
		if (!is_null($state)) $address_string .= $state;
		
		$address_url = urlencode($address_string);
		
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => "https://maps.google.com/maps/api/geocode/xml?address=" . $address_url . "&output=xml&key=" . $google_app_key,
			CURLOPT_CUSTOMREQUEST => "GET",
			CURLOPT_RETURNTRANSFER => 1
		));   
		
		$xml_response = curl_exec($curl); 
		$err = curl_error($curl);

		curl_close($curl);
		
		if (!$err) {
			$response_object = simplexml_load_string($xml_response);
			if ($response_object->status == 'OK') {
				if (property_exists($response_object->result, 'address_component')) {
					if (isset($response_object->result->address_component[6])) {
						if (property_exists($response_object->result->address_component[6], 'short_name')) {
							$zip_code = $response_object->result->address_component[6]->short_name;
						} else {
							$zip_code = 'zip_code';
						}
					} else {
						$zip_code = 'zip_code';
					}
				} else {
					$zip_code = 'zip_code';
				}			
			} else {
				$zip_code = 'zip_code';
			}
		} else {
			$zip_code = 'zip_code';
		}
		
		return $zip_code;
	}
	
    /**
     * Send invoices
     * @param  \Illuminate\Http\Request
     * @return \Illuminate\Http\Response
     */
    public function send_invoices(Request $request)
    { 	
		$access_token = session('access_token');
				
		$devices = Device::all();
		
		$unique_devices = session('unique_devices');
		$grouped_rows_to_invoices = array();
		
		$prepared_devices_array = array();
		$actual_usage_for_billing = array();
		
		//Group array by clients before sending
		foreach($unique_devices as $key => $item)
		{
		   $grouped_rows_to_invoices[$item['customer']][$key] = $item;
		}
		
		$messages_array = '';
		$invoice_totals_array = array();
		$invoice_subtotals_array = array();
		
		foreach ($grouped_rows_to_invoices as $client => $rows_to_invoice_array) {
			$invoice_subtotals_array[$client] = array();
			$invoice = array(
				'customer_id' => $client,
				'line_items' => array( 
				),
				'terms' =>
'Thanks for your business! If you are a credit card customer (most everyone) - this is your invoice. If you pay
by check, please cut as soon as possible.
* Invoice is due 30 days from the date of issue
*A 1.50% late fee will be assessed for each 30 day period that this invoice is past due',
				'notes' => ''
			); 
			
			$invoice_totals_array[$client] = 0;
			
			$i = 0;
			$counter = count($rows_to_invoice_array);
			foreach ($rows_to_invoice_array as $serial_number => $row_to_invoice) {
				
				if (!in_array($serial_number, $prepared_devices_array)) {
					$current_device = Device::where('serial', $serial_number)->first();
					$prepared_devices_array[] = $serial_number;
					 
					$total_price_for_current_sale = 0;						
					$invoice_subtotals_array[$client][$row_to_invoice['city']] =
					array_key_exists ( $row_to_invoice['city'] , $invoice_subtotals_array[$client] ) ? $invoice_subtotals_array[$client][$row_to_invoice['city']] : 0;			
					
					$description = $row_to_invoice['make'] . ' ' . $row_to_invoice['model'] . ' ' . $serial_number . ' Monthly Base';
					if (
						(int) $current_device->mono_included > 0 ||
						(int) $current_device->color_included > 0 ||
						(int) $current_device->monocolor_included > 0 ||
						(int) $current_device->color2_included > 0 ||
						(int) $current_device->color3_included > 0
					) {
						$description .= ', includes: ';
					} 
					$description .= (int) $current_device->mono_included > 0 ? $current_device->mono_included . " mono pages, " : "";
					$description .= (int) $current_device->color_included > 0 ? $current_device->color_included . " color pages, " : "";
					$description .= (int) $current_device->monocolor_included > 0 ? $current_device->monocolor_included . " mono/color pages, " : "";
					$description .= (int) $current_device->color2_included > 0 ? $current_device->color2_included . " color2 pages, " : "";
					$description .= (int) $current_device->color3_included > 0 ? $current_device->color3_included . " color3 pages, " : "";
					$invoice['line_items'][] = array (
						'name' => $row_to_invoice['city'],
						'description' => $description,
						'rate' => $current_device->base_price,
						'quantity' => 1
					);
					$total_price_for_current_sale += $current_device->base_price;
					
					end($invoice['line_items']);
					$key = key($invoice['line_items']); 
					
					if ($current_device->tax == 'CO') {
						$invoice['line_items'][$key]['tax_name'] = 'CO';
						$invoice['line_items'][$key]['tax_type'] = 'tax';						
						$invoice['line_items'][$key]['tax_percentage'] = 7.5;
					} else {
						
						$invoice['line_items'][$key]['tax_id'] = '';
						$invoice['line_items'][$key]['tax_exemption_id'] = '663126000000059796';
						$invoice['line_items'][$key]['tax_exemption_code'] = 'NON-TAXABLE';							
						$invoice['line_items'][$key]['tax_name'] = '';						
						$invoice['line_items'][$key]['tax_type'] = '';			
						$invoice['line_items'][$key]['tax_percentage'] = 0;
						
					}
					  
					 
					$actual_usage_for_billing[$serial_number] = array();
					$actual_usage_for_billing[$serial_number]['mono_usage'] =
						$current_device->mono_included > $request->mono_usage["'".$serial_number."'"] ?
						(int) $current_device->mono_included :(int) $request->mono_usage["'".$serial_number."'"] - (int) $current_device->mono_included;
					$actual_usage_for_billing[$serial_number]['color_usage'] =
						$current_device->color_included > $request->color_usage["'".$serial_number."'"] ?
						(int) $current_device->color_included : (int) $request->color_usage["'".$serial_number."'"] - (int) $current_device->color_included;
					$actual_usage_for_billing[$serial_number]['monocolor_usage'] =
						$current_device->monocolor_included > $request->monocolor_usage["'".$serial_number."'"] ?
						(int) $current_device->monocolor_included : (int) $request->monocolor_usage["'".$serial_number."'"] - (int) $current_device->monocolor_included;
					$actual_usage_for_billing[$serial_number]['color2_usage'] =
						$current_device->color2_included > $request->color2_usage["'".$serial_number."'"] ?
						(int) $current_device->color2_included : (int) $request->color2_usage["'".$serial_number."'"] - (int) $current_device->color2_included;
					$actual_usage_for_billing[$serial_number]['color3_usage'] =
						$current_device->color3_included > $request->color3_usage["'".$serial_number."'"] ?
						(int) $current_device->color3_included : (int) $request->color3_usage["'".$serial_number."'"] - (int) $current_device->color3_included;
					
					if ($current_device->mono_price != null && $actual_usage_for_billing[$serial_number]['mono_usage'] != null) {
						
						$description = $row_to_invoice['make'] . ' ' . $row_to_invoice['model'] . ' ' . $serial_number . ' B/W ';
						$description .= (int) $row_to_invoice['mono_end'] > 0 ? '(' . $row_to_invoice['mono_beg'] . ' to ' . $row_to_invoice['mono_end'] . ')' : '';
						$invoice['line_items'][] = array (
							'name' => $row_to_invoice['city'],
							'description' => $description,
							'rate' => $current_device->mono_price,  
							'quantity' => $actual_usage_for_billing[$serial_number]['mono_usage']
						);
					
						end($invoice['line_items']);
						$key = key($invoice['line_items']); 
						
						if ($current_device->tax == 'CO') {
							$invoice['line_items'][$key]['tax_name'] = 'CO';
							$invoice['line_items'][$key]['tax_type'] = 'tax';						
							$invoice['line_items'][$key]['tax_percentage'] = 7.5;
						} else {
							
							$invoice['line_items'][$key]['tax_id'] = '';
							$invoice['line_items'][$key]['tax_exemption_id'] = '663126000000059796';
							$invoice['line_items'][$key]['tax_exemption_code'] = 'NON-TAXABLE';							
							$invoice['line_items'][$key]['tax_name'] = '';						
							$invoice['line_items'][$key]['tax_type'] = '';			
							$invoice['line_items'][$key]['tax_percentage'] = 0;
							
						}

						$total_price_for_current_sale += $current_device->mono_price * $actual_usage_for_billing[$serial_number]['mono_usage'];
					}
					if ($current_device->color_price != null && $actual_usage_for_billing[$serial_number]['color_usage'] != null) {
						$description = $row_to_invoice['make'] . ' ' . $row_to_invoice['model'] . ' ' . $serial_number . ' Color ';
						$description .= (int) $row_to_invoice['color_end'] > 0 ? '(' . $row_to_invoice['color_beg'] . ' to ' . $row_to_invoice['color_end'] . ')' : '';
						
						$invoice['line_items'][] = array (
							'name' => $row_to_invoice['city'],
							'description' => $description,
							'rate' => $current_device->color_price,  
							'quantity' => $actual_usage_for_billing[$serial_number]['color_usage']
						);
						
						end($invoice['line_items']);
						$key = key($invoice['line_items']); 
						
						if ($current_device->tax == 'CO') {
							$invoice['line_items'][$key]['tax_name'] = 'CO';
							$invoice['line_items'][$key]['tax_type'] = 'tax';						
							$invoice['line_items'][$key]['tax_percentage'] = 7.5;
						} else {
							
							$invoice['line_items'][$key]['tax_id'] = '';
							$invoice['line_items'][$key]['tax_exemption_id'] = '663126000000059796';
							$invoice['line_items'][$key]['tax_exemption_code'] = 'NON-TAXABLE';							
							$invoice['line_items'][$key]['tax_name'] = '';						
							$invoice['line_items'][$key]['tax_type'] = '';			
							$invoice['line_items'][$key]['tax_percentage'] = 0;
							
						}
						$total_price_for_current_sale += $current_device->color_price * $actual_usage_for_billing[$serial_number]['color_usage'];
					}		
					if ($current_device->monocolor_price != null && $actual_usage_for_billing[$serial_number]['monocolor_usage'] != null) {		
						$description = $row_to_invoice['make'] . ' ' . $row_to_invoice['model'] . ' ' . $serial_number . ' Mono/Color ';
						$description .= (int) $row_to_invoice['monocolor1_end'] > 0 ? '(' . $row_to_invoice['monocolor1_beg'] . ' to ' . $row_to_invoice['monocolor1_end'] . ')' : '';
						
						$invoice['line_items'][] = array (
							'name' => $row_to_invoice['city'],
							'description' => $description,
							'rate' => $current_device->monocolor_price,  
							'quantity' => $actual_usage_for_billing[$serial_number]['monocolor_usage']
						);
					
						end($invoice['line_items']);
						$key = key($invoice['line_items']); 
						
						if ($current_device->tax == 'CO') {
							$invoice['line_items'][$key]['tax_name'] = 'CO';
							$invoice['line_items'][$key]['tax_type'] = 'tax';						
							$invoice['line_items'][$key]['tax_percentage'] = 7.5;
						} else {
							
							$invoice['line_items'][$key]['tax_id'] = '';
							$invoice['line_items'][$key]['tax_exemption_id'] = '663126000000059796';
							$invoice['line_items'][$key]['tax_exemption_code'] = 'NON-TAXABLE';								
							$invoice['line_items'][$key]['tax_name'] = '';						
							$invoice['line_items'][$key]['tax_type'] = '';			
							$invoice['line_items'][$key]['tax_percentage'] = 0;
							
						}
						
						$total_price_for_current_sale += $current_device->monocolor_price * $actual_usage_for_billing[$serial_number]['monocolor_usage'];
					}
					if ($current_device->color2_price != null && $actual_usage_for_billing[$serial_number]['color2_usage'] != null) {			
						$description = $row_to_invoice['make'] . ' ' . $row_to_invoice['model'] . ' ' . $serial_number . ' Color2 ';
						$description .= (int) $row_to_invoice['colorlevel2_end'] > 0 ? '(' . $row_to_invoice['colorlevel2_beg'] . ' to ' . $row_to_invoice['colorlevel2_end'] . ')' : '';
						
						$invoice['line_items'][] = array (
							'name' => $row_to_invoice['city'],
							'description' => $description,
							'rate' => $current_device->color2_price,  
							'quantity' => $actual_usage_for_billing[$serial_number]['color2_usage']
						);
						
						end($invoice['line_items']);
						$key = key($invoice['line_items']); 
						
						if ($current_device->tax == 'CO') {
							$invoice['line_items'][$key]['tax_name'] = 'CO';
							$invoice['line_items'][$key]['tax_type'] = 'tax';						
							$invoice['line_items'][$key]['tax_percentage'] = 7.5;
						} else {
							
							$invoice['line_items'][$key]['tax_id'] = '';
							$invoice['line_items'][$key]['tax_exemption_id'] = '663126000000059796';
							$invoice['line_items'][$key]['tax_exemption_code'] = 'NON-TAXABLE';								
							$invoice['line_items'][$key]['tax_name'] = '';						
							$invoice['line_items'][$key]['tax_type'] = '';			
							$invoice['line_items'][$key]['tax_percentage'] = 0;
							
						}
						
						$total_price_for_current_sale += $current_device->color2_price * $actual_usage_for_billing[$serial_number]['color2_usage'];
					}
					if ($current_device->color3_price != null && $actual_usage_for_billing[$serial_number]['color3_usage'] != null) {
						$description = $row_to_invoice['make'] . ' ' . $row_to_invoice['model'] . ' ' . $serial_number . ' Color3 ';
						$description .= (int) $row_to_invoice['colorlevel3_end'] > 0 ? '(' . $row_to_invoice['colorlevel3_beg'] . ' to ' . $row_to_invoice['colorlevel3_end'] . ')' : '';
						
						$invoice['line_items'][] = array (
							'name' => $row_to_invoice['city'],
							'description' => $description,
							'rate' => $current_device->color3_price,  
							'quantity' => $actual_usage_for_billing[$serial_number]['color3_usage']
						);
				
						end($invoice['line_items']);
						$key = key($invoice['line_items']); 
						
						if ($current_device->tax == 'CO') {
							$invoice['line_items'][$key]['tax_name'] = 'CO';
							$invoice['line_items'][$key]['tax_type'] = 'tax';						
							$invoice['line_items'][$key]['tax_percentage'] = 7.5;
						} else {
							
							$invoice['line_items'][$key]['tax_id'] = '';
							$invoice['line_items'][$key]['tax_exemption_id'] = '663126000000059796';
							$invoice['line_items'][$key]['tax_exemption_code'] = 'NON-TAXABLE';							
							$invoice['line_items'][$key]['tax_name'] = '';						
							$invoice['line_items'][$key]['tax_type'] = '';			
							$invoice['line_items'][$key]['tax_percentage'] = 0;
							
						}
						
						$total_price_for_current_sale += $current_device->color3_price * $actual_usage_for_billing[$serial_number]['color3_usage'];
					}
					 
					$sale = new Sale;
					  
					$sale->date = date("Y-m-d");
					$sale->cost_amount = $row_to_invoice['device_total'];
					$sale->price_amount = $total_price_for_current_sale;
					$sale->device()->associate($current_device);
					
					$sale->save();
					
					$invoice_totals_array[$client] += $total_price_for_current_sale;				
					
					if ($i == $counter - 1) {
						if ($row_to_invoice['stateprovince'] == 'CO') {
							$invoice_totals_array[$client] = $invoice_totals_array[$client] * 1.075;
						}					
					}
					
					if ($current_device->tax == 'CO') {
						$total_price_for_current_sale = $total_price_for_current_sale * 1.075;
					}					
				
					$invoice_subtotals_array[$client][$row_to_invoice['city']] += $total_price_for_current_sale; 
				}
				$i++;
				
			}	
			
			$invoice['notes'] = 'Subtotals:
			';
			foreach ($invoice_subtotals_array[$client] as $city => $subtotal) {
				
				$invoice['notes'] .= '
' . $city . ': ' . $subtotal;
				
			}
			
			$json_invoice = json_encode($invoice, JSON_PRETTY_PRINT);
			
			$auth_data = array(
				'authtoken' => session('authtoken'),
				'organization_id' => $this->organization_id,
				'JSONString' => $json_invoice
			);
 
			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->create_invoices_url, 
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_POSTFIELDS => http_build_query($auth_data), 
				CURLOPT_RETURNTRANSFER => 1, 
				CURLOPT_HTTPHEADER => array("Content-Type: application/x-www-form-urlencoded") 
			));   
			
			$response = curl_exec($curl); 
			$err = curl_error($curl);

			curl_close($curl);
			
			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {
			
				$decoded_response = json_decode($response); 
				
				if ( property_exists($decoded_response, 'code') && $decoded_response->code == 0 ) {
					
					if ( property_exists($decoded_response, 'invoice') ) {
						$invoice = $decoded_response->invoice;
						$messages_array[$client] = 'Invoice #' . $invoice->invoice_id . ' was created successfully.';		
					}
				
				} else if (property_exists($decoded_response, 'message')) {
					$messages_array[$client] = 'Some error ocurred: ';
					$messages_array[$client] .= $decoded_response->message;
				}
			}
			 
		}
		
		/*
		* Prepating ACH file
		*/
		
		$add_ach_array = $request->add_ach;		  
		
		$txt = '';
		
		foreach ($add_ach_array as $zb_client_id => $add_ach_for_client) { 
			if ($add_ach_for_client == '1') {	
				
				$client_info = $this->get_single_client($zb_client_id);	
				
				/*
				Example: 
				Customer 2,
				CustomerID2,
				5678 Elm St,
				Big City,
				NY,
				67890,
				987-654-3210,
				01/01/2015,
				987654321,
				9876543210,
				Business Checking,
				Debit,
				Monthly,
				12,
				11/20/2015,
				290.00,
				Revoked,
				11/21/2015 
				*/
				
				$address = $client_info->billing_address->street2 != "" ? $client_info->billing_address->street2 : "address";
				$city = $client_info->billing_address->city != "" ? $client_info->billing_address->city : "city";
				$province = $client_info->billing_address->state != "" ? $client_info->billing_address->state : "state_code";
				$zip_code = $client_info->billing_address->zip != "" ? $client_info->billing_address->zip : "zip_code";
				$home_phone = $client_info->billing_address->phone != "" ? $client_info->billing_address->phone : "home_phone";
				$routing_number = $request->routing_no[$zb_client_id] != "" ? $request->routing_no[$zb_client_id] : "routing_number";
				$bank_account = $request->bank_account[$zb_client_id] != "" ? $request->bank_account[$zb_client_id] : "bank_account";
				
				$txt .= $client_info->company_name . ","; 	// Customer Name (Required)
				$txt .= $zb_client_id . ",";				 // Customer ID (NOT required)
				$txt .= $address. ","; 		// Customer Address (Required)
				$txt .= $city . ","; 		// Customer City (Required)
				$txt .= $province . ","; 	// Customer State (Required): Valid 2-character state abbreviation			 
				$txt .= $zip_code . ","; 		// Customer ZIP (Required)
				$txt .= $home_phone . ","; // Customer Phone (Required): xxx-xxx-xxxx
				$txt .= date("m/d/Y") . ","; 		// Authorization Date (Required): mm/dd/yyyy 
				$txt .= $routing_number . ","; 		// Customer Routing Number (Required)
				$txt .= $bank_account . ","; 					// Customer Bank Account (Required)
				$txt .= "Business Checking,";				// Account Type (Required): (Business Checking, Personal Checking, Personal Savings)
				$txt .= "Debit,"; 		// Payment Type (Required): (Debit, Credit)
				$txt .= "Once,"; 		// Frequency (Required): (Once, Weekly, Biweekly, Monthly, Quarterly, Semiannually, Annually. Credits restricted to Once)
				$txt .= "1,"; 		// Number of Payments (Required): 1-9998; Use 9999 for Open-Ended recurring Customer Payments. Credits restricted to 1. 
				$txt .= date("m/d/Y") . ","; 		// Payment Date (Required): mm/dd/yyyy
				$txt .= $invoice_totals_array[$zb_client_id] . ","; 		// Amount Per Payment (Required): A decimal amount greater than 0.00
				$txt .= "Onetime"; 		// Auth Option (Required): (Onetime, Revoked)
				  
				$txt .= "\n";			
			}
		}
		
		$guid = uniqid('', true);
		$guid = str_replace(".", "", $guid);
		$filepath = storage_path() . "/files/";
		$filename = $guid . ".csv";
		
		$myfile = fopen( $filepath . $filename, "w" );		
		fwrite($myfile, $txt);
		fclose($myfile);				
		
		if ($txt != "") {
		
			$ach_log = new AchLog;
			$ach_log->date = date("Y-m-d");
			$ach_log->filename = $filename;					
			$ach_log->save();	
			
		}
		
		return redirect()->route('invoices')
			->with('messages_array', $messages_array)
			->with('filename', $filename)
			->with('txt', $txt); 
 
    }
}
