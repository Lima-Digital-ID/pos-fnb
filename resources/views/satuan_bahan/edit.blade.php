@extends('layouts.app')
@section('title', __('Tambah Satuan Bahan'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang( 'Tambah Satuan Bahan' )
            <small>@lang( 'Tambah Satuan bahan anda' )</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Tambah Satuan bahan anda')])
            @can('satuan_bahan.view')
                {!! Form::model($data, ['method' => 'PUT', 'route' => ['satuan_bahan.update', $data->id]]) !!}
                <div class="form-group col-sm-12">
                    {{ $data->id }}
                    <label>Satuan Bahan</label>
                    <input type="text" placeholder="Satuan Bahan" class="form-control" name="satuan"
                        value="{{ old('satuan', $data->satuan) }}">
                </div>
                <div class="form-group col-sm-12">
                    <button type="submit" class="btn btn-danger"><i class="fa fa-floppy-o"></i>
                        Simpan Data</button>
                    <a href="{{ route('satuan_bahan.index') }}" class="btn btn-info"><i class="fa fa-sign-out"></i>
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
