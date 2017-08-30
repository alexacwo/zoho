<?php

	namespace App\Http\Controllers\Api\Device;

	use DB;

	use App\Device;
	use App\Sale;
	use Illuminate\Http\Request;
	use Illuminate\Http\Response;
	use App\Http\Controllers\Controller; 

	class DeviceApiController extends Controller {

		/**
		 * Send information about quotes as JSON
		 *
		 * @param Request
		 * @return Response
		 */
		public function index(Request $request)
		{
			return response()->json(Device::find(1));
		}

		/**
		 * Store a newly created resource in storage.
		 *
		 * @param Request
		 * @return Response
		 */
		public function store(Request $request)
		{ 
			$device = new Device;
			  
			$device->sale_id = 0;
			
			$device->fb_client_id = $request->device['customer'];
			
			$device->make = $request->device['make'];
			$device->model = $request->device['model'];
			$device->serial = $request->device['serial'];
			$device->sales_rep = $request->device['sales_rep'];
			$device->tax = $request->device['tax'];		
			
			$device->base_price =
			($request->device['base_price'] != '' && is_numeric($request->device['base_price'])) ? floatval($request->device['base_price']) : 0;			
			
			$device->mono_price =
			($request->device['mono_price'] != '' && is_numeric($request->device['mono_price'])) ? floatval($request->device['mono_price']) : 0;		
			$device->color_price =
			($request->device['color_price'] != '' && is_numeric($request->device['color_price'])) ? floatval($request->device['color_price']) : 0;	
			$device->monocolor_price =
			($request->device['monocolor_price'] != '' && is_numeric($request->device['monocolor_price'])) ? floatval($request->device['monocolor_price']) : 0;		
			$device->color2_price =
			($request->device['color2_price'] != '' && is_numeric($request->device['color2_price'])) ? floatval($request->device['color2_price']) : 0;		
			$device->color3_price =
			($request->device['color3_price'] != '' && is_numeric($request->device['color3_price'])) ? floatval($request->device['color3_price']) : 0;	
			
			$device->mono_included =
			($request->device['mono_included'] != '' && is_numeric($request->device['mono_included'])) ? floatval($request->device['mono_included']) : 0;	
			$device->color_included =
			($request->device['color_included'] != '' && is_numeric($request->device['color_included'])) ? floatval($request->device['color_included']) : 0;		
			$device->monocolor_included =
			($request->device['monocolor_included'] != '' && is_numeric($request->device['monocolor_included'])) ? floatval($request->device['monocolor_included']) : 0;			
			$device->color2_included =
			($request->device['color2_included'] != '' && is_numeric($request->device['color2_included'])) ? floatval($request->device['color2_included']) : 0;		
			$device->color3_included =
			($request->device['color3_included'] != '' && is_numeric($request->device['color3_included'])) ? floatval($request->device['color3_included']) : 0;	
			
			$device->price_total = 1;
			$device->city = '1';
			$device->state = '1';
			
			$device->save();
			 
			return response()->json(
				array(
				'message' => $device
				
				));
		}		
		
		/**
		 * Update the specified device
		 * @param Request $request
		 *
		 * @return Response
		 */
		public function update(Request $request, $serial_number)
		{
			$device = Device::where('serial', $serial_number)->first();
			
			$device->make = $request->device['make'];
			$device->model = $request->device['model'];
			$device->sales_rep = $request->device['sales_rep'];
			$device->fb_client_id = $request->device['fb_client_id'];			
			$device->tax = $request->device['tax'];	
			
			$device->base_price =
			($request->device['base_price'] != '' && is_numeric($request->device['base_price'])) ? floatval($request->device['base_price']) : 0;			
			
			$device->mono_price =
			($request->device['mono_price'] != '' && is_numeric($request->device['mono_price'])) ? floatval($request->device['mono_price']) : 0;		
			$device->color_price =
			($request->device['color_price'] != '' && is_numeric($request->device['color_price'])) ? floatval($request->device['color_price']) : 0;	
			$device->monocolor_price =
			($request->device['monocolor_price'] != '' && is_numeric($request->device['monocolor_price'])) ? floatval($request->device['monocolor_price']) : 0;		
			$device->color2_price =
			($request->device['color2_price'] != '' && is_numeric($request->device['color2_price'])) ? floatval($request->device['color2_price']) : 0;		
			$device->color3_price =
			($request->device['color3_price'] != '' && is_numeric($request->device['color3_price'])) ? floatval($request->device['color3_price']) : 0;	
			
			$device->mono_included =
			($request->device['mono_included'] != '' && is_numeric($request->device['mono_included'])) ? floatval($request->device['mono_included']) : 0;	
			$device->color_included =
			($request->device['color_included'] != '' && is_numeric($request->device['color_included'])) ? floatval($request->device['color_included']) : 0;		
			$device->monocolor_included =
			($request->device['monocolor_included'] != '' && is_numeric($request->device['monocolor_included'])) ? floatval($request->device['monocolor_included']) : 0;			
			$device->color2_included =
			($request->device['color2_included'] != '' && is_numeric($request->device['color2_included'])) ? floatval($request->device['color2_included']) : 0;		
			$device->color3_included =
			($request->device['color3_included'] != '' && is_numeric($request->device['color3_included'])) ? floatval($request->device['color3_included']) : 0;	
			
			$device->save();

			return response()->json(array('message' => 'success'));
		}
				
		/**
		 * Delete device from the database
		 * @param $serial_number
		 *
		 * @return Response
		 */
		public function destroy ($serial_number)
		{
			$device = Device::where('serial', $serial_number)->first();
			$device->delete();

			return response()->json(array('message' => 'success'));
		}
	}