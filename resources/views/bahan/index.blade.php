@extends('layouts.app')
@section('title', __('Bahan'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang( 'Bahan' )
            <small>@lang( 'Mengelola bahan anda' )</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Semua bahan anda')])
            @can('bahan.create')
                @slot('tool')
                    <div class="box-tools">
                        <a href="{{ action('IngredientController@create') }}" class="btn btn-block btn-primary"><i
                                class="fa fa-plus"></i> @lang( 'messages.add' )</a>
                    </div>
                @endslot
            @endcan
            @can('bahan.view')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="ingredient_table">
                        <thead>
                            <tr>
                                {{-- <th width="30px">#</th> --}}
                                <th>Lokasi Bisnis</th>
                                <th>Nama Bahan</th>
                                <th>Satuan</th>
                                <th>Stok</th>
                                <th>Limit Stok</th>
                                <th>Limit Bahan</th>
                                <th>Tindakan</th>
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
