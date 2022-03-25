@extends('layouts.app')
@section('title', 'List Pengeluaran Manajemen')

@section('content')
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>List Pengeluaran Manajemen
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row no-print">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
              {!! Form::open(['url' => URL::current(), 'method' => 'get', 'id' => 'manajemen_form' ]) !!}
                <div class="col-md-4" {{$location_id != null ? 'hidden' : ''}} >
                    <div class="form-group">
                      {!! Form::label('Lokasi : ') !!}
                        {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'id' => 'location_id']); !!}
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        {!! Form::label('Tipe :') !!}
                        {!! Form::select('tipe_manajemen', 
                                    array('Bahan Baku' => 'Bahan Baku', 'Operasional' => 'Operasional'), 0, ['class' => 'form-control', 'id' => 'tipe_manajemen', 'required' => 'required', 'placeholder' => 'Pilih Tipe']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('Rentang Tanggal :') !!}
                        {!! Form::text('date', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'date', 'readonly']); !!}
                    </div>
                </div>
                
                <div class="col-sm-12">
                  <button type="submit" class="btn btn-primary pull-right">@lang('report.apply_filters')</button>
                </div> 
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    @component('components.widget', ['class' => 'box-primary', 'title' =>"List Pengeluaran Manajemen" ])
           
            <div>
                <button class="btn btn-primary" data-toggle="modal" data-target="#pengeluaran_modal">Tambah Pengeluaran</button>
            </div>
            <br>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="list_pengeluaran">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Deskripsi</th>
                            <th>Total</th>
                            <th>Catatan</th>
                            <th>User</th>
                            <th>Lokasi</th>
                            <th>Action</th> 
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-gray font-14">
                            <th colspan="2" style="text-align:right">Total:</th>
                            <th></th>
                            <th colspan="4"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
    @endcomponent
    <div class="modal fade account_model" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<div class="modal fade" tabindex="-1" role="dialog" id="pengeluaran_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Input Pengeluaran</h4>
            </div>
            {!! Form::open(['url' => action('SellPosController@savePengeluaranDt', 1), 'method' => 'post']) !!}
            <div class="modal-body">
                
                <div class="form-group" {{$location_id != null ? 'hidden' : ''}}>
                    {!! Form::select('id_lokasi', 
                                $business_locations, $location_id, ['class' => 'form-control', 'required' => 'required']); !!}
                </div>
                
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::number('jml_pengeluaran', null, ['class' => 'form-control', 'id' => 'jml_pengeluaran', 'placeholder' => 'Jumlah Pengeluaran', 'required' => 'required']); !!}
                        </div>
                    </div>
                    <div class="col-sm-6" hidden>
                        <div class="form-group">
                            {!! Form::select('id_akun', 
                                        $akun_pengeluaran, 0, ['class' => 'form-control', 'required' => 'required']); !!}
                            {!! Form::hidden('is_entry', 1, ['class' => 'form-control']); !!}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::select('tipe_manajemen', 
                                        array('Bahan Baku' => 'Bahan Baku', 'Operasional' => 'Operasional'), 0, ['class' => 'form-control', 'required' => 'required']); !!}
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    {!! Form::textarea('desc_pengeluaran', null, ['class' => 'form-control', 'id' => 'desc_pengeluaran', 'placeholder' => 'Deskripsi', 'required' => 'required']); !!}
                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-primary">simpan</button>
            </div>
            {!! Form::close() !!}

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
    function format ( d ) {
        var div = $('<div/>')
        .addClass('loading')
        .text('Loading...');
        $.ajax({
            method: "GET",
            url: '/sells/pengeluaran/'+d.id,
            dataType: 'html',
            // async:false,
            success: function(result){
                div.html(result).removeClass('loading');
            }
        });
        return div;
        // return 'Full name:'+d.id+' The child row can contain any data you wish, including links, images, inner tables etc.';
    }
    function sendUri(val){
        var uri=$(val).data('href');
        console.log(uri);
        $('#bukti_upload').attr('src', uri).show();
    }
    $(document).ready( function(){
        dt = $('#list_pengeluaran').DataTable({
                processing: true,
                serverSide: true,
                "ajax": "/sells/jsonpengeluaran_dt/1",
                aaSorting : [0, 'desc'],
                columns: [
                    { data: 'tanggal', render: function(data, type, row){
                        return formatDate(row.tanggal);
                    }},
                    { data: 'deskripsi_pengeluaran'},
                    { data: 'total', render: function(data, type, row){
                        return 'Rp. '+formatRupiah(row.total);
                    }, "class" : "text-right"},
                    { data: 'tipe_manajemen'},
                    { data: 'first_name', 'render':function(data, type, row){
                        return row.first_name+' '+row.last_name;
                    }},
                    { data: 'cabang'},
                    { data: 'action', "class" : "text-center"},
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
                    $( api.column( 2 ).footer() ).html('Rp. '+formatRupiah(total));
                }
            });
        var detailRows = [];
 
        $('#list_pengeluaran tbody').on( 'click', 'tr td.details-control', function () {
            var tr = $(this).closest('tr');
            var row = dt.row( tr );
            var idx = $.inArray( tr.attr('id'), detailRows );
     
            if ( row.child.isShown() ) {
                tr.removeClass( 'details' );
                row.child.hide();
     
                // Remove from the 'open' array
                detailRows.splice( idx, 1 );
            }
            else {
                tr.addClass( 'details' );
                row.child( format( row.data() ) ).show();
     
                // Add to the 'open' array
                if ( idx === -1 ) {
                    detailRows.push( tr.attr('id') );
                }
            }
        } );
     
        // On each draw, loop over the `detailRows` array and show any child rows
        dt.on( 'draw', function () {
            $.each( detailRows, function ( i, id ) {
                $('#'+id+' td.details-control').trigger( 'click' );
            } );
        } );
    });
    $(document).on('submit', '#manajemen_form', function(e) {
            e.preventDefault();
            updateManajemenReport();
        });
    function updateManajemenReport() {
        var data = {
            location_id : $('#location_id').val(),
            tipe_manajemen : $('#tipe_manajemen').val(),
            date: $('#date').val(),
        };
        var out = [];

        for (var key in data) {
            out.push(key + '=' + encodeURIComponent(data[key]));
        }
        url_data = out.join('&');
        dt.ajax.url('/sells/jsonpengeluaran_dt/1?' + url_data).load();
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
      $('input[name="date"]').daterangepicker({
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
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
      });

      $('input[name="date"]').on('apply.daterangepicker', function(ev, picker) {
          $(this).val(picker.startDate.format('DD-MM-YYYY') + ' ~ ' + picker.endDate.format('DD-MM-YYYY'));
      });

      $('input[name="date"]').on('cancel.daterangepicker', function(ev, picker) {
          $(this).val('');
      });
    });
    </script>
@endsection