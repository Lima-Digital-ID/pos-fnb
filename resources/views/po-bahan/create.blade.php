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
        {!! Form::open(['url' => action('PoBahanController@store'), 'method' => 'post', 'id' => 'stock_adjustment_form']) !!}
        <div class="box box-solid">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="">Lokasi</label>
                            <select name="id_lokasi" id="id_lokasi" class="form-control select2 lokasi">
                                <option value="0">---Pilih Lokasi---</option>
                                @foreach ($lokasi as $item)
                                    <option value="{{ $item->id }}" data-id="{{ $item->id }}">
                                        {{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="">Pajak</label>
                            <select name="id_pajak" id="tax" class="form-control">
                                <option value="0" data-pajak="0">---Pilih Pajak---</option>
                                @foreach ($tax as $item)
                                    <option value="{{ $item->id }}" data-pajak="{{ $item->amount }}">
                                        {{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <input type="hidden" id="price-tax">
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('ref_no', __('purchase.ref_no') . ':') !!}
                            {!! Form::text('no_referensi', 'PO' . time(), ['class' => 'form-control', 'readonly' => 'readonly']) !!}
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('transaction_date', __('messages.date') . ':*') !!}
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </span>
                                {!! Form::text('date', @format_datetime('now'), ['class' => 'form-control', 'readonly', 'required']) !!}
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
                        <div class="col-sm-3">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon dynamic_button">
                                        <i class="fa fa-search"></i>
                                    </span>
                                    <select name="bahan[]" id="bahan" class="form-control bahan">
                                        <option value="" class="opt-bahan">---Pilih Bahan---</option>
                                        @foreach ($bahan as $item)
                                            <option value="{{ $item->id_bahan }}">
                                                {{ $item->nama_bahan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-shopping-cart"></i>
                                    </span>
                                    <input type="number" class="form-control qty getSubtotal" placeholder="Kuantitas"
                                        id="qty" name="qty[]">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-usd"></i>
                                    </span>
                                    <input type="number" class="form-control price getSubtotal" placeholder="Harga Satuan"
                                        name="price[]" id="harga">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-usd"></i>
                                    </span>
                                    <input type="number" class="form-control subtotal_before_tax"
                                        placeholder="Harga Sebelum Pajak" name="subtotal[]" id="subtotal"
                                        readonly="readonly">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-usd"></i>
                                    </span>
                                    <input type="number" class="form-control subtotal_after_tax"
                                        placeholder="Harga Sesudah Pajak" name="subtotaltax[]" id="price-after-tax"
                                        readonly="readonly">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="row">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <div class="input-group">
                                <a href="#" class="btn btn-info btn-sm" id="tambah_bahan"><i
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
            var tax = $("#tax").find(":selected").attr('data-pajak')
            var qty = $(selector + " .qty").val()
            var price = $(selector + " .price").val()
            var subtotal_before_tax = $(selector + " .subtotal_before_tax").val()
            var subtotal_after_tax = $(selector + " .subtotal_after_tax").val()

            var subtotal_before_tax_val = qty * price
            var subtotal_after_tax_val = tax == 0 ? subtotal_before_tax_val : (subtotal_before_tax_val * tax / 100) +
                subtotal_before_tax_val

            $(selector + " .subtotal_before_tax").val(subtotal_before_tax_val)
            $(selector + " .subtotal_after_tax").val(subtotal_after_tax_val)
        }
        $(".getSubtotal").change(function() {
            var no = $(this).closest(".row-bahan").attr('data-no')
            subTotal(no)
        })

        $(".lokasi").change(function() {
            var id = $(".lokasi").find(":selected").attr('data-id')
            getIngredientByLocation(id)
        })
        // $(".bahan").change(function() {
        //     $(".bahan").val('');
        // })

        // function getIngredientByLocation(id) {
        //     $.ajax({
        //         url: "/bahan/list/" + id,
        //         type: "GET",
        //         async: false,
        //         success: function(response) {
        //             data = jQuery.parseJSON(response);
        //             console.log(data);
        //             $.each(data, function(k, v) {
        //                 $(".bahan").append("<option value=" + v.id_bahan + ">" + v.nama_bahan +
        //                     "</option>");
        //             });
        //         }
        //     })
        // }

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
                }
            });
        });
    </script>
@endsection
