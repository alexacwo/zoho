<?php

	namespace App\Http\Controllers\Api\Clients;

	use DB;
	
	use App\Client;
	use Illuminate\Http\Request;
	use Illuminate\Http\Response;
	use App\Http\Controllers\Controller; 

	class ClientsApiController extends Controller {

		/**
		 * Save Client Info into database or update it
		 *
		 * @param Request
		 * @return Response
		 */
		public function save_or_update(Request $request, $fb_client_id)
		{			
			$client = Client::where('fb_client_id', $fb_client_id)->first();
			
			if (is_null($client)) {
				$client = new Client;
			}
			
			$client->fb_client_id = $fb_client_id;
			$client->routing_no = $request->client_info['routing_no'];
			$client->bank_account = $request->client_info['bank_account'];
			$client->add_ach = $request->client_info['add_ach'];
			
			$client->save();
			
			return response()->json(array('message' => $client));
		}	

		/**
		 * Save Client Info into database or update it
		 *
		 * @param Request
		 * @return Response
		 */
		public function get_clients_from_zoho(Request $request, $fb_client_id)
		{			
			$client = Client::where('fb_client_id', $fb_client_id)->first();
			
			if (is_null($client)) {
				$client = new Client;
			}
			
			$client->fb_client_id = $fb_client_id;
			$client->routing_no = $request->client_info['routing_no'];
			$client->bank_account = $request->client_info['bank_account'];
			$client->add_ach = $request->client_info['add_ach'];
			
			$client->save();
			
			return response()->json(array('message' => $client));
		}	

	}