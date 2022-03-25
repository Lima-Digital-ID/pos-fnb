@extends('layouts.app')
@section('title', 'Hitungan Harta')

@section('content')
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Hitungan Harta
    </h1>
</section>
 @php
    function formatRupiah($num){
        return number_format($num, 0, '.', '.');
    }
    function formatDate($date){
        $date=date_create($date);
        return date_format($date, 'd-m-Y');
    }
    $total_omset=$omset->jumlah + $omset->jumlah_kas;
    $total_pengeluaran=$pengeluaran->jumlah_pengeluaran + $pengeluaran->jumlah_kasbon + $pengeluaran->jumlah_setoran;
@endphp
<!-- Main content -->
<section class="content">
    <div class="row no-print">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
              {!! Form::open(['url' => URL::current(), 'method' => 'get', 'id' => 'form-trx-member' ]) !!}
                <div class="col-md-4">
                    <div class="form-group">
                      {!! Form::label('Lokasi : ') !!}
                        {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'id' => 'location_id', 'placeholder' => __('messages.please_select')]); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">

                        {!! Form::label('product_sr_date_filter', __('report.date_range') . ':') !!}
                        {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'date', 'readonly']); !!}
                    </div>
                </div>
                <div class="col-sm-12">
                  <button type="submit" class="btn btn-primary pull-right">@lang('report.apply_filters')</button>
                </div> 
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    @if($location_id != null)
    @component('components.widget', ['class' => 'box-primary', 'title' =>"Hitungan Harta ".$locations->name.' per '.$date_range ])
            <div class="row no-print">
                <div class="col-sm-12">
                    <button type="button" class="btn btn-primary" 
                    aria-label="Print" onclick="window.print();"
                    ><i class="fa fa-print"></i> @lang( 'messages.print' )</button>
                </div>
            </div>
            <div><br></div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="">
                    <thead>
                        <tr>
                            <th colspan="2">Uang Kas Awal</th>    
                            <th class="text-right">{{formatRupiah($omset->jumlah_kas)}}</th>
                        </tr>
                        <tr>
                            <th colspan="2">Total Penjualan</th>    
                            <th class="text-right">{{formatRupiah($omset->jumlah)}}</th>
                        </tr>
                        <tr style="background-color:#ecf0f5">
                            <th colspan="2">Total Omset</th>
                            <th class="text-right">{{formatRupiah($total_omset)}}</th>
                        </tr>
                        <tr>
                            <th colspan="3"><br></th>
                        </tr>
                        <tr>
                            <th colspan="2">Total Setoran</th>  
                            <th class="text-right">{{formatRupiah($pengeluaran->jumlah_setoran)}}</th>
                        </tr>
                        <tr>
                            <th colspan="2">Total Pengeluaran</th>   
                            <th class="text-right">{{formatRupiah($pengeluaran->jumlah_pengeluaran)}}</th>
                        </tr>
                        <tr>
                            <th colspan="2">Total Kasbon</th>   
                            <th class="text-right">{{formatRupiah($pengeluaran->jumlah_kasbon)}}</th>
                        </tr>
                        <tr style="background-color:#b76565; color:white">
                            <th colspan="2">Total </th>
                            <th class="text-right">{{formatRupiah($total_pengeluaran)}}</th>
                        </tr>
                         <tr style="background-color:#3c8dbc; color:white">
                            <th colspan="2">Sisa Kas Cabang</th>
                            <th class="text-right">{{formatRupiah($total_omset-$total_pengeluaran)}}</th>
                        </tr>
                        <tr>
                            <th colspan="3"><br></th>
                        </tr>
                        <tr>
                            <th colspan="2">Total Pengeluaran Manajemen</th>    
                            <th class="text-right">{{formatRupiah($pengeluaran_other->pengeluaran_manajemen)}}</th>
                        </tr>
                        <tr>
                            <th colspan="2">Total Pengeluaran Sewa</th>    
                            <th class="text-right">{{formatRupiah($pengeluaran_other->pengeluaran_sewa)}}</th>
                        </tr>
                        <tr>
                            <th colspan="2">Total Tabungan THR</th>   
                            <th class="text-right">{{formatRupiah($pengeluaran_other->tabungan_thr)}}</th>
                        </tr>
                        <tr>
                            <th colspan="2">Total Tabungan Amortisasi</th> 
                            <th class="text-right">{{formatRupiah($pengeluaran_other->tabungan_amortisasi)}}</th>
                        </tr>
                    </thead>
                </table>
            </div>
            
            
    @endcomponent
    @endif
    <div class="modal fade account_model" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
    </div>

</section>
@endsection

@section('javascript')
<script src="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js?v=' . $asset_v) }}"></script>
<style>
    td.details-control {
        background: url('https://datatables.net/examples/resources/details_open.png') no-repeat center center;
        cursor: pointer;
    }
    tr.details td.details-control {
        background: url('https://datatables.net/examples/resources/details_close.png') no-repeat center center;
    }
</style>
<script>
    
    $(document).ready( function(){

    });
    function updateMemberTrxReport() {
        var data = {
            location_id: $('#location_id').val(),
            bulan: $('#bulan').val(),
            tahun: $('#tahun').val(),
        };
        console.log(data);
        var out = [];

        for (var key in data) {
            out.push(key + '=' + encodeURIComponent(data[key]));
        }
        url_data = out.join('&');
        list_trx_member.ajax.url('/reports/sell-trx-member?' + url_data).load();
    }
    function formatRupiah(angka, prefix)
    {
        var reverse = angka.toString().split('').reverse().join(''),
        ribuan = reverse.match(/\d{1,3}/g);
        ribuan = ribuan.join('.').split('').reverse().join('');
        return ribuan;
    }
    function formatDate(date){
        var myDate = new Date(date);
        var tgl=date.split('-');
        // var output = myDate.getDate() + "-" +  (myDate.getMonth()+1) + "-" + myDate.getFullYear();
        var output = tgl[2] + "-" +  tgl[1] + "-" + tgl[0];
        return output;
    }
</script>
<script>
    $(function() {
      $('input[id="date"]').daterangepicker({
        autoUpdateInput: false,
          locale: {
              cancelLabel: 'Clear'
          },
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'This Year': [financial_year.start, financial_year.end]
        },
      });

      $('input[id="date"]').on('apply.daterangepicker', function(ev, picker) {
          $(this).val(picker.startDate.format('DD-MM-YYYY') + ' ~ ' + picker.endDate.format('DD-MM-YYYY'));
      });

      $('input[id="date"]').on('cancel.daterangepicker', function(ev, picker) {
          $(this).val('');
      });
    });
    </script>
@endsection