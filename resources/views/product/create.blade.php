@extends('layouts.app')
@section('title', __('product.add_new_product'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>@lang('product.add_new_product')</h1>
    <!-- <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
        <li class="active">Here</li>
    </ol> -->
</section>

<style>
  .input-group-bahan.isHide{
    display: block;
  }
  .input-group-bahan.isHide .removeBahan{
    display: none;
  }
</style>
<!-- Main content -->
<section class="content">
{!! Form::open(['url' => action('ProductController@store'), 'method' => 'post', 
    'id' => 'product_add_form','class' => 'product_form', 'files' => true ]) !!}
    @component('components.widget', ['class' => 'box-primary'])
        <div class="row">
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('name', __('product.product_name') . ':*') !!}
              {!! Form::text('name', !empty($duplicate_product->name) ? $duplicate_product->name : null, ['class' => 'form-control', 'required',
              'placeholder' => __('product.product_name')]); !!}
          </div>
        </div>
        <div class="col-sm-4 @if(!session('business.enable_brand')) hide @endif">
          <div class="form-group">
            {!! Form::label('brand_id', __('product.brand') . ':') !!}
            <div class="input-group">
              {!! Form::select('brand_id', $brands, !empty($duplicate_product->brand_id) ? $duplicate_product->brand_id : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
            <span class="input-group-btn">
                <button type="button" @if(!auth()->user()->can('brand.create')) disabled @endif class="btn btn-default bg-white btn-flat btn-modal" data-href="{{action('BrandController@create', ['quick_add' => true])}}" title="@lang('brand.add_brand')" data-container=".view_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
              </span>
            </div>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('unit_id', __('product.unit') . ':*') !!}
            <div class="input-group">
              {!! Form::select('unit_id', $units, !empty($duplicate_product->unit_id) ? $duplicate_product->unit_id : session('business.default_unit'), ['class' => 'form-control select2', 'required']); !!}
              <span class="input-group-btn">
                <button type="button" @if(!auth()->user()->can('unit.create')) disabled @endif class="btn btn-default bg-white btn-flat btn-modal" data-href="{{action('UnitController@create', ['quick_add' => true])}}" title="@lang('unit.add_unit')" data-container=".view_modal"><i class="fa fa-plus-circle text-primary fa-lg"></i></button>
              </span>
            </div>
              
          </div>
        </div>

        <div class="clearfix"></div>

        <div class="col-sm-4 @if(!session('business.enable_category')) hide @endif">
          <div class="form-group">
            {!! Form::label('category_id', __('product.category') . ':') !!}
              {!! Form::select('category_id', $categories, !empty($duplicate_product->category_id) ? $duplicate_product->category_id : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
          </div>
        </div>

        <div class="col-sm-4 @if(!(session('business.enable_category') && session('business.enable_sub_category'))) hide @endif">
          <div class="form-group">
            {!! Form::label('sub_category_id', __('product.sub_category') . ':') !!}
              {!! Form::select('sub_category_id', $sub_categories, !empty($duplicate_product->sub_category_id) ? $duplicate_product->sub_category_id : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2']); !!}
          </div>
        </div>

        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('sku', __('product.sku') . ':') !!} @show_tooltip(__('tooltip.sku'))
            {!! Form::text('sku', null, ['class' => 'form-control',
              'placeholder' => __('product.sku')]); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('barcode_type', __('product.barcode_type') . ':*') !!}
              {!! Form::select('barcode_type', $barcode_types, !empty($duplicate_product->barcode_type) ? $duplicate_product->barcode_type : $barcode_default, ['class' => 'form-control select2', 'required']); !!}
          </div>
        </div>
        <div class="col-sm-4">
          <div class="form-group">
          <br>
            <label>
              {{-- {!! Form::checkbox('enable_stock', 1, !empty($duplicate_product) ? $duplicate_product->enable_stock : true, ['class' => 'input-icheck', 'id' => 'enable_stock']); !!}  --}}
              <input type="hidden" name="enable_stock" value='1'>
              {{-- <strong>@lang('product.manage_stock')</strong> --}}
            </label>
            {{-- @show_tooltip(__('tooltip.enable_stock')) <p class="help-block"><i>@lang('product.enable_stock_help')</i></p> --}}
          </div>
        </div>
        <div class="col-sm-4 @if(!empty($duplicate_product) && $duplicate_product->enable_stock == 0) hide @endif" id="alert_quantity_div">
          <div class="form-group">
            {{-- {!! Form::label('alert_quantity',  __('product.alert_quantity') . ':*') !!} @show_tooltip(__('tooltip.alert_quantity')) --}}
            {!! Form::hidden('alert_quantity', !empty($duplicate_product->alert_quantity) ? $duplicate_product->alert_quantity : 0 , ['class' => 'form-control', 'required',
            'placeholder' => __('product.alert_quantity'), 'min' => '0']); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-8">
          <div class="form-group">
            {!! Form::label('product_description', __('lang_v1.product_description') . ':') !!}
              {!! Form::textarea('product_description', !empty($duplicate_product->product_description) ? $duplicate_product->product_description : null, ['class' => 'form-control']); !!}
          </div>
        </div>
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('image', __('lang_v1.product_image') . ':') !!}
            {!! Form::file('image', ['id' => 'upload_image', 'accept' => 'image/*']); !!}
            <small><p class="help-block">@lang('purchase.max_file_size', ['size' => (config('constants.document_size_limit') / 1000000)]) <br> @lang('lang_v1.aspect_ratio_should_be_1_1')</p></small>
          </div>
        </div>
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('commission', 'Komisi' . ':') !!}
            {!! Form::number('commission', null , ['class' => 'form-control',
            'placeholder' => 'Komisi', 'min' => '0']); !!}
          </div>
        </div>
        <div class="col-sm-4">
              <div class="form-group">
                {!! Form::label('commission', 'Lokasi' . ':') !!}
                {!! Form::select('id_lokasi', 
                            $location_option, null, ['class' => 'form-control', 'placeholder' => 'Pilih Lokasi', 'required' => 'required']); !!}
              </div>
        </div>
        <div class="col-md-12">
          <div class="row">
            <div class="col-md-12" id="append-bahan">
              <div class="row row-bahan" data-index='0'>
                <div class="col-sm-4">
                  <div class="form-group">
                    <label for="">Nama Bahan</label>
                    <div class="input-group input-group-bahan isHide">
                      <span class="input-group-addon removeBahan"><a href="" onclick="removeBahan(event,this)">X</a></span>
                      <select name="id_bahan[]" id="" class="form-control" onchange="getSatuan(this)">
                        <option value="">Pilih Bahan</option>
                        @foreach ($bahan as $item)
                        <option data-satuan="{{$item->satuan}}" data-harga="{{$item->harga_bahan}}" value="{{$item->id_bahan}}">{{$item->nama_bahan}}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="form-group mb-3">
                    <label for="">Kebutuhan</label>
                    <div class="input-group">
                      <input type="number" class="form-control kebutuhanBahan" name="kebutuhan[]" placeholder="Kebutuhan"  onkeyup="getStok(this)">
                      <span class="input-group-addon satuanBahan">Satuan</span>
                    </div>                
                  </div>
                </div>
                <input type="hidden" class="form-control hpp" name="hpp[]" placeholder="" readonly>
                <input type="hidden" class="form-control hargaBahan" name="" placeholder="" readonly>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-sm-12">
              <div class="form-group">
                <button id="add-bahan">Tambah Bahan</button>
              </div>
            </div>
          </div>

        </div>
        <br>
        <div class="col-sm-12">
          <div class="form-group">
              <label>
              {!! Form::checkbox('is_paket', 1, false, ['class' => 'input-icheck', 'id' => 'is_paket', 'onclick' => 'alert(1)']); !!} <strong>Item Paket</strong>
              </label>
          </div>
        </div>
        
        <div id="col-item-paket" hidden>
          <div class="col-sm-12">
            <div class="form-group">
                <button id="add-item">Tambah</button>
            </div>
          </div>
          <div class="col-sm-6" id="remove-item-paket">
            <div class="form-group">
                  {!! Form::select('item_id[]', 
                              $product_option, null, ['class' => 'form-control', 'placeholder' => 'Pilih Item']); !!}
            </div>
          </div>
          <div class="col-sm-4">
            <div class="form-group">
                  {!! Form::number('item_price[]', null , ['class' => 'form-control hide',
                      'placeholder' => 'Price Item', 'min' => '0']); !!}
            </div>
          </div>
          <div class="col-sm-12">
            <div id="item-paket"></div>
          </div>
        </div>
        </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary hide'])
        <div class="row">
        @if(session('business.enable_product_expiry'))

            @if(session('business.expiry_type') == 'add_expiry')
              @php
                $expiry_period = 12;
                $hide = true;
              @endphp
            @else
              @php
                $expiry_period = null;
                $hide = false;
              @endphp
            @endif
          <div class="col-sm-4 @if($hide) hide @endif">
            <div class="form-group">
              <div class="multi-input">
                {!! Form::label('expiry_period', __('product.expires_in') . ':') !!}<br>
                {!! Form::text('expiry_period', !empty($duplicate_product->expiry_period) ? @num_format($duplicate_product->expiry_period) : $expiry_period, ['class' => 'form-control pull-left input_number',
                  'placeholder' => __('product.expiry_period'), 'style' => 'width:60%;']); !!}
                {!! Form::select('expiry_period_type', ['months'=>__('product.months'), 'days'=>__('product.days'), '' =>__('product.not_applicable') ], !empty($duplicate_product->expiry_period_type) ? $duplicate_product->expiry_period_type : 'months', ['class' => 'form-control select2 pull-left', 'style' => 'width:40%;', 'id' => 'expiry_period_type']); !!}
              </div>
            </div>
          </div>
        @endif

        <div class="col-sm-4">
          <div class="form-group">
          <br>
            <label>
              {!! Form::checkbox('enable_sr_no', 1, !(empty($duplicate_product)) ? $duplicate_product->enable_sr_no : false, ['class' => 'input-icheck']); !!} <strong>@lang('lang_v1.enable_imei_or_sr_no')</strong>
            </label> @show_tooltip(__('lang_v1.tooltip_sr_no'))
          </div>
        </div>

        <div class="clearfix"></div>

        <!-- Rack, Row & position number -->
        @if(session('business.enable_racks') || session('business.enable_row') || session('business.enable_position'))
          <div class="col-md-12">
            <h4>@lang('lang_v1.rack_details'):
              @show_tooltip(__('lang_v1.tooltip_rack_details'))
            </h4>
          </div>
          @foreach($business_locations as $id => $location)
            <div class="col-sm-3">
              <div class="form-group">
                {!! Form::label('rack_' . $id,  $location . ':') !!}
                
                @if(session('business.enable_racks'))
                  {!! Form::text('product_racks[' . $id . '][rack]', !empty($rack_details[$id]['rack']) ? $rack_details[$id]['rack'] : null, ['class' => 'form-control', 'id' => 'rack_' . $id, 
                    'placeholder' => __('lang_v1.rack')]); !!}
                @endif

                @if(session('business.enable_row'))
                  {!! Form::text('product_racks[' . $id . '][row]', !empty($rack_details[$id]['row']) ? $rack_details[$id]['row'] : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.row')]); !!}
                @endif
                
                @if(session('business.enable_position'))
                  {!! Form::text('product_racks[' . $id . '][position]', !empty($rack_details[$id]['position']) ? $rack_details[$id]['position'] : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.position')]); !!}
                @endif
              </div>
            </div>
          @endforeach
        @endif
        
        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('weight',  __('lang_v1.weight') . ':') !!}
            {!! Form::text('weight', !empty($duplicate_product->weight) ? $duplicate_product->weight : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.weight')]); !!}
          </div>
        </div>
        <!--custom fields-->
        <div class="clearfix"></div>
        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('product_custom_field1',  __('lang_v1.product_custom_field1') . ':') !!}
            {!! Form::text('product_custom_field1', !empty($duplicate_product->product_custom_field1) ? $duplicate_product->product_custom_field1 : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.product_custom_field1')]); !!}
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('product_custom_field2',  __('lang_v1.product_custom_field2') . ':') !!}
            {!! Form::text('product_custom_field2', !empty($duplicate_product->product_custom_field2) ? $duplicate_product->product_custom_field2 : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.product_custom_field2')]); !!}
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('product_custom_field3',  __('lang_v1.product_custom_field3') . ':') !!}
            {!! Form::text('product_custom_field3', !empty($duplicate_product->product_custom_field3) ? $duplicate_product->product_custom_field3 : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.product_custom_field3')]); !!}
          </div>
        </div>

        <div class="col-sm-3">
          <div class="form-group">
            {!! Form::label('product_custom_field4',  __('lang_v1.product_custom_field4') . ':') !!}
            {!! Form::text('product_custom_field4', !empty($duplicate_product->product_custom_field4) ? $duplicate_product->product_custom_field4 : null, ['class' => 'form-control', 'placeholder' => __('lang_v1.product_custom_field4')]); !!}
          </div>
        </div>
        <!--custom fields-->
        <div class="clearfix"></div>
        @include('layouts.partials.module_form_part')
      </div>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary'])
        <div class="row">

        <div class="col-sm-4 @if(!session('business.enable_price_tax')) hide @endif">
          <div class="form-group">
            {!! Form::label('tax', __('product.applicable_tax') . ':') !!}
              {!! Form::select('tax', $taxes, !empty($duplicate_product->tax) ? $duplicate_product->tax : null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2'], $tax_attributes); !!}
          </div>
        </div>

        <div class="col-sm-4 @if(!session('business.enable_price_tax')) hide @endif">
          <div class="form-group">
            {!! Form::label('tax_type', __('product.selling_price_tax_type') . ':*') !!}
              {!! Form::select('tax_type', ['inclusive' => __('product.inclusive'), 'exclusive' => __('product.exclusive')], !empty($duplicate_product->tax_type) ? $duplicate_product->tax_type : 'exclusive',
              ['class' => 'form-control select2', 'required']); !!}
          </div>
        </div>

        <div class="clearfix"></div>

        <div class="col-sm-4">
          <div class="form-group">
            {!! Form::label('type', __('product.product_type') . ':*') !!} @show_tooltip(__('tooltip.product_type'))
            {!! Form::select('type', ['single' => __('lang_v1.single'), 'variable' => __('lang_v1.variable')], !empty($duplicate_product->type) ? $duplicate_product->type : null, ['class' => 'form-control select2',
            'required', 'data-action' => !empty($duplicate_product) ? 'duplicate' : 'add', 'data-product_id' => !empty($duplicate_product) ? $duplicate_product->id : '0']); !!}
          </div>
        </div>

        <div class="form-group col-sm-11 col-sm-offset-1" id="product_form_part"></div>

        <input type="hidden" id="variation_counter" value="1">
        <input type="hidden" id="default_profit_percent" 
          value="{{ $default_profit_percent }}">

      </div>
    @endcomponent
    <div class="row">
    <div class="col-sm-12">
      <input type="hidden" name="submit_type" id="submit_type">
      <div class="text-center">
      <div class="btn-group">
        @if($selling_price_group_count)
          <button type="submit" value="submit_n_add_selling_prices" class="btn btn-warning submit_product_form">@lang('lang_v1.save_n_add_selling_price_group_prices')</button>
        @endif

        <button id="opening_stock_button" @if(!empty($duplicate_product) && $duplicate_product->enable_stock == 0) disabled @endif type="submit" value="submit_n_add_opening_stock" class="btn bg-purple submit_product_form">@lang('lang_v1.save_n_add_opening_stock')</button>

        <button type="submit" value="save_n_add_another" class="btn bg-maroon submit_product_form">@lang('lang_v1.save_n_add_another')</button>

        <button type="submit" value="submit" class="btn btn-primary submit_product_form">@lang('messages.save')</button>
      </div>
      
      </div>
    </div>
  </div>
{!! Form::close() !!}
  
</section>
<!-- /.content -->

@endsection

@section('javascript')
  @php $asset_v = env('APP_VERSION'); @endphp
  <script>

  $(document).ready(function() {
    $(document).on('ifChecked', 'input#is_paket', function() {
        $('div#col-item-paket').show();
    });
    $(document).on('ifUnchecked', 'input#is_paket', function() {
        $('div#col-item-paket').hide();
    });
    var max_fields      = 10; //maximum input boxes allowed
    var n = 1; //initlal text box count
    var wrapper_item          = $("#item-paket");
    var add_button_item      = $("#add-item");
    $(add_button_item).click(function(e){ //on add input button click
        e.preventDefault();
        
        var option_item = '<option value="">Pilih Item</option>';
        var alkes_option_js = @php print_r($product_option_js)@endphp;
        console.log('alkes_option_js');
        for(i=0;i<alkes_option_js.length;i++){
            option_item += '<option value="'+alkes_option_js[i].value+'">'+alkes_option_js[i].label+'</option>';
        }

        var input_item = '<select id="item_id[]" name="item_id[]" class="form-control">'+option_item+'</select>';
        var input_price = '<input type="number" class="form-control hide" name="item_price[]" placeholder="Price Item">';
        if(n < max_fields){ //max input box allowed
            n++; //text box increment
            $(wrapper_item).append('<div class="form-group"><div class="row"><div class="col-sm-6"><div class="input-group"><span class="input-group-addon"><a href="#" class="remove_field_item">X</a></span>'+input_item+'</div></div><div class="col-sm-4">'+input_price+'</div></div>'); //add input box
        }
    });
    $(wrapper_item).on("click",".remove_field_item", function(e){ //user click on remove text
        e.preventDefault(); 
        $(this).closest('.form-group').remove(); n--;
    });
  });
  </script>
  <script src="{{ asset('js/product.js?v=' . $asset_v) }}"></script>
@endsection