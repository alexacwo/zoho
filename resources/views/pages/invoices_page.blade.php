@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="col-sm-12">
					
            <div class="panel panel-success">
                <div class="panel-heading">
					Invoices
                </div>

				<style>
					[ng\:cloak], [ng-cloak], [data-ng-cloak], [x-ng-cloak], .ng-cloak, .x-ng-cloak {
					  display: none !important;
					}
				</style>
                <div class="panel-body ng-cloak">				
				
					<div class="col-md-12">			
					
						<form action="{{ url('/invoice') }}" method="POST" class="form-horizontal" enctype="multipart/form-data">
							{{ csrf_field() }}
							
							@if (session('messages_array'))
								<div class="col-md-12" style="padding-top:0px; padding-left:0px; margin-bottom:20px;">					
									<div class="alert alert-warning" style="margin-bottom:0px;"> 
										@foreach(session('messages_array') as $fb_client_id => $response_message) 
											<strong>{{ $response_message }}</strong>											
											@if (!$loop->last)
											<br><br>
											@endif							
										@endforeach
										
										@if (null != (session('filename')) && (null != (session('txt')) && session('txt') != ''))
											<br>
											<br>
											<a href="{{ url('/storage/files') . '/' . session('filename') }}">
												ACH file link
											</a>
										@elseif (session('txt') == '')
											<br>
											<br>
											No ACH files were created. (Check the ACH Info tab to set 'Create ACH File' option for the clients to 'Yes')
										@endif 
									</div>
								</div>	
							@endif 
							 
							
							<div class="form-group">
								<div class="col-sm-4">
									<div class="fileupload fileupload-new" data-provides="fileupload">
										<span class="btn btn-primary btn-file" style="float:left;">
											<span class="fileupload-new">
												Select file
											</span>
											<span class="fileupload-exists">
												Change
											</span>
											<input name="excel_invoice" type="file"
												 data-toggle="collapse" data-target="#hideFileUpload"
											/>
										</span>
										<span class="fileupload-preview" style="float:left; margin-right:10px;">
										</span>
										<a href="#" class="close fileupload-exists" data-dismiss="fileupload" style="float:left;">
											×
										</a>
										<div style="clear:both;"></div>
										
										
										<button type="submit" class="btn btn-default" id="upload_file_button" style="margin-top:20px;">
											<i class="fa fa-plus"></i> Upload invoice
										</button>
										
									</div>
									
								</div>
							
							</div> 
						</form>
						
					</div>
				</div>
			</div> 
			 
        </div>
    </div>
@endsection