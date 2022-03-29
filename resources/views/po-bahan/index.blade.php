@extends('layouts.app')
@section('title', __('PO Bahan'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>PO Bahan
            <small>@lang( 'Mengelola PO bahan anda' )</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Semua PO bahan anda')])
            @can('bahan.create')
                @slot('tool')
                    <div class="box-tools">
                        <a href="{{ action('PoBahanController@create') }}" class="btn btn-block btn-primary"><i
                                class="fa fa-plus"></i> @lang( 'messages.add' )</a>
                    </div>
                @endslot
            @endcan
            @can('bahan.view')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="po-bahan">
                        <thead>
                            <tr>
                                <th width="30px">#</th>
                                <th>No Referensi</th>
                                <th>Jenis Pajak</th>
                                <th>Tanggal PO</th>
                                <th>Bahan</th>
                                <th>Kuantitas</th>
                                <th>Harga Satuan</th>
                                <th>Harga Sebelum Pajak</th>
                                <th>Harga Sesudah Pajak</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcan
        @endcomponent

        <div class="modal fade ingredient_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

@endsection
