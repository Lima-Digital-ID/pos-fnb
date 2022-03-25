@extends('layouts.app')
@section('title', 'List Setoran')

@section('content')
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>List Setoran
    </h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row no-print">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
              {!! Form::open(['url' => URL::current(), 'method' => 'get', 'id' => 'setoran_form' ]) !!}
                <div class="col-md-4" {{$location_id != null ? 'hidden' : ''}} >
                    <div class="form-group">
                      {!! Form::label('Lokasi : ') !!}
                        {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'id' => 'location_id']); !!}
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
    @component('components.widget', ['class' => 'box-primary', 'title' =>"List Setoran" ])
           
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="list_pengeluaran">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Deskripsi</th>
                            <th>Setoran Melalui</th>
                            <th>Tanggal</th>
                            <th>Total</th>
                            <th>User</th>
                            <th>Lokasi</th>
                            <th>Action</th> 
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-gray font-14">
                            <th colspan="4" style="text-align:right">Total:</th>
                            <th></th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
    @endcomponent
    <div class="modal fade account_model" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content" style="background-color: #24272900;">
      <!-- <div class="modal-header"  style="background-color: #24272900">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"  style="background-color: #fff">
          <span aria-hidden="true">&times;</span>
        </button>
      </div> -->
      <div class="modal-body" style="padding-bottom:10; padding-right:0">
        <button type="button" class="btn close" data-dismiss="modal">
          <i class="fa fa-times" style="font-size:28px;color:#fff"></i>
        </button>
      </div>
      <div class="modal-body" style="padding:0">
        <img src="" id="bukti_upload" width="100%">
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
        $('#bukti_upload').attr('src', uri).show();
    }
    $(document).ready( function(){
        dt = $('#list_pengeluaran').DataTable({
                processing: true,
                serverSide: true,
                "ajax": "/sells/jsonsetoran",
                aaSorting : [3, 'desc'],
                columns: [
                    {
                        "class":          "details-control",
                        "orderable":      false,
                        "data":           null,
                        "defaultContent": "",
                        "searchable": false
                    },
                    { data: 'deskripsi_pengeluaran'},
                    { data: 'method', render: function(data, type, row){
                        return (row.method == 'cash' ? 'Tunai' : (row.method == 'card' ? 'Kartu Kredit' : (row.method == 'bank_transfer' ? 'Transfer Bank' : '')));
                    }},
                    { data: 'tanggal', render: function(data, type, row){
                        return formatDate(row.tanggal);
                    }},
                    { data: 'total', render: function(data, type, row){
                        return 'Rp. '+formatRupiah(row.total);
                    }, "class" : "text-right"},
                    { data: 'first_name', 'render':function(data, type, row){
                        return row.first_name+' '+row.last_name;
                    }},
                    { data : 'cabang'},
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
                    $( api.column( 4 ).footer() ).html('Rp. '+formatRupiah(total));
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
    $(document).on('submit', '#setoran_form', function(e) {
            e.preventDefault();
            updateSetoranReport();
        });
    function updateSetoranReport() {
        var data = {
            location_id : $('#location_id').val(),
            date: $('#date').val(),
        };
        var out = [];

        for (var key in data) {
            out.push(key + '=' + encodeURIComponent(data[key]));
        }
        url_data = out.join('&');
        dt.ajax.url('/sells/jsonsetoran?' + url_data).load();
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