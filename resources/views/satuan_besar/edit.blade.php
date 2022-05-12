@extends('layouts.app')
@section('title', __('Ubah Satuan Besar'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang( 'Ubah Satuan Besar' )
            <small>@lang( 'Ubah Satuan besar anda' )</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Ubah Satuan besar anda')])
            @can('satuan_besar.view')
                {!! Form::open(['url' => action('SatuanBesarController@update', [$data->id_satuan_besar]), 'method' => 'PUT']) !!}
                <div class="form-group col-sm-12">
                    <label>Satuan Besar</label>
                    <input type="text" placeholder="Satuan Besar" class="form-control" name="satuan_besar"
                        value="{{ old('satuan', $data->satuan_besar) }}">
                </div>
                <div class="form-group col-sm-12">
                    <button type="submit" class="btn btn-danger"><i class="fa fa-floppy-o"></i>
                        Simpan Data</button>
                    <a href="{{ route('satuan_besar.index') }}" class="btn btn-info"><i class="fa fa-sign-out"></i>
                        Kembali</a>
                </div>
                {!! Form::close() !!}
            @endcan
        @endcomponent

        <div class="modal fade ingredient_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

@endsection
