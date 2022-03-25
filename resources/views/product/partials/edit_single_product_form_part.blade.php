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
<div class="col-sm-12"><br>
    <div class="table-responsive">
    <table class="table table-bordered add-product-price-table table-condensed {{$class}}">
        <tr>
          <th>@lang('product.default_purchase_price')</th>
          <th>@lang('product.profit_percent') @show_tooltip(__('tooltip.profit_percent'))</th>
        </tr>
        @php
            $product_id = 0;
        @endphp
        @foreach($product_deatails->variations as $variation )
            @if($loop->first)
            @php
                $product_id = $variation->product_id;
            @endphp
                <tr>
                    <td>
                        <input type="hidden" name="single_variation_id" value="{{$variation->id}}">

                        <div class="col-sm-6">
                          {!! Form::label('single_dpp', trans('product.exc_of_tax') . ':*') !!}

                          {!! Form::text('single_dpp', @num_format($variation->default_purchase_price), ['class' => 'form-control input-sm dpp input_number', 'placeholder' => __('product.exc_of_tax'), 'required']); !!}
                        </div>

                        <div class="col-sm-6">
                          {!! Form::label('single_dpp_inc_tax', trans('product.inc_of_tax') . ':*') !!}
                        
                          {!! Form::text('single_dpp_inc_tax', @num_format($variation->dpp_inc_tax), ['class' => 'form-control input-sm dpp_inc_tax input_number', 'placeholder' => __('product.inc_of_tax'), 'required']); !!}
                        </div>
                    </td>

                    <td>
                        <br/>
                        {!! Form::text('profit_percent', @num_format($variation->profit_percent), ['class' => 'form-control input-sm input_number', 'id' => 'profit_percent', 'required']); !!}

                        {!! Form::hidden('single_dsp', @num_format($variation->default_sell_price), ['class' => 'form-control input-sm dsp input_number', 'placeholder' => __('product.exc_of_tax'), 'id' => 'single_dsp', 'required']); !!}
    
                        {!! Form::hidden('single_dsp_inc_tax', @num_format($variation->sell_price_inc_tax), ['class' => 'form-control input-sm hide input_number', 'placeholder' => __('product.inc_of_tax'), 'id' => 'single_dsp_inc_tax', 'required']); !!}
                    </td>

                </tr>
            @endif
        @endforeach
    </table>
    </div>
</div>
<div class="col-sm-12"><br>
  <div class="table-responsive">
    <table class="table table-bordered add-product-price-table table-condensed {{$class}}">
        <tr>
          @php
              $kategori_harga = \DB::table('tb_harga_produk as hp')->select('kh.*','hp.id as id_harga_produk','hp.harga','hp.harga_inc_tax')->join('products as p','hp.product_id','p.id')->join('tb_kategori_harga as kh','hp.id_kategori','kh.id')->where('hp.product_id',$product_id)->get();
          @endphp
          <th colspan="{{count($kategori_harga)}}">Harga Jual Produk Berdasarkan Kategori</th>
        </tr>
        <tr>
          @foreach ($kategori_harga as $item)
            <td>
              <label for="">{{$item->kategori}}:*</label>
              <input type="hidden" name="id_harga_produk[]" value="{{$item->id_harga_produk}}">
              <input type="text" name="harga_kategori[]" class="input_number form-control harga_kategori" data-id="#harga_kategori_inc_tax{{$item->id_harga_produk}}" placeholder="Harga {{$item->kategori}}" value="{{$item->harga}}" required>
              <br>
              <label for="">{{$item->kategori}} Inc. Pajak:*</label>
              <input id="harga_kategori_inc_tax{{$item->id_harga_produk}}" type="text" name="harga_kategori_inc_tax[]" class="harga_kategori_inc_tax form-control" value="{{$item->harga_inc_tax}}" readonly>
            </td>
          @endforeach


          {{-- <td>
            <label><span class="dsp_label">@lang('product.exc_of_tax')</span></label>
          </td> --}}
        </tr>
    </table>
    </div>
</div>
