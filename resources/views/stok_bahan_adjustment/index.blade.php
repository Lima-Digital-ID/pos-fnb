@extends('layouts.app')
@section('title', __('Penyesuaian Stok Bahan'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang('Penyesuaian Stok Bahan')
            <small></small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Semua Penyesuaian stok bahan')])
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{ action('StockBahanAdjustmenController@create') }}">
                        <i class="fa fa-plus"></i> Tambah</a>
                </div>
            @endslot
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="stock_adjustment_table">
                    <thead>
                        <tr>
                            <th>@lang('messages.date')</th>
                            <th>@lang('purchase.ref_no')</th>
                            <th>@lang('business.location')</th>
                            <th>@lang('stock_adjustment.adjustment_type')</th>
                            <th>@lang('stock_adjustment.total_amount')</th>
                            <th>@lang('stock_adjustment.total_amount_recovered')</th>
                            <th>@lang('stock_adjustment.reason_for_stock_adjustment')</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                </table>
            </div>
        @endcomponent

    </section>
    <!-- /.content -->
@stop
@section('javascript')
    <script src="{{ asset('js/stock_adjustment.js?v=' . $asset_v) }}"></script>
@endsection
