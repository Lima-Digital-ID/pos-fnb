@extends('layouts.app')
@section('title', 'List Deposit, THR, Amortisasi')

@section('content')
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>List Deposit, THR, Amortisasi
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
    @component('components.widget', ['class' => 'box-primary', 'title' =>"List Deposit, THR, Amortisasi" ])
           
            <div>
                <button class="btn btn-primary" data-toggle="modal" data-target="#pengeluaran_modal">Tambah Pengeluaran</button>
                <button class="btn btn-primary" data-toggle="modal" data-target="#pengurangan_modal">Tambah Pengurangan</button>
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
                            <th id="totalAll"></th>
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
            {!! Form::open(['url' => action('SellPosController@savePengeluaranDt', 0), 'method' => 'post']) !!}
            <div class="modal-body">
                <div class="form-group" {{$location_id != null ? 'hidden' : ''}}>
                    {!! Form::select('id_lokasi', 
                                $business_locations, $location_id, ['class' => 'form-control', 'id' => 'id_lokasi', 'required' => 'required']); !!}
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::number('jml_pengeluaran', null, ['class' => 'form-control', 'id' => 'jml_pengeluaran', 'placeholder' => 'Jumlah Pengeluaran', 'required' => 'required']); !!}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::select('id_akun', 
                                        $akun_pengeluaran, 0, ['class' => 'form-control', 'placeholder' => 'Pilih Pengeluaran', 'required' => 'required']); !!}
                        </div>
                    </div>
                    <div class="col-sm-6" hidden>
                        <div class="form-group">
                            {!! Form::select('tipe_manajemen', 
                                        array('-' => '-'), 0, ['class' => 'form-control']); !!}
                            {!! Form::hidden('is_entry', 1, ['class' => 'form-control']); !!}
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
<div class="modal fade" tabindex="-1" role="dialog" id="pengurangan_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Input Pengurangan Deposit dll</h4>
            </div>
            {!! Form::open(['url' => action('SellPosController@savePengeluaranDt', 0), 'method' => 'post']) !!}
            <div class="modal-body">
                <div class="form-group" {{$location_id != null ? 'hidden' : ''}}>
                    {!! Form::select('id_lokasi', 
                                $business_locations, $location_id, ['class' => 'form-control', 'id' => 'id_lokasi2', 'required' => 'required', 'onchange'  => 'getPegawai()']); !!}
                </div>
                <div class="row">
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::number('jml_pengeluaran', null, ['class' => 'form-control', 'id' => 'jml_pengeluaran', 'placeholder' => 'Jumlah Pengeluaran', 'required' => 'required']); !!}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            {!! Form::select('id_akun', 
                                        $akun_pengeluaran2, 0, ['class' => 'form-control', 'placeholder' => 'Pilih Pengeluaran', 'required' => 'required', 'onchange' => 'cekDeposit(this.value)']); !!}
                        </div>
                    </div>
                    <div class="col-sm-6" hidden>
                        <div class="form-group">
                            {!! Form::select('tipe_manajemen', 
                                        array('-' => '-'), 0, ['class' => 'form-control']); !!}
                            {!! Form::hidden('is_entry', 0, ['class' => 'form-control']); !!}
                        </div>
                    </div>
                    <div id="form_deposit" hidden>
                        <div class="col-sm-6">
                            <div class="form-group">
                                {!! Form::select('id_pegawai', 
                                        $option_pegawai, 0, ['class' => 'form-control', 'id'=> 'id_pegawai', 'placeholder' => 'Pilih Pegawai']); !!}
                                <!-- <select class="form-control" id="id_pegawai" name="id_pegawai">
                                    
                                </select> -->
                            </div>
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
    function cekDeposit(val){
        if (val == 129) {
            $('#form_deposit').show();
        }else{
            $('#form_deposit').hide();
        }
    }
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
                "ajax": "/sells/jsonpengeluaran_dt/0",
                aaSorting : [1, 'desc'],
                columns: [
                    { data: 'tanggal', render: function(data, type, row){
                        return formatDate(row.tanggal);
                    }},
                    { data: 'deskripsi_pengeluaran'},
                    { data: 'total', render: function(data, type, row){
                        return '<span class="sub-total" data-amount="'+row.amount+'">'+'Rp. '+(row.is_entry == 0 ? '-': '')+formatRupiah(row.total)+'</span>';
                    }, "class" : "text-right"},
                    { data: 'notes'},
                    { data: 'first_name', 'render':function(data, type, row){
                        return row.first_name+' '+row.last_name;
                    }},
                    { data: 'cabang'},
                    { data: 'action', "class" : "text-center"},
                ],"fnDrawCallback": function (oSettings) {
                    var a=sum_total($('#list_pengeluaran'));
                    
                    $('#totalAll').text('Rp. '+formatRupiah(a));
                },
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
    function sum_total(table) {
        var sum = 0;
        table
            .find('tbody')
            .find('tr')
            .each(function() {
                if (
                    parseFloat(
                        $(this)
                            .find('.sub-total')
                            .data('amount')
                    )
                ) {
                    sum += parseFloat(
                        $(this)
                            .find('.sub-total')
                            .data('amount')
                    );
                }
            });
        return sum;
    }
    function updateManajemenReport() {
        var data = {
            location_id : $('#location_id').val(),
            date: $('#date').val(),
        };
        var out = [];

        for (var key in data) {
            out.push(key + '=' + encodeURIComponent(data[key]));
        }
        url_data = out.join('&');
        dt.ajax.url('/sells/jsonpengeluaran_dt/0?' + url_data).load();
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
    function getPegawai(){
        var id=$('#id_lokasi2').val();
        $('#id_pegawai').empty();
        var pegawai=$('#id_pegawai');
        $.ajax({
            url : '{{URL::to('users/get-pegawai/')}}'+'/'+id, 
            type : 'GET',
            dataType : 'json',
            success: function(response){
                arrData=response;
                var option = '<option value="">Pilih Pegawai</option>';
                for (var i = 0; i < arrData.length; i++) {
                    option+='<option value="'+arrData[i]['id']+'">'+arrData[i]['nama_pegawai']+'</option>';
                }
                // console.log(option);
                pegawai.append(option);
            }
        });
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