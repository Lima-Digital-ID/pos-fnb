@extends('layouts.app')
@section('title', 'Laporan Keuangan')

@section('content')
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">
<style>
    tbody tr{
        cursor: pointer;
    }
</style>
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Rekap Penjualan Makanan dan Minuman
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
            <table class="table table-bordered table-striped salesTable" id="">
                <thead>
                    <tr>
                        <th style="min-width: 100px;">Tanggal</th>
                        <th style="min-width: 100px;">No Invoice</th>
                        <th style="min-width: 100px;">Makanan</th>
                        <th style="min-width: 100px;">Minuman</th>
                        <th style="min-width: 100px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $makanan = 0;
                        $minuman = 0;
                    @endphp
                    @foreach ($report as $item)
                        @php
                            $makanan+=$item->makanan;
                            $minuman+=$item->minuman;
                        @endphp
                        <tr data-href="{{url('sells/'.$item->id."?noprint=true")}}">
                            <td>{{$item->transaction_date}}</td>
                            <td>{{$item->invoice_no}}</td>
                            <td>{{(int)$item->makanan}}</td>
                            <td>{{(int)$item->minuman}}</td>
                            <td>{{$item->makanan + $item->minuman}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-center">Total</th>
                            <th>{{$makanan}}</th>
                            <th>{{$minuman}}</th>
                            <th>{{$makanan + $minuman}}</th>
                        </tr>
                    </tfoot>
            </table>
        </div>
        @endcomponent
    @endif
</section>
@endsection