@extends('layouts.app')
@section('title', __('Rekap Penjualan Non Tunai'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Rekap Penjualan Non Tunai
            <small>@lang( 'Mengelola Rekap Penjualan Non Tunai anda' )</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Semua Rekap Penjualan Non Tunai anda')])
            @can('bahan.create')
                @slot('tool')
                    <div class="box-tools">
                        <a href="{{ action('RekapPenjualanOnlineController@create') }}" class="btn btn-block btn-primary"><i
                                class="fa fa-plus"></i> @lang( 'messages.add' )</a>
                    </div>
                @endslot
            @endcan
            @can('bahan.view')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="rekap-penjualan">
                        <thead>
                            <tr>
                                {{-- <th width="30px">#</th> --}}
                                <th>No Invoice</th>
                                <th>Tanggal Rekap</th>
                                <th>Total Penjualan</th>
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
