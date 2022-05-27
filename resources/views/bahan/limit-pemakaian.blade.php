@extends('layouts.app')
@section('title', __('Limit Pemakaian'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang( 'Limit Pemakaian' )
            <small>@lang( 'Mengelola Limit Pemakaian anda' )</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Semua Limit Pemakaian anda')])
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="limit_pemakaian_ingredient_table">
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
        @endcomponent

        <div class="modal fade ingredient_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

@endsection
