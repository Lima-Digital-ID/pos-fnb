@extends('layouts.app')
@section('title', __('Tambah Satuan Besar'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang( 'Tambah Satuan Besar' )
            <small>@lang( 'Tambah Satuan Besar anda' )</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Tambah Satuan Besar anda')])
            @can('satuan_besar.view')
                {!! Form::open(['route' => 'satuan_besar.store', 'method' => 'POST']) !!}
                <div class="form-group col-sm-12">
                    <label>Satuan Besar</label>
                    <input type="text" placeholder="Satuan besar" class="form-control" name="satuan_besar"
                        value="{{ old('satuan_besar') }}">
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
