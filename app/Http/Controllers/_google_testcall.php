<?php

	
	$app_key = "AIzaSyBPGS6iUl2MiKoBxythY7CJ-PdlF0SDHCU";
	
	$address_string = "5678 Elm St, Big City, NY";
	$address_url = urlencode($address_string);
	
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => "https://maps.google.com/maps/api/geocode/xml?address=" . $address_url . "&output=xml&key=" . $app_key,
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
						$zip_code = 'xxxxx';
					}
				} else {
					$zip_code = 'xxxxx';
				}
			} else {
				$zip_code = 'xxxxx';
			}			
		} else {
			$zip_code = 'xxxxx';
		}
	} else {
		$zip_code = 'xxxxx';
	}
	
	echo $zip_code;
	
?>