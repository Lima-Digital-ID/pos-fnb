@extends('layouts.app')
@section('title', 'Rekap Transaksi Jasa Pegawai')

@section('content')
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Rekap Transaksi Jasa Pegawai
    </h1>
</section>

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
                        {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'product_sr_date_filter', 'readonly']); !!}
                    </div>
                </div>
                <div class="col-sm-12">
                  <button type="submit" class="btn btn-primary pull-right">@lang('report.apply_filters')</button>
                </div> 
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    @component('components.widget', ['class' => 'box-primary', 'title' =>"Rekap Transaksi Jasa Pegawai" ])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="count_trx">
                    <thead>
                        <tr>
                            <th>Nama Pegawai</th>
                            <th>Nama Produk</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-gray font-14">
                            <th colspan="2" style="text-align:right">Total:</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
    @endcomponent
    <div class="modal fade account_model" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<div class="modal fade" tabindex="-1" role="dialog" id="detailUse">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Detail Penggunaan</h4>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                   <table class="table table-bordered table-striped" id="promo_detail">
                      <thead>
                        <tr>
                          <th>Nama</th>
                          <th>Waktu</th>
                        </tr>
                      </thead>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-default" class="close" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
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
        count_trx=$('#count_trx').DataTable({
                processing: true,
                serverSide: true,
                "ajax": "/reports/count_trx",
                columns: [
                    { data: 'nama_pegawai'},
                    { data: 'nama_product'},
                    { data: 'total', 'render' : function(data, type, row){
                        return formatRupiah(parseFloat(row.total).toFixed(0));
                    }},
                ],
                "footerCallback": function ( row, data, start, end, display ) {
                    var api = this.api(), data;
         
                    // Remove the formatting to get integer data for summation
                    var intVal = function ( i ) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '')*1 :
                            typeof i === 'number' ?
                                i : 0;
                    };
         
                    // Total over all pages
                    total = api
                        .column( 2 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
         
                    // Total over this page
                    pageTotal = api
                        .column( 2, { page: 'current'} )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
         
                    // Update footer
                    $( api.column( 2 ).footer() ).html(total);
                }
            });
            $('#location_id, #product_sr_date_filter').change(function() {
                updateMemberTrxReport();
            });

        });
        $(document).on('submit', '#form-trx-member', function(e) {
            e.preventDefault();
            updateMemberTrxReport();
        });
    function updateMemberTrxReport() {
        var data = {
            location_id: $('#location_id').val(),
            date_filter: $('#product_sr_date_filter').val(),
        };
        var out = [];

        for (var key in data) {
            out.push(key + '=' + encodeURIComponent(data[key]));
        }
        url_data = out.join('&');
        count_trx.ajax.url('/reports/count_trx?' + url_data).load();
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
        var output = tgl[2] + "/" +  tgl[1] + "/" + tgl[0];
        return output;
    }
    function formatDate2(date){
        var myDate = new Date(date);
        var tgl=date.split(/[ -]+/);
        var output = tgl[2] + "-" +  tgl[1] + "-" + tgl[0] + ' ' + tgl[3];
        return output;
    }
    function getMonth(val){
        var bulan = ['Januari', 'Februari', 'Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        return bulan[val-1];
    }
    function showDetail(eq){
        var id=$(eq).data('id');
        m = $('#promo_detail').DataTable();
        m.clear().draw(false);
        $.ajax({
            'url'   : "{{ URL::to('reports/detail-promo-use/') }}"+'/'+id,
            'dataType' : 'json',
            'type' : 'GET',
            'success' : function(response){
                arrData = response['data'];
                for(i = 0; i < arrData.length; i++){
                    m.row.add([
                      '<div class="text-left">'+arrData[i]['name']+'</div>',
                      '<div class="text-left">'+formatDate2(arrData[i]['transaction_date'])+'</div>',
                    ]).draw(false);
                }
            }
        })
    }
</script>
<script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>
@endsection