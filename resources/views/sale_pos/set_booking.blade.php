@extends('layouts.app')
@section('title', 'Atur Booking')

@section('content')
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Atur Booking
    </h1>
</section>

<!-- Main content -->
<section class="content">
             
    @component('components.widget', ['class' => 'box-primary', 'title' =>"Atur Booking" ])
        @slot('tool')
            <div class="box-tools">
                <button type="button" class="btn btn-block btn-primary btn-modal" data-toggle="modal" data-target="#antrian_modal">
                    Atur Booking
                </button>
            </div>
        @endslot
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="list_booking">
                <thead>
                    <tr>
                        <th></th>
                        <th>Cabang</th>
                        <th>Jam Buka Booking</th>
                        <th>Jam Buka Booking</th>
                        <th>Total Kursi Per Jam</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent
    <div class="modal fade account_model" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<div class="modal fade" tabindex="-1" role="dialog" id="antrian_modal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title">Atur Booking</h4>
          </div>
            <form class="" method="post" action="" id="set_booking">
            <div class="modal-body">
              <div class="row">
                    <div class="form-group col-sm-6">
                        {!! Form::label('id_lokasi', 'Pilih Lokasi :') !!}
                        {!! Form::select('id_lokasi', 
                                    $location_option, null , ['class' => 'form-control', 'placeholder' => 'Pilih Lokasi', 'required']); !!}
                    </div>
                    <div class="form-group col-sm-6">
                        <label>Jam Awal Booking :</label>
                        <input type="time" id="open_book" name="open_book" class="form-control" required>
                    </div>
                    <div class="form-group col-sm-6">
                        <label>Jam Tutup Booking :</label>
                        <input type="time" id="close_book" name="close_book" class="form-control" required>
                    </div>
                    <div class="form-group col-sm-6">
                        <label>Total Booking dalam 1 Jam :</label>
                        <input type="number" id="total_booking" min="1" name="total_booking" class="form-control" required placeholder="Customer">
                    </div>
                    <div class="form-group col-sm-12">
                        <input type="checkbox" class="form-check-input" id="activeBook" name="activeBook" value="1">
                        <label class="form-check-label" for="activeBook"> Aktifkan Layanan Booking</label>
                    </div>
              </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
                <button class="btn btn-success">Simpan</button>
            </div>
            </form>
            
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script src="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js?v=' . $asset_v) }}"></script>

<script>
    
    $(document).ready( function(){
        var dt = $('#list_booking').DataTable({
            processing: true,
            serverSide: true,
            "ajax": "/sells/booking",
            columns: [
                { data: 'id'},
                { data: 'name'},
                { data: 'open_book', render: function(data, type, row){
                        return formatTime(row.open_book);
                }},
                { data: 'close_book', render: function(data, type, row){
                        return formatTime(row.close_book);
                }},
                { data: 'total_book_hours'}
            ]
        });

        $('#set_booking').on("submit", function(e){
            e.preventDefault();
            var data=$('#set_booking').serialize();
            $.ajax({
                url : "{{URL::to('sells/setBooking')}}",
                data : data,
                type : 'POST',
                dataType : 'json',
                success : function(response){
                    location.reload();
                }
            })
        });
    });
   
    function formatTime(date){
        var myDate = new Date(date);
        var tgl=date.split(':');
        // var output = myDate.getDate() + "-" +  (myDate.getMonth()+1) + "-" + myDate.getFullYear();
        var output = tgl[0] + ":" +  tgl[1];
        return output;
    }
</script>
@endsection