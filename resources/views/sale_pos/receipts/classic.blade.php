<!-- business information here -->

<div class="row">
<!-- 
<script type="text/javascript">
	window.print();
</script>
 -->
	<!-- Logo -->
	@if(!empty($receipt_details->logo))
		<img src="{{$receipt_details->logo}}" class="img img-responsive center-block">
	@endif

	<!-- Header text -->
	@if(!empty($receipt_details->header_text))
		<div class="col-xs-12">
			{!! $receipt_details->header_text !!}
		</div>
	@endif

	<!-- business information here -->
	<div class="col-xs-12 text-center" style="font-size:10px">
		<!-- <h2 class="text-center"> -->
			<!-- Shop & Location Name  -->
		<!-- 	@if(!empty($receipt_details->display_name))
				{{$receipt_details->display_name}}
			@endif
		</h2>
 -->
		<!-- Address -->
		<p style="font-size:10px">
		@if(!empty($receipt_details->address))
				<small class="text-center">
				{!! $receipt_details->address !!}
				</small>
		@endif
		@if(!empty($receipt_details->contact))
			<br/>{{ $receipt_details->contact }}
		@endif	
		@if(!empty($receipt_details->contact) && !empty($receipt_details->website))
			, 
		@endif
		@if(!empty($receipt_details->website))
			{{ $receipt_details->website }}
		@endif
		@if(!empty($receipt_details->location_custom_fields))
			<br>{{ $receipt_details->location_custom_fields }}
		@endif
		</p>
		<p>
		@if(!empty($receipt_details->sub_heading_line1))
			{{ $receipt_details->sub_heading_line1 }}
		@endif
		@if(!empty($receipt_details->sub_heading_line2))
			<br>{{ $receipt_details->sub_heading_line2 }}
		@endif
		@if(!empty($receipt_details->sub_heading_line3))
			<br>{{ $receipt_details->sub_heading_line3 }}
		@endif
		@if(!empty($receipt_details->sub_heading_line4))
			<br>{{ $receipt_details->sub_heading_line4 }}
		@endif		
		@if(!empty($receipt_details->sub_heading_line5))
			<br>{{ $receipt_details->sub_heading_line5 }}
		@endif
		</p>
		<p>
		@if(!empty($receipt_details->tax_info1))
			{{ $receipt_details->tax_label1 }} {{ $receipt_details->tax_info1 }}
		@endif

		@if(!empty($receipt_details->tax_info2))
			{{ $receipt_details->tax_label2 }} {{ $receipt_details->tax_info2 }}
		@endif
		</p>

		<!-- Title of receipt -->
		<!-- @if(!empty($receipt_details->invoice_heading))
			<h5 class="text-center">
				{!! $receipt_details->invoice_heading !!}
			</h5>
		@endif -->

		<table width="100%">
        	<tr>
        		<td align="left" style="border-bottom:1px solid black">No. Faktur</td>
        		<td align="left" style="border-bottom:1px solid black">: {{$receipt_details->invoice_no}}</td>
        	</tr>
        	<tr>
        		<td align="left">{{$receipt_details->date_label}}</td>
        		<td align="left">: {{$receipt_details->invoice_date}}</td>
        	</tr>
        	<tr>
        		<td align="left">{{$receipt_details->sales_person_label}}</td>
        		<td align="left">: {{$receipt_details->sales_person}}</td>
        	</tr>
        	<tr>
        		<td align="left">{{$receipt_details->customer_label}}</td>
        		<td align="left">: {{$receipt_details->customer_name}}</td>
        	</tr>
        	<tr>
        		<td align="left">Barberman</td>
        		<td align="left">: {{$receipt_details->nama_pegawai}}</td>
        	</tr>

        	@php
        	function splitMethod($val){
        		$data=explode(' ' ,$val);
        		return $data[0];
        	}
        	@endphp
        	@if(!empty($receipt_details->payments))
				@foreach($receipt_details->payments as $payment)
					<tr>
						<td align="left">@lang('Payment')</td>
						<td align="left">: {{$payment['method']}}</td>
					</tr>
				@endforeach
			@endif
        </table>
		<!-- Invoice  number, Date  -->
		<p style="width: 100% !important" class="word-wrap">
			<span class="pull-left text-left word-wrap">
				<!-- @if(!empty($receipt_details->invoice_no_prefix))
					{!! $receipt_details->invoice_no_prefix !!}
				@endif
				{{$receipt_details->invoice_no}}
 -->
				<!-- Table information-->
