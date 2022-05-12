@extends('layouts.app')
@section('title', __('Satuan Besar'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang( 'Satuan Besar' )
            <small>@lang( 'Mengelola satuan besar anda' )</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Semua satuan besar anda')])
            @can('satuan_besar.create')
                @slot('tool')
                    <div class="box-tools">
                        <a href="{{ action('SatuanBesarController@create') }}" class="btn btn-block btn-primary"><i
                                class="fa fa-plus"></i>
                            @lang( 'messages.add' )</a>
                    </div>
                @endslot
            @endcan
            @can('satuan_besar.view')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="satuan_besar_table">
                        <thead>
                            <tr>
                                {{-- <th width="30px">#</th> --}}
                                <th>Nama Satuan Besar</th>
                                <th width="200px">Tindakan</th>
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
