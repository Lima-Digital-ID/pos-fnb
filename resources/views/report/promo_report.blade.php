@extends('layouts.app')
@section('title', 'Rekap Promo')

@section('content')
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Rekap Promo
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
                        {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'id' => 'location_id', 'placeholder' => __('messages.please_select'), 'required']); !!}
                    </div>
                </div>
                <div class="col-md-8">
                    {!! Form::label('Pilih Bulan dan Tahun :') !!}
                    <div class="row">
                        <div class="col-md-6">
                            <select data-plugin-selectTwo class="form-control select2" name="bulan" id="bulan" required>
                                <option value="">--Pilih Bulan--</option>
                                <option value="01">Januari</option>
                                <option value="02">Februari</option>
                                <option value="03">Maret</option>
                                <option value="04">April</option>
                                <option value="05">Mei</option>
                                <option value="06">Juni</option>
                                <option value="07">Juli</option>
                                <option value="08">Agustus</option>
                                <option value="09">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <select data-plugin-selectTwo class="form-control select2" name="tahun" id="tahun" required>
                                <option value="">--Pilih Tahun--</option>
                                @for ($i = date('Y') - 5; $i <= date('Y'); $i++)
                                <option value="{{$i}}">{{$i}}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                
                <div class="col-sm-12">
                  <button type="submit" class="btn btn-primary pull-right">@lang('report.apply_filters')</button>
                </div> 
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    @component('components.widget', ['class' => 'box-primary', 'title' =>"Rekap Promo" ])
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="list_trx_member">
                    <thead>
                        <tr>
                            <th>Nama Promo</th>
                            <th>Masa Berlaku</th>
                            <th>Diskon</th>
                            <th>Lokasi</th>
                            <th>Total Transaksi</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-gray font-14">
                            <th colspan="4" style="text-align:right">Total:</th>
                            <th></th>
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
        list_trx_member=$('#list_trx_member').DataTable({
                processing: true,
                serverSide: true,
                "ajax": "/reports/promo-report",
                columns: [
                    { data: 'promo_name'},
                    { data: 'promo_start', 'render': function(data, type, row){
                        return formatDate(row.promo_start)+' - '+formatDate(row.promo_end);
                    }},
                    { data: 'promo_diskon'},
                    { data: 'lokasi'},
                    { data: 'total_trx'},
                    { data: 'action'},
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
                        .column( 4 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
         
                    // Total over this page
                    pageTotal = api
                        .column( 4, { page: 'current'} )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
         
                    // Update footer
                    $( api.column( 4 ).footer() ).html(total);
                }
            });
            $('#location_id, #bulan, #tahun').change(function() {
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
            bulan: $('#bulan').val(),
            tahun: $('#tahun').val(),
        };
        var out = [];

        for (var key in data) {
            out.push(key + '=' + encodeURIComponent(data[key]));
        }
        url_data = out.join('&');
        list_trx_member.ajax.url('/reports/promo-report?' + url_data).load();
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
<script type="text/javascript">
    var bulan=@php print_r($month) @endphp;
    $('#bulan').val(bulan[1]);
    $('#tahun').val(bulan[0]);
    function formatBulan(val){
        var bulan = ['Januari', 'Februari', 'Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        var getMonth=val[1];
        return bulan[getMonth-1]+' '+val[0];
    }
</script>
@endsection