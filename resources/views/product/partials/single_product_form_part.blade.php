@if(!session('business.enable_price_tax')) 
  @php
    $default = 0;
    $class = 'hide';
  @endphp
@else
  @php
    $default = null;
    $class = '';
  @endphp
@endif

<div class="col-sm-9"><br>
  <div class="table-responsive">
    <table class="table table-bordered add-product-price-table table-condensed {{$class}}">
        <tr>
          <th>@lang('product.default_purchase_price')</th>
          <th>@lang('product.profit_percent') @show_tooltip(__('tooltip.profit_percent'))</th>
          {{-- <th>@lang('product.default_selling_price')</th> --}}
        </tr>
        <tr>
          <td>
            <div class="col-sm-6">
              {!! Form::label('single_dpp', trans('product.exc_of_tax') . ':*') !!}

              {!! Form::text('single_dpp', $default, ['class' => 'form-control input-sm dpp input_number', 'placeholder' => __('product.exc_of_tax'), 'required']); !!}
            </div>

            <div class="col-sm-6">
              {!! Form::label('single_dpp_inc_tax', trans('product.inc_of_tax') . ':*') !!}
            
              {!! Form::text('single_dpp_inc_tax', $default, ['class' => 'form-control input-sm dpp_inc_tax input_number', 'placeholder' => __('product.inc_of_tax'), 'required']); !!}
            </div>
          </td>

          <td>
            <br/>
            {!! Form::text('profit_percent', @num_format($profit_percent), ['class' => 'form-control input-sm input_number', 'id' => 'profit_percent', 'required']); !!}

            {!! Form::hidden('single_dsp', $default, ['class' => 'form-control input-sm dsp input_number', 'placeholder' => __('product.exc_of_tax'), 'id' => 'single_dsp', 'required']); !!}
  
            {!! Form::hidden('single_dsp_inc_tax', $default, ['class' => 'form-control input-sm hide input_number', 'placeholder' => __('product.inc_of_tax'), 'id' => 'single_dsp_inc_tax', 'required']); !!}
          </td>

          {{-- <td>
            <label><span class="dsp_label">@lang('product.exc_of_tax')</span></label>
          </td> --}}
        </tr>
    </table>
    </div>
</div>
<div class="col-sm-9"><br>
  <div class="table-responsive">
    <table class="table table-bordered add-product-price-table table-condensed {{$class}}">
        <tr>
          @php
              $kategori_harga = \DB::table('tb_kategori_harga')->get();
          @endphp
          <th colspan="{{count($kategori_harga)}}">Harga Jual Produk Berdasarkan Kategori</th>
        </tr>
        <tr>
          @foreach ($kategori_harga as $item)
            <td>
              <label for="">{{$item->kategori}}:*</label>
              <input type="hidden" name="id_kategori[]" value="{{$item->id}}">
              <input type="text" name="harga_kategori[]" class="input_number form-control harga_kategori" data-id="#harga_kategori_inc_tax{{$item->id}}" placeholder="Harga {{$item->kategori}}" required>
              <br>
              <label for="">{{$item->kategori}} Inc. Pajak:*</label>
              <input type="text" id="harga_kategori_inc_tax{{$item->id}}" name="harga_kategori_inc_tax[]" class="harga_kategori_inc_tax form-control" readonly>
            </td>
          @endforeach


          {{-- <td>
            <label><span class="dsp_label">@lang('product.exc_of_tax')</span></label>
          </td> --}}
        </tr>
    </table>
    </div>
</div>