<!-- 		        @if(!empty($receipt_details->table_label) || !empty($receipt_details->table))
		        	<br/>
					<span class="pull-left text-left">
						@if(!empty($receipt_details->table_label))
							{!! $receipt_details->table_label !!} : 
						@endif
						{{$receipt_details->table}}
 -->
						<!-- Waiter info -->
<!-- 					</span>
		        @endif
 -->		        
				<!-- customer info -->
<!-- 				@if(!empty($receipt_details->customer_name))
					<br/>
					{{ $receipt_details->customer_label }} :  {{ $receipt_details->customer_name }}
				@endif -->
				<!-- @if(!empty($receipt_details->customer_info))
					{!! $receipt_details->customer_info !!}
				@endif -->
				<!-- @if(!empty($receipt_details->client_id_label))
					<br/>
					{{ $receipt_details->client_id_label }} :  {{ $receipt_details->client_id }}
				@endif
				@if(!empty($receipt_details->customer_tax_label))
					<br/>
					{{ $receipt_details->customer_tax_label }} :  {{ $receipt_details->customer_tax_number }}
				@endif
				@if(!empty($receipt_details->customer_custom_fields))
					<br/>{!! $receipt_details->customer_custom_fields !!}
				@endif
				@if(!empty($receipt_details->sales_person_label))
					<br/>
					{{ $receipt_details->sales_person_label }} :  {{ $receipt_details->sales_person }}
				@endif
				<br/>
				{{$receipt_details->date_label}} :  {{$receipt_details->invoice_date}} -->
			<!-- </span> -->

			<!-- <span class="pull-right text-left"> -->
			<!-- <span> -->
				<!-- @if(!empty($receipt_details->serial_no_label) || !empty($receipt_details->repair_serial_no))
					@if(!empty($receipt_details->serial_no_label))
					<br>
						{!! $receipt_details->serial_no_label !!}
					@endif
					{{$receipt_details->repair_serial_no}}
					<br>
		        @endif
				@if(!empty($receipt_details->repair_status_label) || !empty($receipt_details->repair_status))
					@if(!empty($receipt_details->repair_status_label))
					<br>
						{!! $receipt_details->repair_status_label !!}
					@endif
					{{$receipt_details->repair_status}}
		        @endif
		        
		        @if(!empty($receipt_details->repair_warranty_label) || !empty($receipt_details->repair_warranty))
					@if(!empty($receipt_details->repair_warranty_label))
					<br>
						{!! $receipt_details->repair_warranty_label !!}
					@endif
					{{$receipt_details->repair_warranty}}
		        @endif
		         -->
				<!-- Waiter info -->
				<!-- @if(!empty($receipt_details->service_staff_label) || !empty($receipt_details->service_staff))
					@if(!empty($receipt_details->service_staff_label))
		        	<br/>
						{!! $receipt_details->service_staff_label !!}
					@endif
					{{$receipt_details->service_staff}}
		        @endif -->
			</span>
		</p>
	</div>
	
	@if(!empty($receipt_details->defects_label) || !empty($receipt_details->repair_defects))
		<div class="col-xs-12">
			@if(!empty($receipt_details->defects_label))
			<br>
				{!! $receipt_details->defects_label !!}
			@endif
			{{$receipt_details->repair_defects}}
		</div>
    @endif
	<!-- /.col -->
</div>


