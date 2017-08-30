@extends('layouts.app')

@section('content')
    <div class="container sale_page" ng-controller="achFilesController">
        <div class="col-sm-12">
			
            <div class="panel panel-info">
                <div class="panel-heading">
					ACH Client Information
                </div>

				<style>
					[ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
					  display: none !important;
					}
				</style>

				<div class="panel-body ng-cloak">				
					
					<div class="animate-show" ng-show="clientUploadingError">
						<div class="col-md-6" style="padding-top:0px;">					
							<div class="alert alert-danger">
								<strong>Some error occured! Try to refresh the page.</strong>
							</div>
						</div>		
					</div>
					<div class="animate-show" ng-show="clientUploadingSuccess">
						<div class="col-md-6" style="padding-top:0px;">					
							<div class="alert alert-success">
								<strong>Client info was updated in the database successfully.</strong>
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
							
					<img class="loading_spinner animate-show" src="{{url('/public/img/loading_spinner.gif')}}" ng-show="showLoadingSpinner" /> 
					
					<table class="table table-striped task-table animate-show"
						ng-show="!showLoadingSpinner"
						ng-init="clients = {{ $clients }}">

						<!-- Table Headings -->
						<thead>
							<th>Client</th>
							<th>Client Routing No.</th>
							<th>Client Bank Account</th>
							<th>Create ACH File</th>
							<th>&nbsp;</th>
						</thead>
						<tbody>   
							<tr>
								<td class="table-text col-sm-2"> 
									<select class="form-control"
										ng-model="zohobooksClientId"   
										ng-options="client.contact_id as client.contact_name for client in zohobooksClients"
									>
										<option value="false"></option>
									</select> 
								</td>
								<td class="table-text col-sm-1">				
									<input type="text" class="form-control"
										ng-model="clients[zohobooksClientId].routing_no"
										ng-disabled="zohobooksClientId == null">
								</td>
								<td class="table-text col-sm-1">									
									<input type="text" class="form-control"
										ng-model="clients[zohobooksClientId].bank_account"
										ng-disabled="zohobooksClientId == null">
								</td>
								<td class="table-text col-sm-1">	
									<select class="form-control"
										ng-model="clients[zohobooksClientId].add_ach"
										ng-options="option.value as option.name for option in achIncludeOptions"
										ng-disabled="zohobooksClientId == null">
											<option value="" ng-if="false"></option>
									</select>									
								</td> 
								<td class="table-text col-sm-1">						
									<button type="button" class="btn btn-primary"
										ng-click="updateClientInDatabase(zohobooksClientId)"
										ng-disabled="zohobooksClientId == null">
										Save client info
									</button>
								</td> 
							</tr> 
						</tbody>
					</table>	
					
				</div>
			</div>
			
            <div class="panel panel-info">
                <div class="panel-heading">
					ACH Files
                </div>

				<style>
					[ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
					  display: none !important;
					}
				</style>

				<div class="panel-body ng-cloak">	
					 
					@if (count($ach_files) > 0)
						
						<div ng-init="achFiles = {{$ach_files}}" ng-show="!showLoadingSpinner">
							<div class="col-sm-12">
								<ul uib-pagination
											total-items="achFiles.length"
											items-per-page="achFilesNumPerPage"
											ng-model="achFilesCurrentPage"
											max-size="achFilesMaxSize"
											class="pagination-sm"
											boundary-links="true"></ul>
							</div>
										
							<table class="table table-striped task-table">
							
								<thead>
									<th>Date</th>
									<th>ACH File Link</th>
								</thead>
								<tbody>
								 
									<tr
										ng-repeat="achFile in filteredAchFiles"
									>
										<td class="table-text col-sm-2">									
											<% achFile.date %>	 
										</td>
										<td class="table-text col-sm-2">										
											<a href="{{ url('/storage/files') }}/<% achFile.filename %>"> 
												<% achFile.filename %>
											</a>
										</td>
									</tr> 
									
								</tbody>
							</table>
							
							<div class="col-sm-12">
								<ul uib-pagination
											total-items="ach_files.length"
											items-per-page="achFilesNumPerPage"
											ng-model="achFilesCurrentPage"
											max-size="achFilesMaxSize"
											class="pagination-sm"
											boundary-links="true"></ul>
							</div> 
						</div> 
					@else 
						No ACH Files yet.
					@endif
				</div>
			</div>
			
        </div>
    </div>
@endsection