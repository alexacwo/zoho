@extends('layouts.app')

@section('content')
    <div class="container" ng-controller="deviceUploadController">
        <div class="col-sm-12"> 
			
			@if (isset($devices) && count($devices) > 0)
				<div class="panel panel-success" ng-init="devices = {{$devices}}">
					<div class="panel-heading">
						Edit devices
					</div>

					<style>
						[ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
						  display: none !important;
						}
					</style>

					<div class="panel-body ng-cloak">		
						
						<div class="animate-show" ng-show="deviceUploadingError">
							<div class="col-md-6" style="padding-top:0px;">					
								<div class="alert alert-danger">
									<strong>Some error occured! Try to refresh the page.</strong>
								</div>
							</div>		
						</div>
						<div class="animate-show" ng-show="deviceUploadingSuccess">
							<div class="col-md-6" style="padding-top:0px;">					
								<div class="alert alert-success">
									<strong>Device was updated in the database successfully.</strong>
								</div>
							</div>		
						</div>
						
						<div class="col-sm-12">
							
							<div class="col-md-6" style="padding-top:0px;">	
								To improve site performance the client list is only loaded once from ZOHO. To update the client list (in case it is changed in ZOHO Books) press this button, wait for the response message, then refresh the page:
								<br>
								<button type="button" class="btn btn-primary has-spinner"
									ng-class="{'active': buttonLoadingSpinner}"
									ng-click="updateClientList()"
								>
									<span class="spinner"><i class="icon-spin icon-refresh"></i></span>
									Update
								</button>
							</div>
							
							<div class="animate-show col-md-6" ng-show="zohoClientListUpdateSuccess" style="padding-top:0px;">
								<div class="alert alert-success">
									<strong>Client list updated successfully.</strong>
								</div>	
							</div>		
						</div>
							
						<div class="animate-show" ng-show="!showLoadingSpinner">
							<div class="col-sm-12">
								<h4><b>Search:</b></h4>
							</div>
							
							<div class="col-sm-4">							
								By sales rep:<br>
								<select class="form-control"
									ng-model="filterDevicesBySalesRep"
									ng-init="filterDevicesBySalesRep = ''"
								>
									<option value="">
										All
									</option>
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
							</div>
							
							<div class="col-sm-4">							
								By sales rep:<br>
								<select class="form-control"
									ng-model="filterDevicesByClient"   
									ng-init="filterDevicesByClient = null" 
									ng-options="client.contact_id as client.contact_name for client in zohobooksClients"
								>
									<option value="">All</option>
								</select>
							</div>
							
							<div class="col-sm-4">							
								By serial number:<br>
								<input type="text" class="form-control"
									ng-model="filterDevicesBySerialNumber"
									ng-init="filterDevicesBySerialNumber"
									ng-init="filterDevicesBySerialNumber = ''"
								>	
							</div> 
						</div> 
						
						<div class="col-sm-12 animate-show" ng-show="!showLoadingSpinner"><!--  -->
							<ul uib-pagination
										total-items="filteredDevices.length"
										items-per-page="devicesNumPerPage"
										ng-model="devicesCurrentPage"
										max-size="devicesMaxSize"
										class="pagination-sm"
										boundary-links="true"></ul>
						</div> 		
				
						<div class="col-md-12" style="padding:0px;">
							<style>								
								.loading_spinner {
									display:block;
									margin:auto;
								}
							</style>
							
							<img class="loading_spinner animate-show" src="{{url('/public/img/loading_spinner.gif')}}" ng-show="showLoadingSpinner" /><!-- showLoadingSpinner -->
						
							<table class="table table-striped task-table animate-show" ng-show="!showLoadingSpinner"> <!-- !showLoadingSpinner -->

								<!-- Table Headings -->
								<thead>
									<th>Sales Rep</th>
									<th>Client</th>
									<th>Make</th>
									<th>Model</th>
									<th>Serial</th>
									<th>Base</th>
									<th>Tax</th>
									<th>Mono cost</th>
									<th>Color cost</th>
									<th>Mono/color cost</th>
									<th>Color2 cost</th>
									<th>Color3 cost</th>
									<th>&nbsp;</th>
									<th>&nbsp;</th>
								</thead>
								<tbody>   
									<tr class="devices_upload_row animate-show" ng-hide="hideDeletedDevice[$index]"
										ng-repeat="device in pagedDevices"
									>
										<td class="table-text col-sm-2">
											<select class="form-control"
												ng-model="pagedDevices[$index].sales_rep"
												ng-init="pagedDevices[$index].sales_rep">
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
										<td class="table-text col-sm-2">	  
											<select class="form-control"
												ng-model="pagedDevices[$index].fb_client_id"   
												ng-options="client.contact_id as client.contact_name for client in zohobooksClients"
											>
												<option value="false"></option>
											</select> 
										</td>
										<td class="table-text col-sm-1">									
											<input type="text" class="form-control"
												ng-model="pagedDevices[$index].make"
												ng-init="pagedDevices[$index].make">
										</td>
										<td class="table-text col-sm-1">									
											<input type="text" class="form-control"
												ng-model="pagedDevices[$index].model"
												ng-init="pagedDevices[$index].model">
										</td>
										<td class="table-text col-sm-5">										
											<input disabled type="text" class="form-control"
												ng-model="pagedDevices[$index].serial"
												ng-init="pagedDevices[$index].serial">
										</td>
										<td class="table-text col-sm-1">
											<input type="text" class="form-control"
												ng-model="pagedDevices[$index].base_price"
												ng-init="pagedDevices[$index].base_price">
										</td>
										<td class="table-text col-sm-1">
											<input type="text" class="form-control"
												ng-model="pagedDevices[$index].tax"
												ng-init="pagedDevices[$index].tax">
											<br><br><b>Incl. prints:</b>
										</td>
										<td class="table-text col-sm-1">
											<input type="text" class="form-control"
												ng-model="pagedDevices[$index].mono_price"
												ng-init="pagedDevices[$index].mono_price">
											<br><br>
											<input type="text" class="form-control"
												ng-model="pagedDevices[$index].mono_included"
												ng-init="pagedDevices[$index].mono_included">
										</td>
										<td class="table-text col-sm-1">
											<input type="text" class="form-control"
												ng-model="pagedDevices[$index].color_price"
												ng-init="pagedDevices[$index].color_price">
											<br><br>
											<input type="text" class="form-control"
												ng-model="pagedDevices[$index].color_included"
												ng-init="pagedDevices[$index].color_included">
										</td>
										<td class="table-text col-sm-1">
											<input type="text" class="form-control"
												ng-model="pagedDevices[$index].monocolor_price"
												ng-init="pagedDevices[$index].monocolor_price">
											<br><br>
											<input type="text" class="form-control"
												ng-model="pagedDevices[$index].monocolor_included"
												ng-init="pagedDevices[$index].monocolor_included">
										</td>
										<td class="table-text col-sm-1">
											<input type="text" class="form-control"
												ng-model="pagedDevices[$index].color2_price"
												ng-init="pagedDevices[$index].color2_price">
											<br><br>
											<input type="text" class="form-control"
												ng-model="pagedDevices[$index].color2_included"
												ng-init="pagedDevices[$index].color2_included">
										</td>
										<td class="table-text col-sm-1">
											<input type="text" class="form-control"
												ng-model="pagedDevices[$index].color3_price"
												ng-init="pagedDevices[$index].color3_price">
											<br><br>
											<input type="text" class="form-control"
												ng-model="pagedDevices[$index].color3_included"
												ng-init="pagedDevices[$index].color3_included">
										</td>
										<td class="table-text col-sm-1">														
											<button type="button" class="btn btn-primary" ng-click="updateDeviceInDatabase(pagedDevices[$index], pagedDevices[$index].serial)">
												Save
											</button>
											<br><br><br>																		
											<button type="button" class="btn btn-danger" ng-click="deleteDeviceFromDatabase($index, pagedDevices[$index].serial)">
												Delete
											</button>
										</td>  
									</tr> 
								</tbody>
							</table>	
							
						</div>	
						
						<div class="col-sm-12 animate-show" ng-show="!showLoadingSpinner"> <!-- !showLoadingSpinner -->
							<ul uib-pagination
										total-items="filteredDevices.length"
										items-per-page="devicesNumPerPage"
										ng-model="devicesCurrentPage"
										max-size="devicesMaxSize"
										class="pagination-sm"
										boundary-links="true"></ul>
						</div>							
					</div>
				</div>
			@else
				<br>
				There are no devices in the database.
			@endif
 
        </div>
    </div>
@endsection