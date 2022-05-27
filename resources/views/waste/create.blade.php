@extends('layouts.app')
@section('title', __('Tambah PO Stok Bahan'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <br>
        <h1>@lang('Tambah PO Stok Bahan')</h1>
        <!-- <ol class="breadcrumb">                                                                                                               </ol> -->
    </section>

    <!-- Main content -->
    <section class="content no-print">
        {!! Form::open(['url' => action('WasteController@store'), 'method' => 'post', 'id' => 'stock_adjustment_form']) !!}
        <div class="box box-solid">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('ref_no', __('purchase.ref_no') . ':') !!}
                            {!! Form::text('no_referensi', 'WST' . time(), ['class' => 'form-control', 'readonly' => 'readonly']) !!}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::label('transaction_date', __('messages.date') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                <input type="date" name="date" id="" class="form-control">
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
                                            <option value="{{ $item->id }}" data-id="{{ $item->id }}">
                                                {{ $item->name }}</option>
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
                                    <span class="input-group-addon dynamic_button">
                                        <i class="fa fa-search"></i>
                                    </span>
                                    <select name="price_kategory[]" id="price_kategory" class="form-control price_kategory">
                                        <option value="" class="">---Pilih Kategori---</option>
                                        @foreach ($price_category as $item)
                                            <option value="{{ $item->id }}" data-id="{{ $item->id }}">
                                                {{ $item->kategori }}</option>
                                        @endforeach
                                    </select>
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

                                    <input type="hidden" name="price_ingredient[]" value="" id="price_ingredient">
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
                <div class="row" style="float: right;">
                    <div class="col-sm-12">
                        <a href="{{ route('po-bahan.index') }}" class="btn btn-info">
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
        function subTotal(no) {
            var selector = ".row-bahan[data-no='" + no + "']"
            var qty = $(selector + " .qty").val()
            var price = $(".row-bahan[data-no='" + no + "'] .bahan").find(":selected").attr('data-price')
            var subtotal = qty * price
            $(".row-bahan[data-no='" + no + "'] #price_ingredient").val(price)
            $(selector + " .subtotal").val(subtotal)

        }

        function subTotalProduct(no) {
            var selector = ".row-product[data-no='" + no + "']"
            var idProduct = $(".row-product[data-no='" + no + "'] .produk").find(":selected").attr('data-id');
            var qty = parseInt($(selector + " .qty_product").val())
            var price = parseInt($(".row-product[data-no='" + no + "'] .price-product").val())
            var subtotal = qty * price
            $(".row-product[data-no='" + no + "'] .subtotal_product").val(subtotal)
            console.log(idProduct, qty, price, subtotal);
        }
        $(".getSubtotal").change(function() {
            var no = $(this).closest(".row-bahan").attr('data-no')
            subTotal(no)
        })
        $(".bahan").change(function() {
            var no = $(this).closest(".row-bahan").attr('data-no')
            var satuan = $(".row-bahan[data-no='" + no + "'] .bahan").find(":selected").attr('data-satuan')
            $(".row-bahan[data-no='" + no + "'] .satuan-bahan").html(satuan);
        })

        function grandTotal() {
            var qty = $('#qty').val();
            var tax = $('#price-tax').val();
            var price = $('#harga').val();
            var subtotal = parseFloat(qty) * parseFloat(price);
            var priceTax = subtotal * parseFloat(tax) / 100 + subtotal;
            $('#subtotal').val(subtotal);
            $('#price-after-tax').val(priceTax);
        }
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
                        subTotal(x)
                    })
                    $(".row-bahan:last input,.row-bahan:last select").val('')
                    $(".row-bahan:last .dynamic_button").empty().html(
                        `<a href="javascript:void(0);" class="remove_button" title="Remove field">X</i></a>`
                    )
                    $(".row-bahan .remove_button").click(function() {
                        $(this).closest('.row-bahan').remove()
                        x--
                    })
                    $(".bahan").change(function() {
                        var no = $(this).closest(".row-bahan").attr('data-no')
                        var satuan = $(".row-bahan[data-no='" + no + "'] .bahan").find(
                                ":selected")
                            .attr('data-satuan')
                        $(".row-bahan[data-no='" + no + "'] .satuan-bahan").html(satuan);
                    })
                }
            });
            $("#add_item_product").click(function() {
                var fieldHTML = $(".row-product:last").clone()
                if (x < maxField) {
                    x++;
                    $('#add_product').append(fieldHTML);
                    $(".row-product:last").attr('data-no', x)
                    $(".row-product:last input,.row-product:last select").val('')
                    $(".row-product:last .product_dynamic_button").empty().html(
                        `<a href="javascript:void(0);" class="remove_button" title="Remove field">X</i></a>`
                    )
                    $(".row-product .remove_button").click(function() {
                        $(this).closest('.row-product').remove()
                        x--
                    })
                    $(".row-product .price_kategory").change(function() {
                        var no = $(this).closest(".row-product").attr('data-no')
                        var id = $(this).find(":selected").attr('data-id');
                        var idProduct = $('.produk').find(":selected").attr('data-id');
                        $.ajax({
                            type: "GET",
                            url: "{{ url('waste/get-price-category') }}/" + id + "/" +
                                idProduct, //json get site
                            dataType: 'json',
                            success: function(response) {
                                var price = parseInt(response[0].harga)
                                $(".row-product[data-no='" + no + "'] .price-product")
                                    .val(price)
                                subTotalProduct(no)
                            }
                        });
                    })
                    $(".qty_product").keyup(function() {
                        var no = $(this).closest(".row-product").attr('data-no')
                        var qty = $(".row-product[data-no='" + no + "'] .qty_product").val()
                        subTotalProduct(no)
                    })
                    $(".produk").change(function() {
                        var no = $(this).closest(".row-product").attr('data-no')
                        var id = $(this).find(":selected").attr('data-id');
                        subTotalProduct(no)
                    })
                }
            });
            $(".qty_product").keyup(function() {
                var no = $(this).closest(".row-product").attr('data-no')
                var qty = $(".row-product[data-no='" + no + "'] .qty_product").val()
                subTotalProduct(no)
            })
            $(".produk").change(function() {
                var no = $(this).closest(".row-product").attr('data-no')
                var id = $(this).find(":selected").attr('data-id');
                console.log(id);
                subTotalProduct(no)
            })
            $(".price_kategory").change(function() {
                var no = $(this).closest(".row-product").attr('data-no')
                var id = $(this).find(":selected").attr('data-id');
                var idProduct = $('.produk').find(":selected").attr('data-id');
                $.ajax({
                    type: "GET",
                    url: "{{ url('waste/get-price-category') }}/" + id + "/" +
                        idProduct, //json get site
                    dataType: 'json',
                    success: function(response) {
                        // console.log(response);
                        // console.log("{{ url('waste/get-price-category') }}/" + id + "/" +
                        //     idProduct);
                        var price = parseInt(response[0].harga)
                        $(".row-product[data-no='" + no + "'] .price-product").val(price)
                        subTotalProduct(no)
                    }
                });
            })

        });
    </script>
@endsection
