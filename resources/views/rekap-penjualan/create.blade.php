@extends('layouts.app')
@section('title', __('Rekap Penjualan Non Tunai'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Rekap Penjualan Non Tunai
            <small>@lang( 'Rekap Penjualan Non Tunai anda' )</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Rekap Penjualan Non Tunai anda')])
            @can('bahan.view')
                {!! Form::open(['route' => 'rekap-penjualan.store', 'method' => 'POST']) !!}
                <div class="col-md-4">
                    <div class="form-group">
                      {!! Form::label('Lokasi : ') !!}
                        {!! Form::select('location_id', 
                            $business_locations, $location_id, ['id' => 'location_id', 'class' => 'form-control select2', 'required' => 'required', 'placeholder'=>'Pilih Lokasi']); !!}
                    </div>
                </div>
                
                <div class="form-group col-md-4">
                    <label>Pilih Invoice</label>
                    <select name="id_inv[]" id="inv" class="form-control select2" multiple>
                        <option value="">---Pilih No Invoice---</option>
                        {{-- @foreach ($inv as $item)
                            <option value="{{ $item->id }}" {{ old('id_inv') == $item->id ? 'selected' : '' }}>
                                {{ $item->invoice_no }}</option>
                        @endforeach --}}
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Total</label>
                    <input type="number" required placeholder="Total" class="form-control" name="total" value="{{ old('total') }}">
                </div>
                <div class="form-group col-sm-12">
                    <button type="submit" class="btn btn-danger"><i class="fa fa-floppy-o"></i>
                        Simpan Data</button>
                    <a href="{{ route('rekap-penjualan.index') }}" class="btn btn-info"><i class="fa fa-sign-out"></i>
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
    $("#location_id").change(function(){
        var location_id = $(this).val()
        $("#inv").empty()
        $.ajax({
            type : 'get',
            url : "{{url('rekap-penjualan/getInv')}}",
            data : {location_id : location_id},
            dataType : 'json',
            success : function(res){
                $.each(res,function(i,v){
                    $("#inv").append(`<option value='${v.id}'>${v.invoice_no}</option>`)
                })
            }
        })
    })
</script>
@endsection
