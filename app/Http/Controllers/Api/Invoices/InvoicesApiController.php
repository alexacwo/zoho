<?php

namespace App\Http\Controllers\Api\Invoices;

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

class InvoicesApiController extends Controller
{	
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
		
		$this->create_invoices_url = "https://books.zoho.com/api/v3/invoices?";
		
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
		
		$total_number_of_invoices = count($grouped_rows_to_invoices);
		
		$continue_flag = 'true';	
		
		for($batch_index_start = (int) $request->batch_index; $batch_index_start <= (int) $request->batch_index + 3; $batch_index_start++) {
			 			
			if ($batch_index_start < $total_number_of_invoices) {
							
				$array_values = array_values($grouped_rows_to_invoices);
				$array_keys = array_keys($grouped_rows_to_invoices);
				
				$client = $array_keys[$batch_index_start];
				
				$rows_to_invoice_array = $array_values[$batch_index_start];
				
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
				 
		
		
	
				//$i = 0;
				//$counter = count($rows_to_invoice_array);
				
				foreach ($rows_to_invoice_array as $serial_number => $row_to_invoice) {
					
					//if (!in_array($serial_number, $prepared_devices_array)) {
						$current_device = Device::where('serial', $serial_number)->first();
					//	$prepared_devices_array[] = $serial_number;
						 
						$total_price_for_current_sale = 0;	
						
						$city_name = $row_to_invoice['city']; 
						$city = $request->sent_data['city_friendly_name'][$client][$city_name];	
						
						$invoice_subtotals_array[$client][$city] =
							array_key_exists ( $city , $invoice_subtotals_array[$client] ) ?
							$invoice_subtotals_array[$client][$city] : 0;			
						
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
						
						$invoice['line_items'][] = 
						array (
							'name' => $city,
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
							$current_device->mono_included >= $request->sent_data['mono_usage'][$serial_number] ?
							0 :(int) $request->sent_data['mono_usage'][$serial_number] - (int) $current_device->mono_included;						
						$actual_usage_for_billing[$serial_number]['color_usage'] =
							$current_device->color_included >= $request->sent_data['color_usage'][$serial_number] ?
							0 : (int) $request->sent_data['color_usage'][$serial_number] - (int) $current_device->color_included;
						$actual_usage_for_billing[$serial_number]['monocolor_usage'] =
							$current_device->monocolor_included >= $request->sent_data['monocolor_usage'][$serial_number] ?
							0 : (int) $request->sent_data['monocolor_usage'][$serial_number] - (int) $current_device->monocolor_included;
						$actual_usage_for_billing[$serial_number]['color2_usage'] =
							$current_device->color2_included >= $request->sent_data['color2_usage'][$serial_number] ?
							0 : (int) $request->sent_data['color2_usage'][$serial_number] - (int) $current_device->color2_included;
						$actual_usage_for_billing[$serial_number]['color3_usage'] =
							$current_device->color3_included >= $request->sent_data['color3_usage'][$serial_number] ?
							0 : (int) $request->sent_data['color3_usage'][$serial_number] - (int) $current_device->color3_included;
						
						if ($current_device->mono_price != null && $actual_usage_for_billing[$serial_number]['mono_usage'] != null) {
							
							$description = $row_to_invoice['make'] . ' ' . $row_to_invoice['model'] . ' ' . $serial_number . ' B/W ';
							$description .= (int) $row_to_invoice['mono_end'] > 0 ? '(' . $row_to_invoice['mono_beg'] . ' to ' . $row_to_invoice['mono_end'] . ')' : '';
							$invoice['line_items'][] = array (
								'name' => $city,
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
								'name' => $city,
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
								'name' => $city,
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
								'name' => $city,
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
								'name' => $city,
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
						
						
						//if ($i == $counter - 1) {
						//	if ($row_to_invoice['stateprovince'] == 'CO') {
						//		$invoice_totals_array[$client] = $invoice_totals_array[$client] * 1.075;
						//	}					
						//}
						
						if ($current_device->tax == 'CO') {
							$total_price_for_current_sale = $total_price_for_current_sale * 1.075;
						}	
						
						$invoice_totals_array[$client] += $total_price_for_current_sale;				
												
						$invoice_subtotals_array[$client][$city] += round($total_price_for_current_sale,2); 
												 
						$sale = new Sale;
						  
						$sale->date = date("Y-m-d");
						$sale->cost_amount = $row_to_invoice['device_total'];
						$sale->price_amount = $total_price_for_current_sale;
						$sale->device()->associate($current_device);
						
						$sale->save();	
						
					}
									
					
					
						
						//$i++;
					
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
									$invoice_res = $decoded_response->invoice;
									$messages_array[$client] = 'Invoice #' . $invoice_res->invoice_id . ' was created successfully.';		
								} else {
									$messages_array[$client] = 'Some error ocurred: ';
									$messages_array[$client] .= $decoded_response->message;
								}
							
							} else if (property_exists($decoded_response, 'message')) {
								$messages_array[$client] = 'Some error ocurred: ';
								$messages_array[$client] .= $decoded_response->message;
							}
						}
					//} 
							
			} else {
				$continue_flag = 'false';
				continue;
			}
		}	
		 
		
		/*
		* Prepating ACH file
		*/
		
		//$add_ach_array = $request->add_ach;		  
		
		$txt = '';
		
		foreach ($unique_devices as $zb_client_id => $unique_device) { 
			if ($unique_device['add_ach'] == '1') {	
				
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
				$routing_number = $unique_device['routing_no'] != "" ? $unique_device['routing_no'] : "routing_number";
				$bank_account = $unique_device['bank_account'] != "" ? $unique_device['bank_account'] : "bank_account";
				
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
		
		$response_message = array(
			'messages_array' => $messages_array,
			'filename' => $filename,
			'continue_flag' => $continue_flag
		);
		
		echo json_encode($response_message);
		
		/*return redirect()->route('invoices')
			->with('messages_array', $messages_array)
			->with('filename', $filename)
			->with('txt', $txt); */
 
    }
}
