@extends('layouts.app')
@section('title', __('Tambah Bahan'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang( 'Tambah Bahan' )
            <small>@lang( 'Tambah bahan anda' )</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Tambah bahan anda')])
            @can('bahan.view')
                {!! Form::open(['route' => 'bahan.store', 'method' => 'POST']) !!}
                <div class="form-group col-sm-12">
                    <label>Nama Bahan</label>
                    <input type="text" placeholder="Nama Bahan" class="form-control" name="nama_bahan"
                        value="{{ old('nama_bahan') }}">
                </div>
                <div class="form-group col-sm-12">
                    <label>Satuan Bahan</label>
                    <select name="id_satuan" id="" class="form-control satuanBahan">
                        <option value="">---Pilih Satuan---</option>
                        @foreach ($satuan as $item)
                            <option value="{{ $item->id_satuan }}" data-value="{{ $item->satuan }}" {{ old('id_satuan') == $item->id_satuan ? 'selected' : '' }}>
                                {{ $item->satuan }}</option>
                        @endforeach
                    </select>
                </div>
                {{-- <div class="form-group col-sm-12">
                    <label>Lokasi Bisnis</label>
                    <select name="location_id" id="" class="form-control">
                        <option value="">---Pilih Lokasi---</option>
                        @foreach ($lokasi as $item)
                            <option value="{{ $item->id }}" {{ old('location_id') == $item->location_id ? 'selected' : '' }}>
                                {{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-sm-12">
                    <label>Stok Bahan</label>
                    <input type="number" placeholder="Stok Bahan" class="form-control" name="stok" value="{{ old('stok') }}">
                </div> --}}
                <div class="form-group col-sm-12">
                    <label>Harga Bahan</label>
                    <input type="number" placeholder="Harga Bahan" class="form-control" name="price_ingredient"
                        value="{{ old('price_ingredient') }}">
                </div>
                <div class="form-group col-sm-12">
                    <label>Satuan Besar</label>
                    <select name="id_satuan_besar" id="" class="form-control satuanBesar">
                        <option value="">---Pilih Satuan Besar---</option>
                        @foreach ($satuanBesar as $item)
                            <option value="{{ $item->id_satuan_besar }}" data-value="{{ $item->satuan_besar }}" {{ old('id_satuan_besar') == $item->id_satuan_besar ? 'selected' : '' }}>
                                {{ $item->satuan_besar }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-sm-12">
                    <label>Inisialisasi Stok Bahan</label>
                    <div class="input-group">
                        <span class="input-group-addon">
                            1 <span id="input-satuan-besar"></span>
                        </span>
                        <input type="number" placeholder="Masukkan Jumlah Bahan" class="form-control" name="stok_besar"
                            value="{{ old('stok_besar') }}">
                        <span class="input-group-addon">
                            <span id="input-satuan"></span>
                        </span>
                    </div>

                </div>
                <div class="form-group col-sm-12">
                    <label>Limit Stok Bahan</label>
                    <input type="number" placeholder="Limit Stok Bahan" class="form-control" name="limit_stok"
                        value="{{ old('limit_stok') }}">
                </div>
                <div class="form-group col-sm-12">
                    <label>Limit Stok Pemakaian</label>
                    <input type="number" placeholder="Limit Stok Pemakaian" class="form-control" name="limit_pemakaian"
                        value="{{ old('limit_pemakaian') }}">
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

@section('javascript')
    <script>
        $(".satuanBesar").change(function() {
            var satuan = $(this).find(":selected").attr('data-value')
            $("#input-satuan-besar").html(satuan+' =');
        }) 
        $(".satuanBahan").change(function() {
            var satuanBahan = $(this).find(":selected").attr('data-value')
            $("#input-satuan").html(satuanBahan);
        }) 
    </script>
@endsection