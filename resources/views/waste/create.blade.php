@extends('layouts.app')
@section('title', __('Tambah Waste'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <br>
        <h1>@lang('Tambah Waste')</h1>
        <!-- <ol class="breadcrumb">                                                                                                               </ol> -->
    </section>

    <!-- Main content -->
    <section class="content no-print">
        {!! Form::open(['url' => action('WasteController@store'), 'method' => 'post', 'id' => 'stock_adjustment_form']) !!}
        <div class="box box-solid">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                          {!! Form::label('Lokasi : ') !!}
                            {!! Form::select('location_id', 
                                $business_locations, $location_id, ['id' => 'location_id', 'class' => 'form-control select2', 'required' => 'required', 'placeholder'=>'Pilih Lokasi']); !!}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('ref_no', __('purchase.ref_no') . ':') !!}
                            {!! Form::text('no_referensi', 'WST' . time(), ['class' => 'form-control', 'readonly' => 'readonly']) !!}
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('transaction_date', __('messages.date') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                <input type="date" name="date" id="" class="form-control" required>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--box end-->
        <div class="box box-solid">
            <div class="box-header">
                <h3 class="box-title">{{ __('Cari Produk') }}</h3>
            </div>
            <div class="box-body">
                <div id="add_product">

                    <div class="row row-product" data-no='1'>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon product_dynamic_button">
                                        <i class="fa fa-search"></i>
                                    </span>
                                    <select name="product[]" id="product" class="form-control produk">
                                        <option value="" class="opt-produk">---Pilih Produk---</option>
                                        @foreach ($product as $item)
                                            <option value="{{ $item->id }}" data-id="{{ $item->id }}" data-price="{{ $item->hpp }}">
                                                {{ $item->product }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-shopping-cart"></i>
                                    </span>
                                    <input type="number" class="form-control qty_product" placeholder="Kuantitas"
                                        id="qty_product" name="qty_product[]">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-usd"></i>
                                    </span>
                                    <input type="number" class="form-control subtotal_product" placeholder="Subtotal"
                                        name="subtotal_product[]" id="subtotal_product" readonly="readonly">
                                    <input type="hidden" name="price_product[]" value="" class="price-product">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <select name="product_recap_type[]" id="product_recap_type" class="form-control">
                                <option value="promo">Promo</option>
                                <option value="waste">Waste</option>
                            </select>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <div class="input-group">
                                <a href="javascript:void(0)" class="btn btn-info btn-sm" id="add_item_product"><i
                                        class="fa fa-plus">Tambah</i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--box end-->
        <div class="box box-solid">
            <div class="box-header">
                <h3 class="box-title">{{ __('Cari Bahan') }}</h3>
            </div>
            <div class="box-body">
                <div id="add_bahan">

                    <div class="row row-bahan" data-no='1'>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon dynamic_button">
                                        <i class="fa fa-search"></i>
                                    </span>
                                    <select name="bahan[]" id="bahan" class="form-control bahan">
                                        <option value="" class="opt-bahan">---Pilih Bahan---</option>
                                        @foreach ($ingredient as $item)
                                            <option value="{{ $item->id_bahan }}"
                                                data-satuan="{{ $item->satuan->satuan }}"
                                                data-price="{{ $item->harga_bahan }}">
                                                {{ $item->nama_bahan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-shopping-cart"></i>
                                    </span>
                                    <input type="number" class="form-control qty getSubtotal" placeholder="Kuantitas"
                                        id="qty" name="qty[]">
                                    <span class="input-group-addon">
                                        <div id="satuan" class="satuan-bahan">
                                            -
                                        </div>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-usd"></i>
                                    </span>
                                    <input type="number" class="form-control subtotal" placeholder="Subtotal"
                                        name="subtotal[]" id="subtotal" readonly="readonly">

                                    <input type="hidden" name="price_ingredient[]" value="" id="price_ingredient"
                                        class="price_ingredient">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <div class="input-group">
                                <a href="javascript:void(0)" class="btn btn-info btn-sm" id="tambah_bahan"><i
                                        class="fa fa-plus">Tambah</i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--box end-->
        <div class="box box-solid">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-4">
                        <label for="">Total Produk</label>
                        <input type="text" placeholder="Subtotal Produk" class="form-control subTotalProduk" readonly
                        name="subtotal_produk" value="0">
                    </div>
                    <div class="col-sm-4">
                        <label for="">Total Bahan</label>
                        <input type="text" placeholder="Subtotal Bahan" class="form-control subTotalIngredients" readonly
                        name="subtotal_bahan" value="0">
                    </div>
                    <div class="col-sm-4">
                        <label for="">Grand Total</label>
                        <input type="text" placeholder="Grand Total" class="form-control grandTotal" readonly
                            name="grand_total" value="0">
                    </div>
                </div>
                <br>
                <div class="row" style="float: right;">
                    <div class="col-sm-12">
                        <a href="{{ route('waste.index') }}" class="btn btn-info">
                            Kembali</a>
                        <button type="submit" class="btn btn-primary">@lang('messages.save')</button>
                    </div>
                </div>

            </div>
        </div>
        <!--box end-->
        {!! Form::close() !!}
    </section>
@stop
@section('javascript')
    <script>
        function hitungSubTotalProduk() {
            var sum = 0
            $(".subtotal_product").each(function() {
                sum += parseFloat(this.value);
                $(".subTotalProduk").val(sum);
            })
            hitungGrandTotal()
        }

        function hitungSubTotalBahan() {
            var sum = 0
            $(".subtotal").each(function() {
                sum += parseFloat(this.value);
                $(".subTotalIngredients").val(sum);
            })
            hitungGrandTotal()
        }

        function hitungGrandTotal() {
            bahan = $(".subTotalIngredients").val()
            produk = $(".subTotalProduk").val()
            grand = parseFloat(bahan) + parseFloat(produk)
            $(".grandTotal").val(grand);
        }

        function subTotalBahan(no) {
            var selector = ".row-bahan[data-no='" + no + "']"
            var qty = $(selector + " .qty").val()
            var price = $(".row-bahan[data-no='" + no + "'] .bahan").find(":selected").attr('data-price')
            var subtotal = qty * price
            $(".row-bahan[data-no='" + no + "'] .price_ingredient").val(price)
            $(selector + " .subtotal").val(subtotal)
            hitungSubTotalBahan()
        };

        function subTotalProduct(no) {
            var selector = ".row-product[data-no='" + no + "']"
            var idProduct = $(".row-product[data-no='" + no + "'] .produk").find(":selected").attr('data-id');
            var idCategory = $(".row-product[data-no='" + no + "'] .price_kategory").find(":selected").attr('data-id');
            var qty = $( ".row-product[data-no='" + no + "'] .qty_product").val()
            var priceProduct = $(".row-product[data-no='" + no + "'] .produk").find(":selected").attr('data-price');
            var subtotalProduct = qty * priceProduct
            $(".row-product[data-no='" + no + "'] .price-product").val(priceProduct)
            $(".row-product[data-no='" + no + "'] .subtotal_product").val(subtotalProduct)
            hitungSubTotalProduk()
        };

        $(".getSubtotal").keyup(function() {
            var no = $(this).closest(".row-bahan").attr('data-no')
            subTotalBahan(no)
        })

        $(".bahan").change(function() {
            var no = $(this).closest(".row-bahan").attr('data-no')
            var satuan = $(".row-bahan[data-no='" + no + "'] .bahan").find(":selected").attr('data-satuan')
            $(".row-bahan[data-no='" + no + "'] .satuan-bahan").html(satuan)
            subTotalBahan(no)
        })

        $(document).ready(function() {
            var maxField = 100;
            var addButton = $('#tambah_bahan');
            var deleteButton = $('#delete_bahan');
            var wrapper = $('#add_bahan');
            var x = 1;
            $(addButton).click(function() {
                var fieldHTML = $(".row-bahan:last").clone()
                if (x < maxField) {
                    x++;
                    $(wrapper).append(fieldHTML);
                    $(".row-bahan:last").attr('data-no', x)
                    $(".row-bahan:last .getSubtotal").change(function() {
                        subTotalBahan(x)
                    })
                    $(".row-bahan:last input,.row-bahan:last select").val('')
                    $(".row-bahan:last .dynamic_button").empty().html(
                        `<a href="javascript:void(0);" class="remove_button" title="Remove field">X</i></a>`
                    )
                    $(".row-bahan .remove_button").click(function() {
                        $(this).closest('.row-bahan').remove()
                        x--

                        hitungSubTotalBahan()
                    })
                    $(".getSubtotal").keyup(function() {
                        var no = $(this).closest(".row-bahan").attr('data-no')
                        subTotalBahan(no)
                    })

                    $(".bahan").change(function() {
                        var no = $(this).closest(".row-bahan").attr('data-no')
                        var satuan = $(".row-bahan[data-no='" + no + "'] .bahan").find(":selected")
                            .attr('data-satuan')
                        $(".row-bahan[data-no='" + no + "'] .satuan-bahan").html(satuan)
                        subTotalBahan(no)
                    })
                }
            });
            $("#add_item_product").click(function() {
                var fieldHTML = $(".row-product:last").clone()
                if (x < maxField) {
                    x++;
                    $('#add_product').append(fieldHTML);
                    $(".row-product:last").attr('data-no', x)
                    $(".row-product:last input,.row-product:last select:not(#product_recap_type)").val('')
                    $(".row-product:last .product_dynamic_button").empty().html(
                        `<a href="javascript:void(0);" class="remove_button" title="Remove field">X</i></a>`
                    )
                    $(".row-product .remove_button").click(function() {
                        $(this).closest('.row-product').remove()
                        x--
                        hitungSubTotalProduk()
                    })
                    $(".qty_product").keyup(function() {
                        var no = $(this).closest(".row-product").attr('data-no')
                        subTotalProduct(no)
                    })
                    $(".produk").change(function() {
                        var no = $(this).closest(".row-product").attr('data-no')
                        subTotalProduct(no)
                    })
                    $(".price_kategory").change(function() {
                        var no = $(this).closest(".row-product").attr('data-no')
                        subTotalProduct(no)
                    })
                }
            });
            $(".qty_product").keyup(function() {
                var no = $(this).closest(".row-product").attr('data-no')
                subTotalProduct(no)
            })
            $(".produk").change(function() {
                var no = $(this).closest(".row-product").attr('data-no')
                subTotalProduct(no)
            })
            $(".price_kategory").change(function() {
                var no = $(this).closest(".row-product").attr('data-no')
                subTotalProduct(no)
            })
        });
    </script>
@endsection
