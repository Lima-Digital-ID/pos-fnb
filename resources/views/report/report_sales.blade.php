@extends('layouts.app')
@section('title', 'Laporan Keuangan')

@section('content')
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Laporan Penjualan
    </h1>
</section>
<section class="content">
    <div class="row no-print">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
              {!! Form::open(['url' => URL::current(), 'method' => 'get', 'id' => 'form-trx-member' ]) !!}
                <div class="col-md-4">
                    <div class="form-group">
                      {!! Form::label('Lokasi : ') !!}
                        {!! Form::select('location_id', 
                            $business_locations, $location_id, ['id' => 'location_id', 'class' => 'form-control select2', 'required' => 'required', 'placeholder'=>'Pilih Lokasi']); !!}
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Dari Tanggal</label>
                        <input type="date" class="form-control" name="dari" value="{{isset($_GET['dari']) ? $_GET['dari'] : ''}}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Sampai Tanggal</label>
                        <input type="date" class="form-control" name="sampai" value="{{isset($_GET['sampai']) ? $_GET['sampai'] : ''}}">
                    </div>
                </div>
                
                <div class="col-sm-12">
                  <button type="submit" class="btn btn-primary pull-right">@lang('report.apply_filters')</button>
                </div> 
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    @if(isset($_GET['dari']) != null)
        @component('components.widget', ['class' => 'box-primary', 'title' =>"Laporan Penjualan" ])
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="">
                <thead>
                    <tr>
                        <th style="min-width: 100px;">Tanggal</th>
                        <th style="min-width: 100px;">No Invoice</th>
                        <th style="min-width: 100px;">HPP</th>
                        <th style="min-width: 100px;">Harga Jual</th>
                        <th style="min-width: 100px;">Keuntungan</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $keuntungan = 0
                    @endphp
                    @foreach ($report as $item)
                        @php
                            $keuntungan+=($item->jual - $item->hpp)
                        @endphp
                        <tr>
                            <td>{{$item->tanggal}}</td>
                            <td>{{$item->invoice_no}}</td>
                            <td>{{number_format($item->hpp,0,',','.')}}</td>
                            <td>{{number_format($item->jual,0,',','.')}}</td>
                            <td>{{number_format($item->jual - $item->hpp,0,',','.')}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="4" class="text-center">Total Keuntungan</th>
                            <th>{{number_format($keuntungan,0,',','.')}}</th>
                        </tr>
                    </tfoot>
            </table>
        </div>
        @endcomponent
    @endif
</section>
@endsection