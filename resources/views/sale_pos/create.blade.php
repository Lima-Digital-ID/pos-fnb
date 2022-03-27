@extends('layouts.app')

@section('title', 'POS')

@section('content')

<!-- Content Header (Page header) -->
<!-- <section class="content-header">
    <h1>Add Purchase</h1> -->
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
<!-- </section> -->

<!-- Main content -->
<section class="content no-print">
	<div class="row">
		<div class="@if(!empty($pos_settings['hide_product_suggestion']) && !empty($pos_settings['hide_recent_trans'])) col-md-10 col-md-offset-1 @else col-md-7 @endif col-sm-12">
			@component('components.widget', ['class' => 'box-success'])
				@slot('header')
					<div class="col-sm-6">
						<h3 class="box-title">POS Terminal <i class="fa fa-keyboard-o hover-q text-muted" aria-hidden="true" data-container="body" data-toggle="popover" data-placement="bottom" data-content="@include('sale_pos.partials.keyboard_shortcuts_details')" data-html="true" data-trigger="hover" data-original-title="" title=""></i></h3>
					</div>
					<input type="hidden" id="item_addition_method" value="{{$business_details->item_addition_method}}">
					@if(is_null($default_location))
						<div class="col-sm-6">
							<div class="form-group" style="margin-bottom: 0px;">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fa fa-map-marker"></i>
									</span>
								{!! Form::select('select_location_id', $business_locations, $location_id, ['class' => 'form-control input-sm mousetrap', 
								'placeholder' => __('lang_v1.select_location'),
								'id' => 'select_location_id', 
								'required', 'autofocus'], $bl_attributes); !!}
								<span class="input-group-addon">
										@show_tooltip(__('tooltip.sale_location'))
									</span> 
								</div>
							</div>
						</div>
					@endif
				@endslot
				<div class="box-body row">
					<div class="col-sm-12">
						<div class="form-group">
						  <a class="btn btn-primary" data-toggle="collapse" href="#collapseCustomer" role="button" aria-expanded="false" aria-controls="collapseCustomer">
						    Tambah Customer
						  </a>
						  <a class="btn btn-success" data-toggle="collapse" href="#collapseEditCustomer" role="button" aria-expanded="false" aria-controls="collapseEditCustomer">
						    Edit Customer
						  </a>
					  	</div>
					</div>
					<div class="collapse" id="collapseCustomer">
						<form class="" method="post" action="" id="add_customer_form">
							<div class="col-md-6">
						        <div class="form-group">
						            <div class="input-group">
						                <span class="input-group-addon">
						                    <i class="fa fa-user"></i>
						                </span>
						                {!! Form::hidden('type', 'customer', ['class' => 'form-control', 'id' => 'type2', 'placeholder' => __('contact.name'), 'required']); !!}
						                {!! Form::text('name', null, ['class' => 'form-control', 'id' => 'name2', 'placeholder' => __('contact.name'), 'required']); !!}
						            </div>
						        </div>
						     </div>
					    	<div class="col-md-6 customer_fields">
					          <div class="form-group">
					              <div class="input-group">
					                  <span class="input-group-addon">
					                      <i class="fa fa-users"></i>
					                  </span>
					                  {!! Form::select('customer_group_id', $customer_groups, '', [ 'id' => 'customer_group_id2', 'class' => 'form-control']); !!}
					              </div>
					          </div>
					        </div>
					        <div class="col-md-6 customer_fields">
					          <div class="form-group">
					              <div class="input-group">
					                  <span class="input-group-addon">
					                      <i class="fa fa-barcode"></i>
					                  </span>
					                  {!! Form::text('member_code', null, ['class' => 'form-control', 'title' => 'Kode Member', 'placeholder' => 'Kode Member']); !!}
					              </div>
					          </div>
					        </div>
					        <div class="col-md-6 customer_fields">
					          <div class="form-group">
					              <div class="input-group">
					                  <span class="input-group-addon">
					                      <i class="fa fa-calendar-times-o"></i>
					                  </span>
					                  {!! Form::date('exp_member_date', null, ['class' => 'form-control', 'title' => 'Tanggal Kadaluarsa Member']); !!}
					              </div>
					          </div>
					        </div>
						    <div class="col-md-6">
						        <div class="form-group">
						            <div class="input-group">
						                <span class="input-group-addon">
						                    <i class="fa fa-mobile"></i>
						                </span>
						                {!! Form::text('mobile', null, ['class' => 'form-control',  'id' => 'mobile2', 'onchange' => 'cekMobile2(this.value)', 'required', 'placeholder' => __('contact.mobile')]); !!}
						            </div>
						        </div>
						     </div>
						     <div class="col-md-3">
						        <div class="form-group">
						            <div class="input-group">
						                <span class="input-group-addon">
						                    <i class="fa fa-calendar"></i>
						                </span>
						                {!! Form::date('birthday', null, ['class' => 'form-control', 'id' => 'birthday1']); !!}
						            </div>
						        </div>
						      </div>
						     <div class="col-md-12">
						        <div class="form-group">
						            <button class="btn btn-success">Simpan</button>
						        </div>
						      </div>
					    </form>
					</div>
					<div class="collapse" id="collapseEditCustomer">
						{!! Form::open(['url' => '', 'method' => 'put', 'id' => 'edit_customer_form' ]) !!}
						<!-- <form class="" method="post" action="" id="edit_customer_form"> -->
							<div class="col-md-6">
						        <div class="form-group">
						            <div class="input-group">
						                <span class="input-group-addon">
						                    <i class="fa fa-user"></i>
						                </span>
						                {!! Form::select('id_customer', 
										[], null, ['class' => 'form-control', 'onchange' => '', 'id' => 'id_customer', 'placeholder' => 'Enter Customer name / phone', 'required', 'style' => 'width: 100%;']); !!}
						            </div>
						        </div>
						     </div>
					    	<div class="col-md-6 customer_fields">
					          <div class="form-group">
					              <div class="input-group">
					                  <span class="input-group-addon">
					                      <i class="fa fa-users"></i>
					                  </span>
					                  {!! Form::select('customer_group_id', $customer_groups, '', [ 'id' => 'customer_group_id3', 'class' => 'form-control']); !!}
					              </div>
					          </div>
					        </div>
					        <div class="col-md-6 customer_fields">
					          <div class="form-group">
					              <div class="input-group">
					                  <span class="input-group-addon">
					                      <i class="fa fa-barcode"></i>
					                  </span>
					                  {!! Form::text('member_code', null, ['class' => 'form-control', 'id' => 'member_code', 'title' => 'Kode Member', 'placeholder' => 'Kode Member']); !!}
					              </div>
					          </div>
					        </div>
					        <div class="col-md-6 customer_fields">
					          <div class="form-group">
					              <div class="input-group">
					                  <span class="input-group-addon">
					                      <i class="fa fa-calendar-times-o"></i>
					                  </span>
					                  {!! Form::date('exp_member_date', null, ['class' => 'form-control', 'id' => 'exp_member_date', 'title' => 'Tanggal Kadaluarsa Member']); !!}
					              </div>
					          </div>
					        </div>
						    <div class="col-md-6">
						        <div class="form-group">
						            <div class="input-group">
						                <span class="input-group-addon">
						                    <i class="fa fa-mobile"></i>
						                </span>
						                {!! Form::text('mobile', null, ['class' => 'form-control',  'id' => 'mobile3', 'onchange' => 'cekMobile2(this.value)', 'required', 'placeholder' => __('contact.mobile')]); !!}
						            </div>
						        </div>
						     </div>
						     <div class="col-md-3">
						        <div class="form-group">
						            <div class="input-group">
						                <span class="input-group-addon">
						                    <i class="fa fa-calendar"></i>
						                </span>
						                {!! Form::date('birthday', null, ['class' => 'form-control', 'id' => 'birthday2']); !!}
						            </div>
						        </div>
						      </div>
						     <div class="col-md-12">
						        <div class="form-group">
						            <button class="btn btn-success">Update</button>
						        </div>
						      </div>
					    {!! Form::close() !!}
					</div>
				</div>
				{!! Form::open(['url' => action('SellPosController@store'), 'method' => 'post', 'id' => 'add_pos_sell_form' ]) !!}

				{!! Form::hidden('location_id', $location_id, ['id' => 'location_id', 'data-receipt_printer_type' => isset($bl_attributes[$default_location]['data-receipt_printer_type']) ? $bl_attributes[$default_location]['data-receipt_printer_type'] : 'browser']); !!}

				<!-- /.box-header -->
				<div class="box-body">
					<div class="row">
						@if(config('constants.enable_sell_in_diff_currency') == true)
							<div class="col-md-4 col-sm-6">
								<div class="form-group">
									<div class="input-group">
										<span class="input-group-addon">
											<i class="fa fa-exchange"></i>
										</span>
										{!! Form::text('exchange_rate', config('constants.currency_exchange_rate'), ['class' => 'form-control input-sm input_number', 'placeholder' => __('lang_v1.currency_exchange_rate'), 'id' => 'exchange_rate']); !!}
									</div>
								</div>
							</div>
						@endif
						@if(!empty($price_groups))
							@if(count($price_groups) > 1)
								<div class="col-md-4 col-sm-6">
									<div class="form-group">
										<div class="input-group">
											<span class="input-group-addon">
												<i class="fa fa-money"></i>
											</span>
											@php
												reset($price_groups);
											@endphp
											{!! Form::hidden('hidden_price_group', key($price_groups), ['id' => 'hidden_price_group']) !!}
											{!! Form::select('price_group', $price_groups, null, ['class' => 'form-control select2', 'id' => 'price_group', 'style' => 'width: 100%;']); !!}
											<span class="input-group-addon">
												@show_tooltip(__('lang_v1.price_group_help_text'))
											</span> 
										</div>
									</div>
								</div>
							@else
								@php
									reset($price_groups);
								@endphp
								{!! Form::hidden('price_group', key($price_groups), ['id' => 'price_group']) !!}
							@endif
						@endif
						
						@if(in_array('subscription', $enabled_modules))
							<div class="col-md-4 pull-right col-sm-6">
								<div class="checkbox">
									<label>
						              {!! Form::checkbox('is_recurring', 1, false, ['class' => 'input-icheck', 'id' => 'is_recurring']); !!} @lang('lang_v1.subscribe')?
						            </label><button type="button" data-toggle="modal" data-target="#recurringInvoiceModal" class="btn btn-link"><i class="fa fa-external-link"></i></button>@show_tooltip(__('lang_v1.recurring_invoice_help'))
								</div>
							</div>
						@endif
					</div>
					<div class="row">
						<div class="@if(!empty($commission_agent)) col-sm-4 @else col-sm-6 @endif">
							<div class="form-group" style="width: 100% !important">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fa fa-user"></i>
									</span>
									<input type="hidden" id="default_customer_id" 
									value="{{ $walk_in_customer['id']}}" >
									<input type="hidden" id="default_customer_name" 
									value="{{ $walk_in_customer['name']}}" >
									{!! Form::select('contact_id', 
										[], null, ['class' => 'form-control mousetrap', 'onchange' => 'reset_pos_form2()', 'id' => 'customer_id', 'placeholder' => 'Enter Customer name / phone', 'required', 'style' => 'width: 100%;']); !!}
									<!--<span class="input-group-btn">-->
										<!-- <button type="button" class="btn btn-default bg-white btn-flat add_new_customer" data-name=""  @if(!auth()->user()->can('customer.create')) disabled @endif><i class="fa fa-plus-circle text-primary fa-lg"></i></button> -->
										<!--<button type="button" class="btn btn-default bg-white btn-flat add_new_customer" data-name="" @if(!auth()->user()->can('customer.create')) disabled @endif><i class="fa fa-plus-circle text-primary fa-lg"></i></button>-->
									<!--</span>-->
								</div>
							</div>
						</div>
						<input type="hidden" name="pay_term_number" id="pay_term_number" value="{{$walk_in_customer['pay_term_number']}}">
						<input type="hidden" name="pay_term_type" id="pay_term_type" value="{{$walk_in_customer['pay_term_type']}}">
						
						@if(!empty($commission_agent))
							<div class="col-sm-4">
								<div class="form-group">
								{!! Form::select('commission_agent', 
											$commission_agent, null, ['class' => 'form-control select2', 'placeholder' => __('lang_v1.commission_agent')]); !!}
								</div>
							</div>
						@endif
						<div class="col-sm-6">
							<div class="form-group">
								<select name="" id="kategori_customer" class="form-control select2">
									@foreach ($kategoriCustomer as $item)
										<option value="{{$item->id}}">Harga {{$item->kategori}}</option>
									@endforeach
								</select>
							</div>
						</div>

						<div class="col-sm-6">
							<div class="form-group">
								<input type="" id="contact_number" readonly="" placeholder="No. Hp" class="form-control">
							</div>
						</div>
						<div class="@if(!empty($commission_agent)) col-sm-4 @else col-sm-6 @endif">
							<div class="form-group">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fa fa-barcode"></i>
									</span>
									{!! Form::text('search_product', null, ['class' => 'form-control mousetrap', 'id' => 'search_product', 'placeholder' => __('lang_v1.search_product_placeholder'),
									'disabled' => is_null($default_location)? true : false,
									'autofocus' => is_null($default_location)? false : true,
									]); !!}
									<span class="input-group-btn">
										<button type="button" class="btn btn-default bg-white btn-flat pos_add_quick_product" data-href="{{action('ProductController@quickAdd')}}" data-container=".quick_add_product_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
									</span>
								</div>
							</div>
						</div>

						<div class="col-sm-6" hidden>
							<div class="form-group">
								<input class="form-check-input" type="checkbox" value="" id="contact_member" disabled>
								<label class="form-check-label" for="contact_member">
								Member
								</label>
							</div>
						</div>
						<div class="@if(!empty($commission_agent)) col-sm-4 @else col-sm-6 @endif" hidden>
							<div class="form-group">
								<div class="input-group">
									<span class="input-group-addon">
										<i class="fa fa-cut"></i>
									</span>
									{!! Form::select('id_pegawai', 
										$pegawai_option, null, ['class' => 'form-control select2', 'placeholder' => 'Select Barberman']); !!}
								</div>
							</div>
						</div>
						<div class="clearfix"></div>

						<!-- Call restaurant module if defined -->
				        @if(in_array('tables' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
				        	<span id="restaurant_module_span">
				          		<div class="col-md-3"></div>
				        	</span>
				        @endif
			        </div>

					<div class="row">
					<div class="col-sm-12 pos_product_div">
						<input type="hidden" name="sell_price_tax" id="sell_price_tax" value="{{$business_details->sell_price_tax}}">

						<!-- Keeps count of product rows -->
						<input type="hidden" id="product_row_count" 
							value="0">
						@php
							$hide_tax = '';
							if( session()->get('business.enable_inline_tax') == 0){
								$hide_tax = 'hide';
							}
						@endphp
						<table class="table table-condensed table-bordered table-striped table-responsive" id="pos_table">
							<thead>
								<tr>
									<th class="tex-center @if(!empty($pos_settings['inline_service_staff'])) col-md-3 @else col-md-4 @endif">	
										@lang('sale.product') @show_tooltip(__('lang_v1.tooltip_sell_product_column'))
									</th>
									<th class="text-center col-md-3">
										@lang('sale.qty')
									</th>
									@if(!empty($pos_settings['inline_service_staff']))
										<th class="text-center col-md-2">
											@lang('restaurant.service_staff')
										</th>
									@endif
									<th class="text-center col-md-2 {{$hide_tax}}">
										@lang('sale.price_inc_tax')
									</th>
									<th class="text-center col-md-2">
										Pegawai
									</th>
									<th class="text-center col-md-2">
										@lang('sale.subtotal')
									</th>
									<th class="text-center"><i class="fa fa-close" aria-hidden="true"></i></th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
						</div>
					</div>
					@include('sale_pos.partials.pos_details')

					@include('sale_pos.partials.payment_modal')

					@if(empty($pos_settings['disable_suspend']))
						@include('sale_pos.partials.suspend_note_modal')
					@endif

					@if(empty($pos_settings['disable_recurring_invoice']))
						@include('sale_pos.partials.recurring_invoice_modal')
					@endif
				</div>
				<!-- /.box-body -->
				{!! Form::close() !!}
			@endcomponent
		</div>

		<div class="col-md-5 col-sm-12">
			@include('sale_pos.partials.right_div')
		</div>
	</div>
</section>

<!-- This will be printed -->
<section class="invoice print_section" id="receipt_section">
</section>
<div class="modal fade contact_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
	@include('contact.create', ['quick_add' => true])
</div>
<!-- /.content -->
<div class="modal fade register_details_modal" tabindex="-1" role="dialog" 
	aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog" 
	aria-labelledby="gridSystemModalLabel">
</div>
<!-- quick product modal -->
<div class="modal fade quick_add_product_modal" tabindex="-1" role="dialog" aria-labelledby="modalTitle"></div>

<!-- modal input pengeluaran -->
<div class="modal fade" tabindex="-1" role="dialog" id="pengeluaran_modal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Input Pengeluaran</h4>
			</div>
			{!! Form::open(['url' => action('SellPosController@savePengeluaran'), 'method' => 'post']) !!}
			<div class="modal-body">
				{!! Form::hidden('user_id', $user_id, ['class' => 'form-control', 'id' => 'user_id']); !!}
				@if(is_null($default_location))
				<div class="form-group">
		            {!! Form::select('id_lokasi', 
		                        $location_option, $location_id, ['class' => 'form-control', 'placeholder' => 'Pilih Lokasi', 'required' => 'required']); !!}
		        </div>
		        @endif
		        <div class="row">
			        <div class="col-sm-6">
		        		<div class="form-group">
		        			<label>Total Uang Tunai</label>
							{!! Form::number('jml_tunai_pengeluaran', 0, ['class' => 'form-control', 'id' => 'jml_tunai_pengeluaran', 'placeholder' => 'Jumlah Pengeluaran', 'required' => 'required', 'onkeyup'=>'cekTunaiPengeluaran(this.value)']); !!}
						</div>
		        	</div>
		        	<div class="col-sm-6">
		        		<div class="form-group">
		        			<label>Total Petty</label>
							{!! Form::number('jml_petty_pengeluaran', 0, ['class' => 'form-control', 'id' => 'jml_petty_pengeluaran', 'placeholder' => 'Jumlah Pengeluaran', 'required' => 'required', 'onkeyup'=>'cekPettyPengeluaran(this.value)']); !!}
						</div>
		        	</div>
		        </div>
		        <div class="row">
			        <div class="col-sm-6">
						<div class="form-group">
							{!! Form::number('jml_pengeluaran', null, ['class' => 'form-control', 'id' => 'jml_pengeluaran', 'placeholder' => 'Jumlah Pengeluaran', 'readonly', 'required' => 'required'/*, 'onkeyup' => 'cekSisaPetty(this.value)'*/]); !!}
						</div>
		        	</div>
		        	<div class="col-sm-6">
		        		<div class="form-group">
				            {!! Form::select('id_akun', 
				                        $akun_pengeluaran, 0, ['class' => 'form-control', 'placeholder' => 'Pilih Akun', 'required' => 'required']); !!}
				        </div>
		        	</div>
		        </div>
				<div class="form-group">
					{!! Form::textarea('desc_pengeluaran', null, ['class' => 'form-control', 'id' => 'desc_pengeluaran', 'placeholder' => 'Deskripsi', 'required' => 'required']); !!}
				</div>
			</div>

			<div class="modal-footer">
				<button class="btn btn-primary">simpan</button>
			</div>
			{!! Form::close() !!}

		</div>
	</div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="petty_modal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Input Petty Cash</h4>
			</div>
			{!! Form::open(['url' => action('SellPosController@inputPetty'), 'method' => 'post']) !!}
			<div class="modal-body">
		        {!! Form::hidden('user_id', $user_id, ['class' => 'form-control', 'id' => 'user_id']); !!}
		        @if(is_null($default_location))
				<div class="form-group">
		            {!! Form::select('id_lokasi', 
		                        $location_option, $location_id, ['class' => 'form-control', 'placeholder' => 'Pilih Lokasi', 'required' => 'required']); !!}
		        </div>
		        @endif
		        <div class="row">
		        	<div class="col-sm-6">
		        		<div class="form-group">
		        			<label>Total Uang Tunai</label>
							{!! Form::number('jml_tunai_petty', 0, ['class' => 'form-control', 'id' => 'jml_tunai_petty', 'placeholder' => 'Jumlah Pengeluaran', 'required' => 'required', 'onkeyup'=>'cekTunaiPetty(this.value)']); !!}
						</div>
		        	</div>
		        	<div class="col-sm-6">
		        		<div class="form-group">
		        			<label>Total Non Tunai</label>
							{!! Form::number('jml_non_tunai_petty', 0, ['class' => 'form-control', 'id' => 'jml_non_tunai_petty', 'placeholder' => 'Jumlah Pengeluaran', 'required' => 'required', 'onkeyup'=>'cekNonTunaiPetty(this.value)']); !!}
						</div>
		        	</div>
		        </div>
				<div class="form-group">
					{!! Form::number('jml_pengeluaran_petty', null, ['class' => 'form-control', 'id' => 'jml_pengeluaran_petty', 'placeholder' => 'Jumlah Pengeluaran', 'required' => 'required']); !!}
				</div>
				<div class="form-group">
					{!! Form::textarea('desc_pengeluaran_petty', null, ['class' => 'form-control', 'id' => 'desc_pengeluaran_petty', 'placeholder' => 'Deskripsi', 'required' => 'required']); !!}
				</div>
			</div>

			<div class="modal-footer">
				<button class="btn btn-primary">simpan</button>
			</div>
			{!! Form::close() !!}

		</div>
	</div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="kasbon_modal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Input Kasbon</h4>
			</div>
			{!! Form::open(['url' => action('SellPosController@inputKasbon'), 'method' => 'post']) !!}
			<div class="modal-body">
		        {!! Form::hidden('user_id', $user_id, ['class' => 'form-control', 'id' => 'user_id']); !!}
		        @if(is_null($default_location))
				<div class="form-group">
		            {!! Form::select('id_lokasi', 
		                        $location_option, $location_id, ['class' => 'form-control', 'placeholder' => 'Pilih Lokasi', 'required' => 'required', 'onchange' => 'getPegawai(this.value)']); !!}
		        </div>
		        @endif
		        <div class="form-group">
		            {!! Form::select('pegawai_id', 
		                        $pegawai_option, null, ['class' => 'form-control', 'id' => 'pegawai_id', 'placeholder' => 'Pilih Pegawai', 'required' => 'required']); !!}
		        </div>
		        <div class="row">
		        	<div class="col-sm-6">
		        		<div class="form-group">
		        			<label>Total Uang Tunai</label>
							{!! Form::number('jml_tunai_kasbon', 0, ['class' => 'form-control', 'id' => 'jml_tunai_kasbon', 'placeholder' => 'Jumlah Pengeluaran', 'required' => 'required', 'onkeyup'=>'cekTunaiKasbon(this.value)']); !!}
						</div>
		        	</div>
		        	<div class="col-sm-6">
		        		<div class="form-group">
		        			<label>Total Non Tunai</label>
							{!! Form::number('jml_non_tunai_kasbon', 0, ['class' => 'form-control', 'id' => 'jml_non_tunai_kasbon', 'placeholder' => 'Jumlah Pengeluaran', 'required' => 'required', 'onkeyup'=>'cekNonTunaiKasbon(this.value)']); !!}
						</div>
		        	</div>
		        </div>
				<div class="form-group">
					{!! Form::number('jml_pengeluaran_kasbon', null, ['class' => 'form-control', 'id' => 'jml_pengeluaran_kasbon', 'placeholder' => 'Jumlah Pengeluaran', 'required' => 'required']); !!}
				</div>
				<div class="form-group">
					{!! Form::textarea('desc_pengeluaran_kasbon', null, ['class' => 'form-control', 'id' => 'desc_pengeluaran_kasbon', 'placeholder' => 'Deskripsi', 'required' => 'required']); !!}
				</div>
			</div>

			<div class="modal-footer">
				<button class="btn btn-primary">simpan</button>
			</div>
			{!! Form::close() !!}

		</div>
	</div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="setoran_modal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Input Setoran</h4>
			</div>
			{!! Form::open(['url' => action('SellPosController@inputSetoran'), 'method' => 'post', 'enctype' => 'multipart/form-data']) !!}
			<div class="modal-body">
				{!! Form::hidden('user_id', $user_id, ['class' => 'form-control', 'id' => 'user_id']); !!}
		        <div class="row">
		        	<div class="col-sm-6">
		        		<div class="form-group">
		        			<label>Total Uang Tunai</label>
							{!! Form::number('jml_tunai', 0, ['class' => 'form-control', 'id' => 'jml_tunai', 'placeholder' => 'Jumlah Pengeluaran', 'required' => 'required', 'onkeyup'=>'cekTunai(this.value)']); !!}
						</div>
		        	</div>
		        	<div class="col-sm-6">
		        		<div class="form-group">
		        			<label>Total Non Tunai</label>
							{!! Form::number('jml_non_tunai', 0, ['class' => 'form-control', 'id' => 'jml_non_tunai', 'placeholder' => 'Jumlah Pengeluaran', 'required' => 'required', 'onkeyup'=>'cekNonTunai(this.value)']); !!}
						</div>
		        	</div>
		        	<div class="col-sm-6">
		        		<div class="form-group">
		        			<label>Total Petty</label>
							{!! Form::number('jml_petty', 0, ['class' => 'form-control', 'id' => 'jml_petty', 'placeholder' => 'Jumlah Pengeluaran', 'required' => 'required', 'onkeyup'=>'cekSisaPetty2(this.value)']); !!}
						</div>
		        	</div>
		        </div>
				<div class="form-group">
					<label>Jumlah Setor</label>
					{!! Form::number('jml_setoran', 0, ['readonly' => 'true', 'class' => 'form-control', 'id' => 'jml_setoran', 'placeholder' => 'Jumlah Pengeluaran', 'required' => 'required']); !!}
				</div>
				<div class="form-group">
					<label>Setor Melalui</label>
					{!! Form::select("method_payment", $payment_type_setor, null, ['class' => 'form-control col-md-12 payment_types_dropdown', 'required', 'id' => "method_payment", 'onchange'=>'selectMethodSetor(this.value)', 'style' => 'width:100%;']); !!}
				</div><br><br>
				<div class="form-group" id="input_ref_code">
					<label>Reference code</label>
					{!! Form::text('ref_code', null, ['class' => 'form-control', 'id' => 'ref_code', 'placeholder' => 'Reference code']); !!}
				</div>
				<div id="input_bank_account_number" hidden>		
					<div class="form-group">
						<label>Setor ke</label>
						{!! Form::text('to', null, ['class' => 'form-control', 'id' => 'to', 'placeholder' => 'atas nama']); !!}
					</div>
					<div class="form-group">
						<label>Nomor Akun Bank</label>
						{!! Form::text('bank_account_number', null, ['class' => 'form-control', 'id' => 'bank_account_number', 'placeholder' => 'Nomor Akun Bank']); !!}
					</div>
				</div>
				<div class="form-group">
					<label>Deskripsi</label>
					{!! Form::textarea('desc_setoran', null, ['class' => 'form-control', 'id' => 'desc_setoran', 'placeholder' => 'Deskripsi', 'required' => 'required']); !!}
				</div>
				<div class="form-group">
					{!! Form::label('bukti_setor', 'Bukti Upload :') !!}
					{!! Form::file('bukti_setor', ['class' =>'form-control',  'accept' => 'image/*']); !!}
				</div>
			</div>

			<div class="modal-footer">
				<button class="btn btn-primary">simpan</button>
			</div>
			{!! Form::close() !!}

		</div>
	</div>
</div>

<div class="modal fade" tabindex="-1" role="dialog" id="antrian_modal">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
		        <button type="button" class="close" data-dismiss="modal">&times;</button>
		        <h4 class="modal-title">Daftar Janji</h4>
	      </div>
			<div class="modal-body">
		        <div class="">
		        	@if(is_null($default_location))
					<div class="form-group">
						{!! Form::label('id_lokasi', 'Pilih Lokasi :') !!}
			            {!! Form::select('id_lokasi', 
			                        $location_option, $location_id, ['class' => 'form-control', 'placeholder' => 'Pilih Lokasi', 'onchange' => 'getAntrian(this.value)']); !!}
			            <br>
			        </div>
			        @endif
			        <table class="table table-bordered table-striped" id="antrian_list">
			          <thead>
			            <tr>
			              <th>Nama</th>
			              <th>Janji</th>
			              <th>Kursi</th>
			              <th>Action</th>
			            </tr>
			          </thead>
			        </table>
		        </div>
		        	<p>
					  <a class="btn btn-primary" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
					    Atur Booking
					  </a>
					</p>
					<div class="collapse" id="collapseExample">
					  <div class="row">
					    <form class="" method="post" action="" id="set_booking">
					    	<hr>
							<div class="form-group col-sm-6" @if($default_location != null) hidden @endif>
								{!! Form::label('id_lokasi', 'Pilih Lokasi :') !!}
					            {!! Form::select('id_lokasi', 
					                        $location_option, $location_id, ['class' => 'form-control', 'placeholder' => 'Pilih Lokasi', 'required']); !!}
					        </div>
					    	<div class="form-group col-sm-6">
					    		<label>Jam Awal Booking :</label>
				    			<input type="time" id="open_book" value="{{ $get_set_book != null ? $get_set_book->open_book : '' }}" name="open_book" class="form-control" required>
					    	</div>
					    	<div class="form-group col-sm-6">
					    		<label>Jam Tutup Booking :</label>
				    			<input type="time" id="close_book" value="{{ $get_set_book != null ? $get_set_book->close_book : '' }}" name="close_book" class="form-control" required>
					    	</div>
					    	<div class="form-group col-sm-6">
					    		<label>Total Booking dalam 1 Jam :</label>
				    			<input type="number" id="total_booking" min="1" value="{{ $get_set_book != null ? $get_set_book->total_book_hours : 0 }}" name="total_booking" class="form-control" required placeholder="Customer">
					    	</div>
					    	<div class="form-group col-sm-12">
							    <input type="checkbox" class="form-check-input" id="activeBook" {{ $get_set_book != null ? ($get_set_book->is_active == 0  ? '' : 'checked') : '' }} name="activeBook" value="1">
							    <label class="form-check-label" for="activeBook"> Aktifkan Layanan Booking</label>
						    </div>
						    <div class="form-group col-sm-2">
								<button class="btn btn-success">Simpan</button>
						    </div>
					    </form>
					  </div>
					</div>
			</div>

			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
			</div>
			
		</div>
	</div>
</div>
@stop

@section('javascript')
	<script>
	var sisaTunai=0;
	var sisaNonTunai=0;
	var sisaPetty=0;
	var location_exist={{($location_id != null ? true : false)}};
	var promo=@php print_r($json_promo)@endphp;
	</script>
	<script>
	function getPegawai(val){
		var select=$('#pegawai_id');
		select.empty();
		select.append('<option value="">Pilih Pegawai</option>');
		$.ajax({
			type: "GET",
			url: "{{URL::to('sells/get_pegawai_by_location')}}"+'/'+val, //json get site
			async : false,
			dataType : 'json',
			success: function(response){
			  arrData = response['data'];
		      for(i = 0; i < arrData.length; i++){
		      		select.append('<option value="'+arrData[i]['id_pegawai']+'">'+arrData[i]['nama_pegawai']+'</option>');
		      }
			}
		});
	}
	function cekTunaiPengeluaran(val){
		if (val > sisaTunai) {
			alert('Inputan melebihi jumlah yang tersedia');
			$('#jml_tunai_pengeluaran').val(0);
		}
		cekSisaPetty();
	}
	function cekPettyPengeluaran(val){
		if (val > sisaPetty) {
			alert('Inputan melebihi jumlah yang tersedia');
			$('#jml_petty_pengeluaran').val(0);
		}
		cekSisaPetty();
	}

	function cekTunai(val){
		if (val > sisaTunai) {
			alert('Inputan melebihi jumlah yang tersedia');
			$('#jml_tunai').val(0);
		}
		total_setor();
	}
	function cekNonTunai(val){
		if (val > sisaNonTunai) {
			alert('Inputan melebihi jumlah yang tersedia');
			$('#jml_non_tunai').val(0);
		}
		total_setor();
	}
	function cekTunaiPetty(val){
		if (val > sisaTunai) {
			alert('Inputan melebihi jumlah yang tersedia');
			$('#jml_tunai_petty').val(0);
		}
		total_petty();
	}
	function cekNonTunaiPetty(val){
		if (val > sisaNonTunai) {
			alert('Inputan melebihi jumlah yang tersedia');
			$('#jml_non_tunai_petty').val(0);
		}
		total_petty();
	}
	function total_setor(){
		var tunai=$('#jml_tunai').val();
		var nontunai=$('#jml_non_tunai').val();
		var petty=$('#jml_petty').val();
		$('#jml_setoran').val(parseInt(tunai)+parseInt(nontunai)+parseInt(petty));
	}
	function total_petty(){
		var tunai=$('#jml_tunai_petty').val();
		var nontunai=$('#jml_non_tunai_petty').val();
		$('#jml_pengeluaran_petty').val(parseInt(tunai)+parseInt(nontunai));
	}

	function cekTunaiKasbon(val){
		if (val > sisaTunai) {
			alert('Inputan melebihi jumlah yang tersedia');
			$('#jml_tunai_kasbon').val(0);
		}
		total_kasbon();
	}
	function cekNonTunaiKasbon(val){
		if (val > sisaNonTunai) {
			alert('Inputan melebihi jumlah yang tersedia');
			$('#jml_non_tunai_kasbon').val(0);
		}
		total_kasbon();
	}
	function total_kasbon(){
		var tunai=$('#jml_tunai_kasbon').val();
		var nontunai=$('#jml_non_tunai_kasbon').val();
		$('#jml_pengeluaran_kasbon').val(parseInt(tunai)+parseInt(nontunai));
	}

	function selectMethodSetor(val){
		if (val == 'card') {
			$('#input_ref_code').show();
			$('#input_bank_account_number').hide();
		}else if (val == 'bank_transfer') {
			$('#input_ref_code').hide();
			$('#input_bank_account_number').show();
		}else{
			$('#input_ref_code').hide();
			$('#input_bank_account_number').hide();
		}
	}
	function cekSisaPetty(){
		// if (val > sisaPetty) {
		// 	alert('Jumlah inputan melebihi sisa petty cash');
		// 	$('#jml_pengeluaran').val(0);
		// }
		var tunai=$('#jml_tunai_pengeluaran').val();
		var petty=$('#jml_petty_pengeluaran').val();
		$('#jml_pengeluaran').val(parseInt(tunai)+parseInt(petty));
	}
	function cekSisaPetty2(val){
		if (val > sisaPetty) {
			alert('Jumlah inputan melebihi sisa petty cash');
			$('#jml_petty').val(0);
		}
		total_setor();
	}

	</script>
	<script src="{{ asset('js/pos.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/printer.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
	<script src="{{ asset('js/opening_stock.js?v=' . $asset_v) }}"></script>
	<script>
	function reset_pos_form2(){

		// //If on edit page then redirect to Add POS page
		// if($('form#edit_pos_sell_form').length > 0){
		// 	setTimeout(function() {
		// 		window.location = $("input#pos_redirect_url").val();
		// 	}, 4000);
		// 	return true;
		// }
		
		// if(pos_form_obj[0]){
		// 	pos_form_obj[0].reset();
		// }
		// if(sell_form[0]){
		// 	sell_form[0].reset();
		// }

		$('tr.product_row').remove();
		$('span.total_quantity, span.price_total, span#total_discount, span#order_tax, span#total_payable, span#shipping_charges_amount').text(0);
		$('span.total_payable_span', 'span.total_paying', 'span.balance_due').text(0);

		$('#modal_payment').find('.remove_payment_row').each( function(){
			$(this).closest('.payment_row').remove();
		});

		//Reset discount
		__write_number($('input#discount_amount'), $('input#discount_amount').data('default'));
		$('input#discount_type').val($('input#discount_type').data('default'));

		//Reset tax rate
		$('input#tax_rate_id').val($('input#tax_rate_id').data('default'));
		__write_number($('input#tax_calculation_amount'), $('input#tax_calculation_amount').data('default'));

		$('select.payment_types_dropdown').val('cash').trigger('change');
		$('#price_group').trigger('change');

		//Reset shipping
		__write_number($('input#shipping_charges'), $('input#shipping_charges').data('default'));
		$('input#shipping_details').val($('input#shipping_details').data('default'));

		if($('input#is_recurring').length > 0){
			$('input#is_recurring').iCheck('update');
		};
		var location_id=$('input#location_id').val();
    	get_promo_list(location_id);
    	
	    $(document).trigger('sell_form_reset');
	}
	</script>


	<script type="text/javascript">
		$(function () {
			$('[data-toggle="popover1"]').popover()
		})
		$(document).ready(function(){
		    $('#id_customer').select2({
		        ajax: {
		            url: '/contacts/customers',
		            dataType: 'json',
		            delay: 250,
		            data: function(params) {
		                return {
		                    q: params.term, // search term
		                    page: params.page,
		                };
		            },
		            processResults: function(data) {
		                return {
		                    results: data,
		                };
		            },
		        },
		        templateResult: function (data) { 
		            return data.text + (data.customer_group != null ? '&nbsp;&nbsp;<span class="label label-success">'+data.customer_group+'</span>' : '' ) +"<br>" + LANG.mobile + ": " + data.mobile; 
		        },
		        minimumInputLength: 1,
		        language: {
		            noResults: function() {
		                var name = $('#id_customer')
		                    .data('select2')
		                    .dropdown.$search.val();
		                return (
		                    '<button type="button" data-name="' +
		                    name +
		                    '" class="btn btn-link add_new_customer"><i class="fa fa-plus-circle fa-lg" aria-hidden="true"></i>&nbsp; ' +
		                    __translate('add_name_as_new_customer', { name: name }) +
		                    '</button>'
		                );
		            },
		        },
		        escapeMarkup: function(markup) {
		            return markup;
		        },
		    });
			$('#id_customer').on('select2:select', function(e) {
		        var data = e.params.data;
		        $('#customer_group_id3').val(data.customer_group_id);
		        $('#mobile3').val(data.mobile);
		        $('#birthday2').val(data.birthday);
		        $('#member_code').val(data.member_code);
		        $('#exp_member_date').val(data.exp_member_date);
		    });
			listAntrian();
			$('#set_booking').on("submit", function(e){
				e.preventDefault();
				var data=$('#set_booking').serialize();
				$.ajax({
					url : "{{URL::to('sells/setBooking')}}",
					data : data,
					type : 'POST',
					dataType : 'json',
					success : function(response){
						arrData=response;
						if (arrData != '') {
							getAntrian(arrData['location_id']);
						}
					}
				})
			});
			$('#add_customer_form').on("submit", function(e){
				e.preventDefault();
				var data=$('#add_customer_form').serialize();
				console.log(data);
				$.ajax({
					url : "{{action('ContactController@store')}}",
					data : data,
					type : 'POST',
					dataType : 'json',
					success : function(result){
						if (result.success == true) {
                            $('select#customer_id').append(
                                $('<option>', { value: result.data.id, text: result.data.name })
                            );
                            $('select#customer_id')
                                .val(result.data.id)
                                .trigger('change');
                            $('#contact_number').val(result.data.mobile);
                            var member=(result.cg != null ? result.cg.name : '');
                            if (member.toLowerCase() == 'member') {
                                $('#contact_member').prop('checked', true);
                            }else{
                                $('#contact_member').prop('checked', false);
                            }
                            $('#collapseCustomer').collapse('hide');
                            toastr.success(result.msg);
                            toastr.success(result.msg);
                            var today = new Date();
							var dd = String(today.getDate()).padStart(2, '0');
							var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
							var yyyy = today.getFullYear();

							today = yyyy + '-' + mm + '-' + dd;
							console.log(today)
                            if (result.data.exp_member_date == today && result.data.customer_group_id != null) {
                            	toastr.error('Member ini hampir kadaluarsa, harap diperpanjang');
                            }
                        } else {
                            toastr.error(result.msg);
                        }
					}
				})
			});
			$('#edit_customer_form').on("submit", function(e){
				e.preventDefault();
				var data=$('#edit_customer_form').serialize();
				var id_customer=$('#id_customer').val();
				$.ajax({
					url : "{{URL::to('contacts')}}"+'/'+id_customer,
					data: data,
					type : 'put',
					dataType : 'json',
					success : function(result){
						if (result.success == true) {
                            $('select#customer_id').append(
                                $('<option>', { value: result.data.id, text: result.data.name })
                            );
                            $('select#customer_id')
                                .val(result.data.id)
                                .trigger('change');
                            $('#contact_number').val(result.data.mobile);
                            var member=(result.cg != null ? result.cg.name : '');
                            if (member.toLowerCase() == 'member') {
                                $('#contact_member').prop('checked', true);
                            }else{
                                $('#contact_member').prop('checked', false);
                            }
                            $('#collapseEditCustomer').collapse('hide');
                            toastr.success(result.msg);
                            var today = new Date();
							var dd = String(today.getDate()).padStart(2, '0');
							var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
							var yyyy = today.getFullYear();

							today = yyyy + '-' + mm + '-' + dd;
                            if (result.data.exp_member_date == today && result.data.customer_group_id != null) {
                            	toastr.error('Member ini hampir kadaluarsa, harap diperpanjang');
                            }
                        } else {
                            toastr.error(result.msg);
                        }
					}
				})
			});
		});
		
		function listAntrian(){
			m = $('#antrian_list').DataTable();
			m.clear().draw(false);
			$.ajax({
			  type: "GET",
			  url: "{{URL::to('sells/antrian')}}", //json get site
			  dataType : 'json',
			  success: function(response){
			      arrData = response['data'];
			      for(i = 0; i < arrData.length; i++){
			          m.row.add([
			              '<div class="text-left">'+arrData[i]['name']+'</div>',
			              '<div class="text-left">'+formatTime(arrData[i]['time'])+' - '+formatTime(arrData[i]['time_until'])+'</div>',
			              '<div class="text-left">'+arrData[i]['total_book']+' Kursi</div>',
			              '<div class="text-center">'+
			              '<button data-id="'+arrData[i]['id']+'" onclick="checkListAntrian(this)" '+(arrData[i]['is_done'] == 0 ? '' : 'disabled')+' class="btn waves-effect waves-light btn-xs '+(arrData[i]['is_done'] == 0 ? 'btn-success' : 'btn-warning')+'"><i class="fa fa-check"></i></button> '+
			              '</div>'
			          ]).draw(false);
						// $('#antrian_list < tbody').append(td);
			      }
			  }
			});
		}
		function getAntrian(val){
			m = $('#antrian_list').DataTable();
			m.clear().draw(false);
			$.ajax({
			  type: "GET",
			  url: "{{URL::to('sells/antrian')}}", //json get site
			  dataType : 'json',
			  data : {location_id : val},
			  success: function(response){
			      arrData = response['data'];
			      for(i = 0; i < arrData.length; i++){
			          m.row.add([
			              '<div class="text-left">'+arrData[i]['name']+'</div>',
			              '<div class="text-left">'+formatTime(arrData[i]['time'])+' - '+formatTime(arrData[i]['time_until'])+'</div>',
			              '<div class="text-left">'+arrData[i]['total_book']+' Kursi</div>',
			              '<div class="text-center">'+
			              '<button data-id="'+arrData[i]['id']+'" onclick="checkListAntrian(this)" '+(arrData[i]['is_done'] == 0 ? '' : 'disabled')+' class="btn waves-effect waves-light btn-xs '+(arrData[i]['is_done'] == 0 ? 'btn-success' : 'btn-warning')+'"><i class="fa fa-check"></i></button> '+
			              '</div>'
			          ]).draw(false);
						// $('#antrian_list < tbody').append(td);
			      }
			  }
			});
		}
		function checkListAntrian(eq){
			// console.log($(eq).data('id'));
			var id=$(eq).data('id');
			$.ajax({
			      type: "GET",
			      url: "{{URL::to('sells/cek-list-antrian')}}"+'/'+id, //json get site
			      async : false,
			      dataType : 'json',
			      success: function(response){
			          listAntrian();
			      }
			  });
		}
	  function formatTime(date) {
	    var temp=date.split(':');

	    return temp[0] + ':' + temp[1];
	  }
	  function cekMobile2(value){
	    $.ajax({
	      url : '/contacts/cek-mobile',
	      data : {telp: value},
	      type : 'POST', 
	      dataType : 'json',
	      success : function(response){
	        if (response == 1) {
	          alert('nomor yang anda input sudah ada, mohon input nomor yang lain');
	          $('#mobile2').val('');
	          $('#mobile2').focus();
	        }
	      }
	    })
	  }
	  	  
	  function getPromo(val){
	  	for (var i = 0; i < promo.length; i++) {
	  		if (val == promo[i]['id']) {
	  			$('#discount_type_modal').prop('disabled', 'disabled');
	  			$('#discount_type_modal').val($('input#discount_type').data('default'));
	  			$('input#discount_type').val($('input#discount_type').data('default'));
		        $('input#discount_amount_modal').val(promo[i]['promo_diskon']);
		        $('input#discount_amount').val(promo[i]['promo_diskon']);
	  		}else if(val == 0){
	  			$('#discount_type_modal').prop('disabled', false);
	  			$('#discount_type_modal').val($('input#discount_type').data('default'));
	  			$('input#discount_type').val($('input#discount_type').data('default'));
		        $('input#discount_amount_modal').val(0);
		        $('input#discount_amount').val(0);
	  		}
	  	};
	  }
	 //  function cekDiskon(){
	 //  	var a=$('[id^=line_discount_amount]');
	 //  	$('#discount_type_modal').prop('disabled', false);
	 //  	$('input#discount_amount_modal').prop('readonly', false);
		// $('#promo_id').prop('disabled', false);
		// $('#discount_type_modal').val($('input#discount_type').data('default'));
		// $('input#discount_type').val($('input#discount_type').data('default'));
		// $('input#discount_amount_modal').val(0);
		// $('input#discount_amount').val(0);
	 //  	for (var i = 0; i < a.length; i++) {
	 //  		var diskon=a.eq(i).val();
	 //  		console.log(diskon);
	 //  		if (diskon > 0) {
		// 	  	$('#discount_type_modal').prop('disabled', 'disabled');
		// 	  	$('input#discount_amount_modal').prop('readonly', true);
		// 	  	$('#promo_id').prop('disabled', 'disabled');
	 //  		}
	 //  	}
	 //  }
	</script>
	@include('sale_pos.partials.keyboard_shortcuts')

	<!-- Call restaurant module if defined -->
    @if(in_array('tables' ,$enabled_modules) || in_array('modifiers' ,$enabled_modules) || in_array('service_staff' ,$enabled_modules))
    	<script src="{{ asset('js/restaurant.js?v=' . $asset_v) }}"></script>
    @endif
@endsection
