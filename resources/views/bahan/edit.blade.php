@extends('layouts.app')
@section('title', __('Tambah Bahan'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang( 'Ubah Bahan' )
            <small>@lang( 'Ubah bahan anda' )</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Ubah bahan anda')])
            @can('bahan.view')
                {!! Form::open(['url' => action('IngredientController@update', [$data->id_bahan]), 'method' => 'PUT']) !!}
                <div class="form-group col-sm-12">
                    <label>Nama Bahan</label>
                    <input type="text" placeholder="Nama Bahan" class="form-control" name="nama_bahan"
                        value="{{ old('nama_bahan', $data->nama_bahan) }}">
                </div>
                <div class="form-group col-sm-12">
                    <label>Satuan Bahan</label>
                    <select name="id_satuan" id="" class="form-control">
                        <option value="">---Pilih Satuan---</option>
                        @foreach ($satuan as $item)
                            <option value="{{ $item->id_satuan }}"
                                {{ old('id_satuan', $data->id_satuan) == $item->id_satuan ? 'selected' : '' }}>
                                {{ $item->satuan }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-sm-12">
                    <label>Stok Bahan</label>
                    <input type="number" placeholder="Stok Bahan" class="form-control" name="stok"
                        value="{{ old('stok', $data->stok) }}">
                </div>
                <div class="form-group col-sm-12">
                    <label>Limit Stok Bahan</label>
                    <input type="number" placeholder="Limit Stok Bahan" class="form-control" name="limit_stok"
                        value="{{ old('limit_stok', $data->limit_stok) }}">
                </div>
                <div class="form-group col-sm-12">
                    <label>Limit Stok Pemakaian</label>
                    <input type="number" placeholder="Limit Stok Pemakaian" class="form-control" name="limit_pemakaian"
                        value="{{ old('limit_pemakaian', $data->limit_pemakaian) }}">
                </div>
                <div class="form-group col-sm-12">
                    <button type="submit" class="btn btn-danger"><i class="fa fa-floppy-o"></i>
                        Simpan Data</button>
                    <a href="{{ route('bahan.index') }}" class="btn btn-info"><i class="fa fa-sign-out"></i>
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
