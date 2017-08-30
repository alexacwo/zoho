@extends('layouts.app')

@section('content')
    <div class="container" ng-controller="invoicesUploadController">
        <div class="col-sm-12">
            <div class="panel panel-danger">
                <div class="panel-heading">
					Upload invoice
                </div>

                <div class="panel-body"> 
				
					@if (count($unique_devices) > 0)
						<style>
							[ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
							  display: none !important;
							}
						</style>
						<div id="sending_invoices_log" class="animate-show ng-cloak" ng-show="sendingInvoicesInBackground">
							Sending invoices in the background ...<br>
							<div ng-repeat="responseMessage in responseMessagesBlock">
								<br><% responseMessage %>
							</div>
						</div>
						<table class="table table-bordered task-table animate-show" ng-show="!sendingInvoicesInBackground">

							<thead>
								<th>Client<br>(we get it from already saved device)</th>
								<th>City Friendly Name</th> 
								<th>Make</th>
								<th>Model</th>
								<th>Serial</th>
								<th>Contract ID</th>  
								<th>Client Routing No.</th> 
								<th>Client Bank Account</th> 
								<th>Create ACH file</th> 
							</thead>
							<tbody>							
								<form method="POST" action="{{ url('/send_invoices') }}">
									{{ csrf_field() }}
									@foreach ($unique_devices as $serial_number => $unique_device)
										<tr style="background:#888; height:3px;">
											<td style="height:0px; padding:0px;"></td>
											<td style="height:0px; padding:0px;"></td>
											<td style="height:0px; padding:0px;"></td>
											<td style="height:0px; padding:0px;"></td>
											<td style="height:0px; padding:0px;"></td>
											<td style="height:0px; padding:0px;"></td>
											<td style="height:0px; padding:0px;"></td>
											<td style="height:0px; padding:0px;"></td>
											<td style="height:0px; padding:0px;"></td>
										</tr>
										<tr>
											<td class="table-text col-sm-2">
												{{ $unique_device['customer_title'] }}
											</td>
											<td class="table-text col-sm-3">
												@php
													$city = str_replace("'","", $unique_device['city'])
												@endphp
												<input
													ng-model="city_friendly_name['{{ $unique_device['customer']}}']['{{ $city }}']"
													ng-init="city_friendly_name['{{ $unique_device['customer']}}']['{{ $city }}'] = '{{ $city }}'" />
											</td>
											<td class="table-text col-sm-1">
												{{ $unique_device['make'] }}
											</td>
											<td class="table-text col-sm-1">
												{{ $unique_device['model'] }}
											</td>
											<td class="table-text col-sm-1">
												{{ $serial_number }}
											</td>
											<td class="table-text col-sm-1">	
												{{ $unique_device['contract_id'] }}
											</td>
											<td class="table-text col-sm-1">	
												<input
												value="{{ $unique_device['routing_no'] }}"
												disabled
												>
												<input type="hidden" name="routing_no[{{ $unique_device['customer'] }}]"
												value="{{ $unique_device['routing_no'] }}"> 
											</td>
											<td class="table-text col-sm-1">	
												<input
												value="{{ $unique_device['bank_account'] }}"
													disabled
												>
												<input type="hidden" name="bank_account[{{ $unique_device['customer'] }}]"
												value="{{ $unique_device['bank_account'] }}"> 
											</td>
											<td class="table-text col-sm-1">
												<select class="form-control"
													value="{{ $unique_device['add_ach'] }}"
													disabled
												>
													<option value="1"
														@if ( $unique_device['add_ach'] == 1 )
															selected
														@endif
													>
														Yes
													</option>
													<option value="0"
														@if ( $unique_device['add_ach'] == 0 )
															selected
														@endif													
													>
														No
													</option>
												</select>
												<input type="hidden" name="add_ach[{{ $unique_device['customer'] }}]"
													value="{{ $unique_device['add_ach'] }}"> 
											</td>  
										</tr>
										<tr>
											<td class="table-text col-sm-1">&nbsp;</td>
											<td class="table-text col-sm-1">&nbsp;</td>
											<td class="table-text col-sm-1">&nbsp;</td>
											<td class="table-text col-sm-1">&nbsp;</td>
											<td class="table-text col-sm-1"><b>Mono</b></td>
											<td class="table-text col-sm-1"><b>Color</b></td>
											<td class="table-text col-sm-1"><b>Monocolor</b></td>
											<td class="table-text col-sm-1"><b>Color2</b></td>
											<td class="table-text col-sm-1"><b>Color3</b></td>
										</tr>
										<tr>
											<td class="table-text col-sm-1">&nbsp;</td>
											<td class="table-text col-sm-1">&nbsp;</td>
											<td class="table-text col-sm-1">&nbsp;</td>
											<td class="table-text col-sm-1"><b>Usage:</b></td>
											<td class="table-text col-sm-1">	
												<input name="mono_usage['{{ $serial_number }}']"
													ng-model="mono_usage['{{ $serial_number }}']"
													ng-init="mono_usage['{{ $serial_number }}'] = {{ $unique_device['mono_usage'] }}">
											</td> 
											<td class="table-text col-sm-1">	
												<input name="color_usage['{{ $serial_number }}']"
													ng-model="color_usage['{{ $serial_number }}']"
													ng-init="color_usage['{{ $serial_number }}'] = {{ $unique_device['color_usage'] }}">
											</td> 
											<td class="table-text col-sm-1">	
												<input name="monocolor_usage['{{ $serial_number }}']"
													ng-model="monocolor_usage['{{ $serial_number }}']"
													ng-init="monocolor_usage['{{ $serial_number }}'] = {{ $unique_device['monocolor_usage'] }}">
											</td> 
											<td class="table-text col-sm-1">	
												<input name="color2_usage['{{ $serial_number }}']"
													ng-model="color2_usage['{{ $serial_number }}']"
													ng-init="color2_usage['{{ $serial_number }}'] = {{ $unique_device['color2_usage'] }}">
											</td> 
											<td class="table-text col-sm-1">	
												<input name="color3_usage['{{ $serial_number }}']"
													ng-model="color3_usage['{{ $serial_number }}']"
													ng-init="color3_usage['{{ $serial_number }}'] = {{ $unique_device['color3_usage'] }}">
											</td> 
										</tr>
									@endforeach
									<tr>
										<td class="table-text"> 
											<div ng-click="sendInvoices(this)" class="btn btn-danger">
												Send invoices
											</div>
										</td>
										<td class="table-text">&nbsp;</td>
										<td class="table-text">&nbsp;</td>
										<td class="table-text">&nbsp;</td>
										<td class="table-text">&nbsp;</td>
										<td class="table-text">&nbsp;</td>
										<td class="table-text">&nbsp;</td>
										<td class="table-text">&nbsp;</td>
										<td class="table-text">&nbsp;</td>
									</tr>
								</form>
							</tbody>
					@endif
                </div>
            </div>
 
        </div>
    </div>
@endsection