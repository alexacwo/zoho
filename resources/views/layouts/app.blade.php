<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Pahoda Image Products</title>

    <!-- Fonts -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css" rel='stylesheet' type='text/css'>
    <link href="https://fonts.googleapis.com/css?family=Lato:100,300,400,700" rel='stylesheet' type='text/css'>

    <!-- Styles -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet"> 
		
    <link href="{{ url('/public/css/font-awesome.css') }}" rel="stylesheet"> 
    <link href="{{ url('/public/css/app.css') }}" rel="stylesheet"> 

    <style>
        body {
            font-family: 'Lato';
        }
        .fa-btn {
            margin-right: 6px;
        }
    </style>
</head>
<body id="app-layout" ng-app="deviceApp">
    <nav class="navbar navbar-default">
        <div class="container">
            <div class="navbar-header">
			
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#app-navbar-collapse">
                    <span class="sr-only">Toggle Navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>

                <a class="navbar-brand" href="{{ url('/') }}">
                    Pahoda Image Products
                </a>
            </div>

            <div class="collapse navbar-collapse" id="app-navbar-collapse">
                <!-- Left Side Of Navbar -->
                <ul class="nav navbar-nav">
                </ul>

                <!-- Right Side Of Navbar -->
                <ul class="nav navbar-nav navbar-right">
					@if (!session('authtoken'))
                        <li>
							<a href="{{ url('/login_page') }}">
								Login
							</a>
						</li>
                    @elseif (!session('login_content'))
						<li>
							<a href="{{ url('/ach_info') }}">
								ACH Info
							</a>
                        </li>
						<li>
							<a href="{{ url('/sales') }}">
								Sales
							</a>
                        </li>
						<li>
							<a href="{{ url('/invoices') }}">
								Invoices
							</a>
                        </li>
						<li>
							<a href="{{ url('/devices') }}">
								Devices
							</a>
                        </li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
								Admin <span class="caret"></span>
                            </a>

                            <ul class="dropdown-menu" role="menu">
                                <li>
									<a href="{{ url('/logout') }}">
										<i class="fa fa-btn fa-sign-out"></i>
										Logout
									</a>
								</li>
                            </ul>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
	
	<div>
		@if (session('authtoken'))
			@yield('content')
		@endif 
		@if (session('login_content'))
			@yield('login_content')
		@endif 
	</div>

    <!-- JavaScripts -->
    <script src="{{ url('/public/js/jquery.min.js') }}"></script>
    <script src="{{ url('/public/js/angular.min.js') }}"></script>
    <script src="{{ url('/public/js/angular-animate.min.js') }}"></script>
    <script src="{{ url('/public/js/bootstrap.min.js') }}"></script>
	
	<script src="{{asset('public/js/ui-bootstrap-tpls-2.3.0.js')}}"></script> 
	
    <script src="{{ url('/public/js/app.js') }}"></script>
    <script src="{{ url('/public/js/angular_script.js') }}"></script>
	<script>
		(function() {
			'use strict';
			
				var app = angular.module('deviceApp',
					[
						'ngAnimate',
						'deviceUploadCtrl',
						'devicesNewCtrl',
						'invoicesUploadCtrl',
						'salesCtrl',
						'achFilesCtrl',
						'deviceService',
						'clientsService',
						'zohobooksService',
						'invoiceService',
						'ui.bootstrap'
					],
				function($interpolateProvider) {
					$interpolateProvider.startSymbol('<%');
					$interpolateProvider.endSymbol('%>');
				});
				
				app
				.filter('customFilterByProfitAmount', function() {
					return function(input, param) { 
						var out = [];
						 	
						for (var i = 0; i < input.length; i++) {
							switch (param) {
								case '25': 
									if (input[i].price_amount/input[i].cost_amount >= 1.25) out.push(input[i]);
									break;
								case '10': 
									if (input[i].price_amount/input[i].cost_amount > 1.1 && input[i].price_amount/input[i].cost_amount < 1.25) out.push(input[i]);
									break;
								case '5': 
									if (input[i].price_amount/input[i].cost_amount <= 1.1) out.push(input[i]);
									break;
								case 'undefined':
								default:
									return input;
									break;
							} 
						}  
						return out;
					};
				})
				
				/*app
				.filter('filterDevices', function () {
					return function (input, devices, params) {
						console.log(devices);
						if (
							typeof params != 'undefined'
							)
						{		
							var out = [];					
							
							for (var i = 0; i < devices.length; i++) {
								if (
									devices[i].sales_rep == params.bySalesRep 
								) out.push(devices[i]);
							} 
							
							console.log(1);
							return out;
						} else {
							console.log(0);
							return input;
						}
					};
				});*/
				
				angular
				.module('deviceService', [])
				.factory('Device', function($http) {
					return {  
						save : function(deviceParams) {
							return $http({
								method: 'POST',
								url: 'api/devices',
								data:  {
									device: deviceParams
								}
								
							}) 
						}, 
						update : function(serialNumber, device) {
							return $http({ 
								method: 'PUT',
								url: 'api/devices/' + serialNumber,
								data:  {
									device: device
								}
								
							}) 
						},  
						delete : function(serialNumber) {
							return $http({ 
								method: 'DELETE',
								url: 'api/devices/' + serialNumber								
							})
						}, 
					}
				});
				
				angular
				.module('zohobooksService', [])
				.factory('Zohobooks', function($http) {
					return {  
						loadClientsFromOrigin : function(access_token) {
							return $http({
								method: 'POST',
								url: 'load_clients'
							}) 
						}, 
						getClientsFromLocalStorage : function(access_token) {
							return $http({
								method: 'GET',
								url: 'clients'
							}) 
						},
					}
				});
				
				angular
				.module('clientsService', [])
				.factory('Clients', function($http) {
					return {  
						saveOrUpdate : function(clientFreshbooksId, clientInfo) {
							return $http({
								method: 'PUT',
								url: 'api/clients/' + clientFreshbooksId,
								data:  {
									fb_client_id: clientFreshbooksId,
									client_info: clientInfo
								}
							}) 
						} 
					}
				});
				
				angular
				.module('invoiceService', [])
				.factory('Invoice', function($http) {
					return {  
						send : function(batchIndex, sentData, achFileName = '') {
							return $http({
								method: 'POST',
								url: 'api/send_invoices',
								data: { 
									sent_data: sentData,
									batch_index: batchIndex,
									ach_filename: achFileName
								}
							})
						} 
					}
				});
				
				angular
				.module('invoicesUploadCtrl', [])
				.controller('invoicesUploadController', function($scope, $http, $timeout, Invoice) {
					
					$scope.sendingInvoicesInBackground = false;
					$scope.responseMessagesBlock = [];
					
					$scope.sendInvoicesInBatches = function(passedScope) {
						
						passedScope.sentData = {
							mono_usage: passedScope.mono_usage,
							color_usage: passedScope.color_usage,
							monocolor_usage: passedScope.monocolor_usage,
							color2_usage: passedScope.color2_usage,
							color3_usage: passedScope.color3_usage,
							city_friendly_name: passedScope.city_friendly_name
						}
						
						passedScope.sendInvoice = function(batchIndex) {
							Invoice
								.send(batchIndex, passedScope.sentData)
								.then(
									function(response) {
										
										console.log(response.data);
										var responseMessages = response.data.messages_array;
										var achFileName = response.data.filename;
										var continueFlag = (typeof response.data.continue_flag == "undefined") ? 'true' : response.data.continue_flag;
																				
										angular.forEach(responseMessages, function(message, invoiceNumber) {
											
											passedScope.responseMessagesBlock.push(message);
											
										});
										
										if (continueFlag == 'true') {
											
											batchIndex += 4;											
											passedScope.sendInvoice(batchIndex);
											
										}
										
									}, function(error) {
										
										console.log(error);
										
										passedScope.responseMessages.push(error);
									}
								);  
									
						}						
						
						passedScope.sendInvoice(0);
										
					}
					
					$scope.sendInvoices = function(passedScope) {						
						
						passedScope.sendingInvoicesInBackground = true;
						 
						passedScope.sendInvoicesInBatches(passedScope);			
						
					}	
					
					
					
				});
				
				angular
				.module('devicesNewCtrl', [])
				.controller('devicesNewController', function($scope, $http, $timeout, Device, Zohobooks) {
					
					$scope.deviceUploadingError = false;
					$scope.deviceUploadingSuccess = false;
					$scope.showLoadingSpinner = true;
					
					Zohobooks
						.getClientsFromLocalStorage()
						.then(	
							function(response) {
								$scope.zohobooksClients = response.data; 
								$scope.showLoadingSpinner = false;
								
								$scope.saveDeviceToDatabase = function(index) {
									 
									  Device
										.save($scope.devices[index])
										.then(
											function(response) {
									 
												//console.log(response.data);
												delete $scope.devices[index];								
												$scope.showDevicesUploadRow[index] = false;
												
												$scope.deviceUploadingSuccess = true;
												$timeout( function(){
													$scope.deviceUploadingSuccess = false;
												}, 3000);
												//console.log(response);
												if (Object.keys($scope.devices).length == 0) {
													$scope.showMessageAfterAllDevicesAreSaved = true;
												}
												
											}, function(error) {
												$scope.deviceUploadingError = true;
												$timeout( function(){
													$scope.deviceUploadingError = false;
												}, 3000);
											}
										);  
								}
							}
						);
				});
				
				angular
				.module('deviceUploadCtrl', [])
				.controller('deviceUploadController', function($scope, $http, $timeout, Device, Zohobooks) {
						
					//$scope.devices = {};
					$scope.filterDevices = {};
					$scope.hideDeletedDevice = {};
					$scope.deviceUploadingError = false;
					$scope.deviceUploadingSuccess = false;
					$scope.zohoClientListUpdateSuccess = false;
					$scope.showLoadingSpinner = true;
					$scope.buttonLoadingSpinner = false;
					
					$scope.updateClientList = function() {
						
						$scope.buttonLoadingSpinner = true;
						Zohobooks
						.loadClientsFromOrigin()
						.then(	
							function(response) {
								//console.log(response);
								$scope.zohoClientListUpdateSuccess = true;
								$scope.buttonLoadingSpinner = false;
								$timeout( function(){
									$scope.zohoClientListUpdateSuccess = false;
								}, 2000);
							}, function(error) {
								//console.log(error);
								$scope.deviceUploadingError = true;
								$scope.buttonLoadingSpinner = true;
							}
						)
					}
					
					Zohobooks
						.getClientsFromLocalStorage()
						.then(	
							function(response) {
								$scope.zohobooksClients = response.data; 
								$scope.showLoadingSpinner = false;
								
								/* Filtered Devices BEGIN */ 
						 
									$scope.pagedDevices = []
									,$scope.devicesCurrentPage = 1
									,$scope.devicesNumPerPage = 5
									,$scope.devicesMaxSize = 5; 

									$scope.$watch(
										'devicesCurrentPage + devicesNumPerPage + filterDevicesBySalesRep + filterDevicesByClient + filterDevicesBySerialNumber',
									function() {
										
										var begin = (($scope.devicesCurrentPage - 1) * $scope.devicesNumPerPage)
										, end = begin + $scope.devicesNumPerPage;
										
										$scope.filteredDevices = [];	 
										if (
											(
												$scope.filterDevicesBySalesRep != ''
											)
											||
											(
												$scope.filterDevicesByClient != null
											)
											||											
											(typeof $scope.filterDevicesBySerialNumber != 'undefined' &&
											$scope.filterDevicesBySerialNumber != '')
										) {
											for (var i = 0; i < $scope.devices.length; i++) {
												if (
													(
														typeof $scope.filterDevicesBySerialNumber == 'undefined'
														||
														$scope.filterDevicesBySerialNumber == ''
														||
														$scope.devices[i].serial.toLowerCase().indexOf($scope.filterDevicesBySerialNumber.toLowerCase()) !== -1
													)
													&&
													(
														$scope.filterDevicesBySalesRep == ''
														||
														$scope.devices[i].sales_rep == $scope.filterDevicesBySalesRep
													)
													&&
													(	
														$scope.filterDevicesByClient == null
														||
														$scope.devices[i].fb_client_id == $scope.filterDevicesByClient
													)
												) $scope.filteredDevices.push($scope.devices[i]);
											} 
											
											$scope.pagedDevices = $scope.filteredDevices.slice(begin, end); 
 
										} else { 
											$scope.filteredDevices = $scope.devices;
											$scope.pagedDevices = $scope.devices.slice(begin, end);
										}
									});			  
								
								/* Filtered Devices END */ 
								
							}, function(error) {
								//console.log(error);
								$scope.deviceUploadingError = true;
							}
						);	
						
					
					
					$scope.updateDeviceInDatabase = function(device, serialNumber) {
						 
						 Device
							.update(serialNumber, device)
							.then(
								function(response) {
									$scope.deviceUploadingSuccess = true;
									$timeout( function(){
										$scope.deviceUploadingSuccess = false;
									}, 2000);
								}, function(error) {	
									$scope.deviceUploadingError = true;
									$timeout( function(){
										$scope.deviceUploadingError = false;
									}, 2000);
								}
							); 
					}				
					
					$scope.deleteDeviceFromDatabase = function(index, serialNumber) {
						 
						Device
							.delete(serialNumber)
							.then(
								function(response) {
									console.log(response);									
									$scope.hideDeletedDevice[index] = true;
								}, function(error) {							
									console.log(error);
								}
							);
					}
				});

				angular
				.module('salesCtrl', [])
				.controller('salesController', function($scope, $http, $timeout) {
					
					/* Filtered Sales BEGIN */ 
					$scope.pagedSales = []
					,$scope.salesCurrentPage = 1
					,$scope.salesNumPerPage = 10
					,$scope.salesMaxSize = 5; 

					$scope.$watch('salesCurrentPage + salesNumPerPage + filterSalesBySalesRep + filterSalesByProfitAmount', function() {
						
						var begin = (($scope.salesCurrentPage - 1) * $scope.salesNumPerPage)
						, end = begin + $scope.salesNumPerPage;
						$scope.filteredSales = [];
						
						$scope.customFilterByProfitAmount = function(input, param) {
							
							switch (param) {
								case '25': 
									if ( input>= 1.25 ) return true;
									break;
								case '10': 
									if ( input > 1.1 && input < 1.25 ) return true;
									break;
								case '5': 
									if ( input <= 1.1 ) return true;
									break;
								default:
									return false;
									break;
							} 
						}

						if (
							(
								$scope.filterSalesBySalesRep != null
								&&
								$scope.filterSalesBySalesRep != ''
							)
							||
							(
								$scope.filterSalesByProfitAmount != null
								&&
								$scope.filterSalesByProfitAmount != ''
							)							
						) {						
							for (var i = 0; i < $scope.sales.length; i++) {
								if (
									(
										(
											$scope.filterSalesBySalesRep == null
											||
											$scope.filterSalesBySalesRep == ''											
										)
										||
										$scope.sales[i].device.sales_rep == $scope.filterSalesBySalesRep
									)
									&&
									(
										(
											$scope.filterSalesByProfitAmount == null
											||
											$scope.filterSalesByProfitAmount == ''											
										)
										||
										(
											$scope.sales[i].cost_amount != 0 &&
											$scope.customFilterByProfitAmount($scope.sales[i].price_amount/$scope.sales[i].cost_amount, $scope.filterSalesByProfitAmount) == true
										)
									)
								) $scope.filteredSales.push($scope.sales[i]);
							}
							
							$scope.pagedSales = $scope.filteredSales.slice(begin, end); 							
						} else {											
							$scope.pagedSales = $scope.sales.slice(begin, end);
							$scope.filteredSales = $scope.sales;
						} 
						
						/* Calculate profits  - we can't divide by zero */						
						$scope.totalCosts = {
							'jesse' : 0,
							'laine' : 0,
							'greg' : 0						
						};
						$scope.totalPrices = {
							'jesse' : 0,
							'laine' : 0,
							'greg' : 0							
						};					
						$scope.lastMonthTotalCosts = {
							'jesse' : 0,
							'laine' : 0,
							'greg' : 0			
						};					
						$scope.lastMonthTotalPrices = {
							'jesse' : 0,
							'laine' : 0,
							'greg' : 0							
						};
						
						var currentDate = new Date();
						var currentMonth = currentDate.getMonth()-1;
						
						var i = 0;
						var j = 0;
						angular.forEach($scope.sales, function(sale, key) {
							
							/* Last month */
							var dateParams = sale.date.split('-');
							var saleDate = new Date(dateParams[0], parseInt(dateParams[1]) - 1, dateParams[2]);							
							var saleMonth = saleDate.getMonth();
							
							/* All time */
							if (typeof $scope.totalCosts[sale.device.sales_rep] == 'undefined') $scope.totalCosts[sale.device.sales_rep] = 0;
							if (typeof $scope.totalPrices[sale.device.sales_rep] == 'undefined') $scope.totalPrices[sale.device.sales_rep] = 0;
							$scope.totalCosts[sale.device.sales_rep] += sale.cost_amount;
							$scope.totalPrices[sale.device.sales_rep] += sale.price_amount;
							
							if (saleMonth == currentMonth) {
								$scope.lastMonthTotalCosts[sale.device.sales_rep] += sale.cost_amount;
								$scope.lastMonthTotalPrices[sale.device.sales_rep] += sale.price_amount;
							}
							
						});
						 
					});
					/* Filtered Sales END */
					
					
					
				});

				angular
				.module('achFilesCtrl', [])
				.controller('achFilesController', function($scope, $http, $timeout, Zohobooks, Clients) {
					
					$scope.clientUploadingError = false;
					$scope.clientUploadingSuccess = false;
					$scope.showLoadingSpinner = true;
					$scope.achIncludeOptions = [
						{'value': 1, 'name': 'Yes'},
						{'value': 0, 'name': 'No'},
					]
					
					$scope.buttonLoadingSpinner = false;
					
					$scope.updateClientList = function() {
						
						$scope.buttonLoadingSpinner = true;
						Zohobooks
						.loadClientsFromOrigin()
						.then(	
							function(response) {
								//console.log(response);
								$scope.zohoClientListUpdateSuccess = true;
								$scope.buttonLoadingSpinner = false;
								$timeout( function(){
									$scope.zohoClientListUpdateSuccess = false;
								}, 2000);
							}, function(error) {
								//console.log(error);
								$scope.deviceUploadingError = true;
								$scope.buttonLoadingSpinner = true;
							}
						)
					}
					
					Zohobooks
						.getClientsFromLocalStorage()
						.then(	
							function(response) {
								$scope.showLoadingSpinner = false;
								$scope.zohobooksClients = response.data;
						
								/* Filtered ACH Files BEGIN */ 
								$scope.filteredAchFiles = []
								,$scope.achFilesCurrentPage = 1
								,$scope.achFilesNumPerPage = 10
								,$scope.achFilesMaxSize = 5; 

								$scope.$watch('achFilesCurrentPage + achFilesNumPerPage', function() {
									var begin = (($scope.achFilesCurrentPage - 1) * $scope.achFilesNumPerPage)
									, end = begin + $scope.achFilesNumPerPage;

									$scope.filteredAchFiles = $scope.achFiles.slice(begin, end);
								});
								/* Filtered ACH Files END */
								
								
								$scope.updateClientInDatabase = function(zohobooksClientId) {
									
									Clients
										.saveOrUpdate(zohobooksClientId, $scope.clients[zohobooksClientId])
										.then(	
											function(response) {
												//console.log(response.data);		
												$scope.clientUploadingSuccess = true;	
												$timeout( function(){
													$scope.clientUploadingSuccess = false;
												}, 3000);									 
											}, function(error) {
												//console.log(error);
												$scope.clientUploadingError = true;
												$timeout( function(){
													$scope.clientUploadingError = false;
												}, 3000);
											}
										);	
								}
								
							}, function(error) {
								//console.log(error);
								$scope.clientUploadingError = true;
							}
						);
				});
		})();
	</script>
</body>
</html>
