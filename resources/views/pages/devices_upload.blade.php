@extends('layouts.app')

@section('content')
    <div class="container" ng-controller="devicesNewController">
        <div class="col-sm-12">
            <div class="panel panel-success">
                <div class="panel-heading">
					Correct and upload these devices
                </div>

				<style>
					[ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
					  display: none !important;
					}
				</style>
                <div class="panel-body ng-cloak">
					<div class="animate-show initial_hide" ng-show="deviceUploadingError">
						<div class="col-md-6" style="padding-top:0px;">					
							<div class="alert alert-danger" style="margin-bottom:0px;">
								<strong>Some error occured! Try to refresh the page.</strong>
							</div>
						</div>		
					</div>
					<div class="animate-show initial_hide" ng-show="deviceUploadingSuccess">
						<div class="col-md-6" style="padding-top:0px;">					
							<div class="alert alert-success" style="margin-bottom:0px;">
								<strong>Device was saved to the database successfully.</strong>
							</div>
						</div>		
					</div>
					
					<img class="loading_spinner animate-show" src="{{url('/public/img/loading_spinner.gif')}}" ng-show="showLoadingSpinner" />
					
					@if (count($devices_to_upload) > 0)
						<table class="table table-striped animate-show" ng-show="!showLoadingSpinner">
					
							<thead>
								<th>Sales Rep</th>
								<th>Client</th>
								<th>Make</th>
								<th>Model</th>
								<th>Serial</th>
								<th>Base</th>
								<th>State (for tax)</th>
								<th>Mono cost</th>
								<th>Color cost</th>
								<th>Mono/color cost</th>
								<th>Color2 cost</th>
								<th>Color3 cost</th>
								<th>&nbsp;</th>
							</thead>
							<tbody>  
								@foreach ($devices_to_upload as $device)
									<tr
										ng-init="showDevicesUploadRow[{{ $loop->index }}] = true"
										ng-show="showDevicesUploadRow[{{ $loop->index }}]"
										class="devices_upload_row animate-show"
									>									
										<td class="table-text col-sm-2"> 
											<select class="form-control"
												ng-model="devices[{{ $loop->index }}].sales_rep"
												ng-init="devices[{{ $loop->index }}].sales_rep='jesse'">
												<option value="jesse">
													Jesse
												</option>
												<option value="laine">
													Laine
												</option>
												<option value="greg">
													Greg
												</option>
											</select>
										</td>
										<td class="table-text col-sm-4">	  
											<select class="form-control"
												ng-model="devices[{{ $loop->index }}].fb_client_id"   
												ng-options="client.contact_id as client.contact_name for client in zohobooksClients"
											>
												<option value="false"></option>
											</select> 
										</td>
										<td class="table-text col-sm-1">									
											<input type="text" class="form-control"
												ng-model="devices[{{ $loop->index }}].make"
												ng-init="devices[{{ $loop->index }}].make='{{ $device['make'] }}'">
										</td>
										<td class="table-text col-sm-2">									
											<input type="text" class="form-control"
												ng-model="devices[{{ $loop->index }}].model"
												ng-init="devices[{{ $loop->index }}].model='{{ $device['model'] }}'">
										</td>
										<td class="table-text col-sm-2">										
											<input type="text" class="form-control"
												ng-model="devices[{{ $loop->index }}].serial"
												ng-init="devices[{{ $loop->index }}].serial='{{ $device['serial'] }}'">											
										</td>
										<td class="table-text col-sm-1">
											<input type="text" class="form-control"
												ng-model="devices[{{ $loop->index }}].base_price"
												ng-init="devices[{{ $loop->index }}].base_price = 0">
										</td>
										<td class="table-text col-sm-1">
											<input type="text" class="form-control"
												ng-model="devices[{{ $loop->index }}].tax"
												ng-init="devices[{{ $loop->index }}].tax = '{{ $device['stateprovince'] }}'">
											<br><br><b>Incl.pages:</b>
										</td>
										<td class="table-text col-sm-1">
											<input type="text" class="form-control"
												ng-model="devices[{{ $loop->index }}].mono_price"
												ng-init="devices[{{ $loop->index }}].mono_price = 0">
											<br><br>
											<input type="text" class="form-control"
												ng-model="devices[{{ $loop->index }}].mono_included"
												ng-init="devices[{{ $loop->index }}].mono_included = 0">
										</td>
										<td class="table-text col-sm-1">
											<input type="text" class="form-control"
												ng-model="devices[{{ $loop->index }}].color_price"
												ng-init="devices[{{ $loop->index }}].color_price = 0">
											<br><br>
											<input type="text" class="form-control"
												ng-model="devices[{{ $loop->index }}].color_included"
												ng-init="devices[{{ $loop->index }}].color_included = 0">
										</td>
										<td class="table-text col-sm-1">
											<input type="text" class="form-control"
												ng-model="devices[{{ $loop->index }}].monocolor_price"
												ng-init="devices[{{ $loop->index }}].monocolor_price = 0">
											<br><br>
											<input type="text" class="form-control"
												ng-model="devices[{{ $loop->index }}].monocolor_included"
												ng-init="devices[{{ $loop->index }}].monocolor_included = 0">
										</td>
										<td class="table-text col-sm-1">
											<input type="text" class="form-control"
												ng-model="devices[{{ $loop->index }}].color2_price"
												ng-init="devices[{{ $loop->index }}].color2_price = 0">
											<br><br>
											<input type="text" class="form-control"
												ng-model="devices[{{ $loop->index }}].color2_included"
												ng-init="devices[{{ $loop->index }}].color2_included = 0">
										</td>
										<td class="table-text col-sm-1">
											<input type="text" class="form-control"
												ng-model="devices[{{ $loop->index }}].color3_price"
												ng-init="devices[{{ $loop->index }}].color3_price = 0">
											<br><br>
											<input type="text" class="form-control"
												ng-model="devices[{{ $loop->index }}].color3_included"
												ng-init="devices[{{ $loop->index }}].color3_included = 0">
										</td>
										<td class="table-text col-sm-1">																			
											<input type="hidden" class="form-control"
												ng-model="devices[{{ $loop->index }}].contract_id"
												ng-init="devices[{{ $loop->index }}].contract_id='{{ $device['contract_id'] }}'">
											<button type="button" class="btn btn-primary" ng-click="saveDeviceToDatabase({{ $loop->index }})">
												Save device
											</button>
										</td>
									</tr>
								@endforeach
							</tbody>
						</table>
					@endif
					<div class="animate-show initial_hide" ng-show="showMessageAfterAllDevicesAreSaved">
						<br>
						Now you can
						<a href="{{ url('/invoices') }}">
							go back and upload an invoice or correct the devices information.
						</a>
						<br>
						
					</div>
                </div>
            </div>
 
        </div>
    </div>
@endsection