<div class="row" style="font-size:10px">
	<div class="col-xs-12">
		<br/>
		<!-- <table class="table table-responsive"> -->
		<table width="100%" style="border-bottom:1px solid rgba(0,0,255,0.5)">
			<thead style="border-bottom:1px solid rgba(0,0,255,0.5)">
				<tr>
					<th>{{$receipt_details->table_product_label}}</th>
					<th>{{$receipt_details->table_qty_label}}</th>
					<th class="text-right">{{$receipt_details->table_unit_price_label}}</th>
					<th class="text-right">{{$receipt_details->table_subtotal_label}}</th>
				</tr>
			</thead>
			<tbody>
				@forelse($receipt_details->lines as $line)
					@php
						$line_discount = ($line['line_discount'] != 0 ? $line['line_discount'] : '0');
						$discount_item=explode(' ', $line_discount);
					@endphp
					<tr>
						<td style="word-break: break-all;" colspan="4">
						<!-- <td colspan="4"> -->
							@if(!empty($line['image']))
								<img src="{{$line['image']}}" alt="Image" width="50" style="float: left; margin-right: 10px;">
							@endif
                            {{$line['name']}} {{$line['variation']}} {{ $line_discount != 0 ? 'Discount '.$line_discount : ''}}
                            @php /* @endphp
                            @if(!empty($line['sub_sku'])), {{$line['sub_sku']}} @endif @if(!empty($line['brand'])), {{$line['brand']}} @endif @if(!empty($line['cat_code'])), {{$line['cat_code']}}@endif
                            @if(!empty($line['product_custom_fields'])), {{$line['product_custom_fields']}} @endif
                            @if(!empty($line['sell_line_note']))({{$line['sell_line_note']}}) @endif 
                            @if(!empty($line['lot_number']))<br> {{$line['lot_number_label']}}:  {{$line['lot_number']}} @endif 
                            @if(!empty($line['product_expiry'])), {{$line['product_expiry_label']}}:  {{$line['product_expiry']}} @endif 
                            @php */ @endphp
                        </td>
                    </tr>
                    @foreach($line['detail'] as $value)
                    <!-- <tr>
                    	<td colspan="4">- {{$value->item_name}} @if($value->default_sell_price > $value->amount)(Rp. <strike>{{number_format($value->default_sell_price, 0, '.', '.')}}</strike>@endif {{number_format($value->amount, 0, ',', '.')}})</td>
					</tr> -->
                    <!-- <tr>
                    	<td></td>
						<td align="left">{{$line['quantity']}} x </td>
						<td class="text-right">Rp. {{number_format($value->amount, 0, '.', '.')}}</td>
						<td align="right">Rp. {{number_format(($value->amount * $line['quantity']), 0, '.', '.')}}</td>
					</tr> -->
                    @endforeach
                    <tr>
                    	<td></td>
						<td align="left">{{$line['quantity']}} x @php /* @endphp {{$line['units']}} @php */ @endphp</td>
						<td class="text-right">{{$line['unit_price_before_discount']}}</td>
						<td align="right">{{$line['line_total']}}</td>
					</tr>
					@if(!empty($line['modifiers']))
						@foreach($line['modifiers'] as $modifier)
							<tr>
								<td colspan="4">
		                            {{$modifier['name']}} {{$modifier['variation']}} 
		                            @if(!empty($modifier['sub_sku'])), {{$modifier['sub_sku']}} @endif @if(!empty($modifier['cat_code'])), {{$modifier['cat_code']}}@endif
		                            @if(!empty($modifier['sell_line_note']))({{$modifier['sell_line_note']}}) @endif 
		                        </td>
		                    </tr>
		                    <tr>
		                    	<td></td>
								<td align="left">{{$modifier['quantity']}} {{$modifier['units']}} </td>
								<td class="text-right">{{$modifier['unit_price_inc_tax']}}</td>
								<td align="right">{{$modifier['line_total']}}</td>
							</tr>
						@endforeach
					@endif
				@empty
					<!-- <tr>
						<td colspan="4">&nbsp;</td>
					</tr> -->
				@endforelse
			</tbody>
		</table>
	</div>
