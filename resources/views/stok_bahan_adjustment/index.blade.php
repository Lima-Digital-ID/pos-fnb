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
                <table class="table table-bordered table-striped" id="stock_adjustment_bahan_table">
                    <thead>
                        <tr>
                            {{-- <th width="30px">#</th> --}}
                            <th>No Referensi</th>
                            <th>Lokasi Binsis</th>
                            <th>Tanggal</th>
                            <th>Jenis Penyesuaian</th>
                            <th>Nama Bahan</th>
                            <th>Jumlah Stok</th>
                            <th>Alasan</th>
                            {{-- <th>Tindakan</th> --}}
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
