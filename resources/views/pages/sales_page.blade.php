@extends('layouts.app')

@section('content')
    <div class="container sales_page" ng-controller="salesController">
        <div class="col-sm-12">
		
            <div class="panel panel-info">
                <div class="panel-heading">
					Sales
                </div>
				
				<style>
					[ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
					  display: none !important;
					}
				</style>
                <div class="panel-body ng-cloak">	
					
					@if (count($sales) > 0)
						
						<div ng-init="sales = {{$sales}}">
							<div class="col-sm-12">
								<b>Profits:</b>
								<br>
							</div>
							<div class="col-sm-4" style="min-height: 150px;border:1px solid #ddd;">
								<br><span style="background:#eee;"><b>Jesse Jarwell:</b></span>
								<br><i>Last month</i>:
								<br>$<% (lastMonthTotalPrices.jesse - lastMonthTotalCosts.jesse) | number :2 %>
								/ Average:
								<% lastMonthTotalCosts.jesse > 0 ?
								((lastMonthTotalPrices.jesse - lastMonthTotalCosts.jesse)/lastMonthTotalCosts.jesse)*100 : 0 | number :2 %> %
								<br><i>All time</i>:
								<br>$<% (totalPrices.jesse - totalCosts.jesse) | number :2 %>
								/ Average:
								<% totalCosts.jesse > 0 ?
								((totalPrices.jesse - totalCosts.jesse)/totalCosts.jesse)*100 : 0 | number :2 %> %
								<br><br>
							</div>
							<div class="col-sm-4" style="min-height: 150px;border:1px solid #ddd;">
								<br><span style="background:#eee;"><b>Laine Dobson:</b></span>
								<br><i>Last month</i>:
								<br>$<% (lastMonthTotalPrices.laine - lastMonthTotalCosts.laine) | number :2 %>
								/ Average: <% lastMonthTotalCosts.laine > 0 ?
								((lastMonthTotalPrices.laine - lastMonthTotalCosts.laine)/lastMonthTotalCosts.laine)*100 : 0 | number :2 %> %
								<br><i>All time</i>:
								<br>$<% (totalPrices.laine - totalCosts.laine) | number :2 %>
								/ Average: <% totalCosts.laine > 0 ?
								((totalPrices.laine - totalCosts.laine)/totalCosts.laine)*100 : 0 | number :2 %> %
								<br><br>
							</div>
							<div class="col-sm-4" style="min-height: 150px;border:1px solid #ddd;">
								<br><span style="background:#eee;"><b>Greg Bentz:</b></span>
								<br><i>Last month</i>:
								<br>$<% (lastMonthTotalPrices.greg - lastMonthTotalCosts.greg) | number :2 %>
								/ Average: <% lastMonthTotalCosts.greg > 0 ?
								((lastMonthTotalPrices.greg - lastMonthTotalCosts.greg)/lastMonthTotalCosts.greg)*100 : 0 | number :2 %> %
								<br><i>All time</i>:
								<br>$<% (totalPrices.greg - totalCosts.greg) | number :2 %>
								/ Average: <% totalCosts.greg > 0 ?
								((totalPrices.greg - totalCosts.greg)/totalCosts.greg)*100 : 0 | number :2 %> %
								<br><br>
							</div> 
							<div class="col-sm-1" style="margin-top:20px;">
								<h4><b>Filters:</b></h4>
							</div>
							
							<div class="col-sm-3" style="margin-top:20px;">
								<select class="form-control"
									ng-model="filterSalesBySalesRep"
									ng-init="filterSalesBySalesRep = null"
									>
									<option value="">
										All
									</option>
									<option value="jesse">
										Jesse Harwell
									</option>
									<option value="laine">
										Laine Dobson
									</option>
									<option value="greg">
										Greg Bentz
									</option>
								</select>
							</div>
								
							<div class="col-sm-3" style="margin-top:20px;">
								<select class="form-control"
									ng-model="filterSalesByProfitAmount"
									ng-init="filterSalesByProfitAmount = null"
								>
									<option value="">
										All
									</option>
									<option value="25">
										More than 25%
									</option>
									<option value="10">
										From 10% to 25%
									</option>
									<option value="5">
										Less than 10%
									</option>
								</select>
							</div>
							
							<div class="col-sm-12">
								<ul uib-pagination
											total-items="filteredSales.length"
											items-per-page="salesNumPerPage"
											ng-model="salesCurrentPage"
											max-size="salesMaxSize"
											class="pagination-sm"
											boundary-links="true"></ul>
							</div>
										
							<table class="table table-striped task-table">

								<thead>
									<th>Date</th>
									<th>Device serial number</th>
									<th>Total cost amount</th>
									<th>Total price amount</th>
									<th>Profit($)</th>
									<th>Profit(%)</th>
									<th>Sales rep</th>
								</thead>
								<tbody>
								 
										<tr
											ng-class="{
												green: sale.price_amount/sale.cost_amount >= 1.25,
												yellow: sale.price_amount/sale.cost_amount > 1.1 && sale.price_amount/sale.cost_amount < 1.25,
												red: sale.price_amount/sale.cost_amount <= 1.1,
											}"
											ng-repeat="sale in pagedSales"
										>
											<td class="table-text col-sm-2">					
												<% sale.date %>	 
											</td>
											<td class="table-text col-sm-2">					
												<% sale.device.serial %>	 
											</td>
											<td class="table-text col-sm-2">										
												<% sale.cost_amount %> 
											</td>
											<td class="table-text col-sm-2">									
												<% sale.price_amount %>	 
											</td>
											<td class="table-text col-sm-1">									
												<% (sale.price_amount - sale.cost_amount) | number:3 %>	 
											</td>
											<td class="table-text col-sm-1">									
												<% (sale.price_amount/sale.cost_amount - 1)*100 | number:2%>% 
											</td>
											<td class="table-text col-sm-2">				
												<span ng-if="sale.device.sales_rep == 'jesse'">Jesse Harwell</span>
												<span ng-if="sale.device.sales_rep == 'laine'">Laine Dobson</span>
												<span ng-if="sale.device.sales_rep == 'greg'">Greg Bentz</span>
											</td>
										</tr> 
								</tbody>
							</table>
							
							<div class="col-sm-12">
								<ul uib-pagination
											total-items="filteredSales.length"
											items-per-page="salesNumPerPage"
											ng-model="salesCurrentPage"
											max-size="salesMaxSize"
											class="pagination-sm"
											boundary-links="true"></ul>
							</div>
							
						</div>
					@else 
						No sales yet.
					@endif
				</div>
			</div>
			
        </div>
    </div>
@endsection