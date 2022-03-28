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
                    <div class="col-sm-4">
                        <div class="form-group">
                            {!! Form::label('ref_no', __('purchase.ref_no') . ':') !!}
                            {!! Form::text('no_referensi', null, ['class' => 'form-control']) !!}
                        </div>
                    </div>
                    <div class="col-sm-4">
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
                    <div class="col-sm-4">
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
                <div class="row" data-no="0">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="fa fa-search"></i>
                                </span>
                                <select name="bahan[]" id="" class="form-control select2">
                                    <option value="">---Pilih Bahan---</option>
                                    @foreach ($bahan as $item)
                                        <option value="{{ $item->id_bahan }}">{{ $item->nama_bahan }}</option>
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
                                <input type="text" class="form-control" placeholder="Stok" name="stok_adjust[]">
                            </div>
                        </div>
                    </div>
                </div>
                <div id="add_bahan">

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
        $(document).ready(function() {
            $.ajax({
                url: "/bahan/list",
                type: "GET",
                async: false,
                success: function(response) {
                    data = jQuery.parseJSON(response);
                    $.each(data, function(k, v) {
                        id_bahan = v.id_bahan
                        bahan = v.nama_bahan
                    });
                }
            })
            var data = "";
            var maxField = 100;
            var addButton = $('#tambah_bahan');
            var wrapper = $('#add_bahan');
            var fieldHTML = `
                <div>
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <a href="javascript:void(0);" class="remove_button" id="delete_bahan" title="Remove field">X</i></a>
                                    </span>
                                    <select name="bahan[]" id="" class="form-control select2">
                                        <option value="">---Pilih Bahan---</option>
                                        <option value="${id_bahan}">${bahan}</option>
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
                                    <input type="text" name="stok_adjust[]" class="form-control" placeholder="Stok">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                `;
            var x = 1;
            $(addButton).click(function() {
                if (x < maxField) {
                    x++;
                    $(wrapper).append(fieldHTML);
                }
            });
            $('#delete_bahan').on('click', '#', function(e) {
                e.preventDefault();
                $(this).parent('div').remove();
                x--;
            });
            $(document).ready(function() {
                $('.select2').select2();
            });
        });
        // $("#tambah_bahan").click(function() {
        //     var no = 1;
        //     $("#add_bahan").append(`
    //         <div class="row">
    //             <div class="col-sm-6">
    //                 <div class="form-group">
    //                     <div class="input-group">
    //                         <span class="input-group-addon">
    //                             <a href="#" id="delete_bahan">X</a>
    //                         </span>
    //                         <select name="bahan" id="" class="form-control select2">
    //                             <option value="">---Pilih Bahan---</option>
    //                         </select>
    //                     </div>
    //                 </div>
    //             </div>
    //             <div class="col-sm-6">
    //                 <div class="form-group">
    //                     <div class="input-group">
    //                         <span class="input-group-addon">
    //                             <i class="fa fa-dropbox"></i>
    //                         </span>
    //                         <input type="text" name="stok" class="form-control" placeholder="Stok">
    //                     </div>
    //                 </div>
    //             </div>
    //         </div>
    //     `);
        // });
    </script>
@endsection
