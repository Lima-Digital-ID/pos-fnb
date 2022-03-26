@extends('layouts.app')
@section('title', __('Satuan Bahan'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang( 'Satuan Bahan' )
            <small>@lang( 'Mengelola satuan bahan anda' )</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Semua satuan bahan anda')])
            @can('satuan_bahan.create')
                @slot('tool')
                    <div class="box-tools">
                        <a href="{{ action('SatuanBahanController@create') }}" class="btn btn-block btn-primary"><i
                                class="fa fa-plus"></i>
                            @lang( 'messages.add' )</a>
                    </div>
                @endslot
            @endcan
            @can('satuan_bahan.view')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="satuan_bahan_table">
                        <thead>
                            <tr>
                                <th width="30px">#</th>
                                <th>Nama Bahan</th>
                                <th>Tindakan</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcan
        @endcomponent

        <div class="modal fade satuan_bahan_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

@endsection
