@extends('layouts.app')
@section('title', __('Limit Stok'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang( 'Limit Stok' )
            <small>@lang( 'Mengelola Limit Stok anda' )</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Semua Limit Stok anda')])
            @can('bahan.view')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="limit_stok_ingredient_table">
                        <thead>
                            <tr>
                                {{-- <th width="30px">#</th> --}}
                                <th>Lokasi Bisnis</th>
                                <th>Nama Bahan</th>
                                <th>Satuan</th>
                                <th>Stok</th>
                                <th>Limit Stok</th>
                                <th>Limit Bahan</th>
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
