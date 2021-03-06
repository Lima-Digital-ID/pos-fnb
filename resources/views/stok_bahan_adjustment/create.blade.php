@extends('layouts.app')
@section('title', __('Tambah Penyesuaian Stok Bahan'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <br>
        <h1>@lang('Tambah Penyesuaian Stok Bahan')</h1>
        <!-- <ol class="breadcrumb">                                                                                                               </ol> -->
    </section>

    <!-- Main content -->
    <section class="content no-print">
        {!! Form::open(['url' => action('StockBahanAdjustmenController@store'), 'method' => 'post', 'id' => 'stock_adjustment_form']) !!}
        <div class="box box-solid">
            <div class="box-body">
                <div class="row">
                    <div class="col-sm-3">
                        <div class="form-group">
                            <label for="">Lokasi Bisnis</label>
                            <select name="id_location" id="" class="form-control select2">
                                <option value="">---Pilih Lokasi---</option>
                                @foreach ($lokasi as $item)
                                    <option value="{{ $item->id }}">{{ $item->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('ref_no', __('purchase.ref_no') . ':') !!}
                            {!! Form::text('no_referensi', 'ADJ' . time(), ['class' => 'form-control', 'readonly' => 'readonly']) !!}
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
                    <div class="col-sm-3">
                        <div class="form-group">
                            {!! Form::label('adjustment_type', __('stock_adjustment.adjustment_type') . ':*') !!} @show_tooltip(__('tooltip.adjustment_type'))
                            {!! Form::select('jenis_penyesuaian', ['Normal' => __('stock_adjustment.normal'), 'Abnormal' => __('stock_adjustment.abnormal')], null, ['class' => 'form-control select2', 'placeholder' => __('messages.please_select'), 'required']) !!}
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
                    <div class="row row-adj" data-no='1'>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon dynamic_button">
                                        <i class="fa fa-search"></i>
                                    </span>
                                    <select name="bahan[]" id="bahan" class="form-control bahan">
                                        <option value="">---Pilih Bahan---</option>
                                        @foreach ($bahan as $item)
                                            <option value="{{ $item->id_bahan }}" data-stok="{{ $item->stok }}">
                                                {{ $item->nama_bahan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-dropbox"></i>
                                    </span>
                                    <input type="number" class="form-control stokInput" placeholder="Stok"
                                        name="stok_adjust[]">
                                    <span class="input-group-addon">
                                        <div id="stok" class="stok-bahan">
                                            -
                                        </div>
                                    </span>
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
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('additional_notes', __('stock_adjustment.reason_for_stock_adjustment') . ':') !!}
                            {!! Form::textarea('alasan', null, ['class' => 'form-control', 'placeholder' => __('stock_adjustment.reason_for_stock_adjustment'), 'rows' => 3]) !!}
                        </div>
                    </div>
                </div>
                <div class="row" style="float: right;">
                    <div class="col-sm-12">
                        <a href="{{ route('stock-bahan-adjustment.index') }}" class="btn btn-info">
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
        $(".stokInput").keyup(function() {
            var no = $(this).closest(".row-adj").attr('data-no')
            var stokAwal = $(".row-adj[data-no='" + no + "'] .bahan").find(":selected").attr(
                'data-stok')
            var stokInput = $(".row-adj[data-no='" + no + "'] .stokInput").val()
            var stok = stokAwal - stokInput
            console.log(stok);
            $(".row-adj[data-no='" + no + "'] .stok-bahan").html(stok);
        })
        $(".bahan").change(function() {
            var no = $(this).closest(".row-adj").attr('data-no')
            var stok = $(".row-adj[data-no='" + no + "'] .bahan").find(":selected").attr(
                'data-stok')
            $(".row-adj[data-no='" + no + "'] .stok-bahan").html(stok);
        })
        $(document).ready(function() {
            var maxField = 100;
            var addButton = $('#tambah_bahan');
            var deleteButton = $('#delete_bahan');
            var wrapper = $('#add_bahan');
            var x = 1;
            $(addButton).click(function() {
                var fieldHTML = $(".row-adj:last").clone()
                if (x < maxField) {
                    x++;
                    $(wrapper).append(fieldHTML);
                    $(".row-adj:last").attr('data-no', x)
                    $(".row-adj:last input,.row-adj:last select").val('')
                    $(".row-adj:last .dynamic_button").empty().html(
                        `<a href="javascript:void(0);" class="remove_button" title="Remove field">X</i></a>`
                    )
                    $(".row-adj .remove_button").click(function() {
                        $(this).closest('.row-adj').remove()
                        x--
                    })
                    $(".stokInput").keyup(function() {
                        var no = $(this).closest(".row-adj").attr('data-no')
                        var stokAwal = $(".row-adj[data-no='" + no + "'] .bahan").find(":selected")
                            .attr(
                                'data-stok')
                        var stokInput = $(".row-adj[data-no='" + no + "'] .stokInput").val()
                        var stok = stokAwal - stokInput
                        console.log(stok);
                        $(".row-adj[data-no='" + no + "'] .stok-bahan").html(stok);
                    })
                    $(".bahan").change(function() {
                        var no = $(this).closest(".row-adj").attr('data-no')
                        var stok = $(".row-adj[data-no='" + no + "'] .bahan").find(":selected")
                            .attr(
                                'data-stok')
                        $(".row-adj[data-no='" + no + "'] .stok-bahan").html(stok);
                    })
                }
            });
        });
    </script>
@endsection