</div>

<div class="row">
	<!-- <br/> -->
	<!-- <div class="col-md-12"><hr/></div> -->
	<!-- <br/> -->

	<div class="col-xs-12">
        <div class="table-responsive">
          	<table style="font-size:10px" width="100%">
				<tbody >
					<tr>
						<td style="width:50%">
							{!! $receipt_details->subtotal_label !!}
						</td>
						<td></td>
						<td align="right" style="width:50%">
							{{$receipt_details->subtotal}}
						</td>
					</tr>
					
					<!-- Shipping Charges -->
					@if(!empty($receipt_details->shipping_charges))
						<tr>
							<td style="width:50%">
								{!! $receipt_details->shipping_charges_label !!}
							</td>
							<td></td>
							<td align="right" style="width:50%">
								{{$receipt_details->shipping_charges}}
							</td>
						</tr>
					@endif

					<!-- Discount -->
					@if( !empty($receipt_details->discount) )
						<tr>
							<td>
								{!! $receipt_details->discount_label !!}
							</td>
							<td></td>
							<td align="right" style="width:50%">
								(-) {{$receipt_details->discount}}
							</td>
						</tr>
					@endif

					<!-- Tax -->
					@if( !empty($receipt_details->tax) )
						<tr>
							<td>
								{!! $receipt_details->tax_label !!}
							</td>
							<td></td>
							<td align="right" style="width:50%">
								(+) {{$receipt_details->tax}}
							</td>
						</tr>
					@endif

					<!-- Total -->
					<tr>
						<td>
							{!! $receipt_details->total_label !!}
						</td>
						<td></td>
						<td align="right">
							{{$receipt_details->total}}
						</td>
					</tr>
				</tbody>
        	</table>
        </div>
    </div>
	<div class="col-xs-12">

		<!-- <table class="table table-condensed" style="font-size:10px"> -->
		<table style="font-size:10px;" width="100%">
			<thead style="border-bottom:1px solid rgba(0,0,255,0.5)">
				
			@if(!empty($receipt_details->payments))
				@foreach($receipt_details->payments as $payment)
					<tr>
						<td>{{$payment['method']}}</td>
						<td></td>
						<td align="right">{{$payment['amount']}}</td>
						<!-- <td>{{$payment['date']}}</td> -->
					</tr>
				@endforeach
			@endif

			<!-- Total Paid-->
			@if(!empty($receipt_details->total_paid))
				<tr>
					<td>
						{!! $receipt_details->total_paid_label !!}
					</td>
					<td></td>
					<td align="right">
						{{$receipt_details->total_paid}}
					</td>
				</tr>
			@endif

			<!-- Total Due-->
			@if(!empty($receipt_details->total_due))
			<tr>
				<th>
					{!! $receipt_details->total_due_label !!}
				</th>
				<td></td>
				<td align="right">
					{{$receipt_details->total_due}}
				</td>
			</tr>
			@endif

			@if(!empty($receipt_details->all_due))
			<tr>
				<th>
					{!! $receipt_details->all_bal_label !!}
				</th>
				<td></td>
				<td align="right">
					{{$receipt_details->all_due}}
				</td>
			</tr>
			@endif
			</thead>
		</table>

		{{$receipt_details->additional_notes}}
	</div>

</div>
<br>
<!-- @if($receipt_details->show_barcode)
	<div class="row">
		<div class="col-xs-12">
			{{-- Barcode --}}
			<img class="center-block" src="data:image/png;base64,{{DNS1D::getBarcodePNG($receipt_details->invoice_no, 'C128', 2,30,array(39, 48, 54), true)}}">
		</div>
	</div>
@endif
 -->
@if(!empty($receipt_details->footer_text))
	<div class="row">
		<div class="col-xs-12">
			<center>{!! $receipt_details->footer_text !!}</center>
		</div>
	</div>
@endif 