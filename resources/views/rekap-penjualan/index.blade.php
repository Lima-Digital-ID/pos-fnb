@extends('layouts.app')
@section('title', __('Rekap Penjualan Non Tunai'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Rekap Penjualan Non Tunai
            <small>@lang( 'Mengelola Rekap Penjualan Non Tunai anda' )</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Semua Rekap Penjualan Non Tunai anda')])
            @can('bahan.create')
                @slot('tool')
                    <div class="box-tools">
                        <a href="{{ action('RekapPenjualanOnlineController@create') }}" class="btn btn-block btn-primary"><i
                                class="fa fa-plus"></i> @lang( 'messages.add' )</a>
                    </div>
                @endslot
            @endcan
            @can('bahan.view')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="rekap-penjualan">
                        <thead>
                            <tr>
                                {{-- <th width="30px">#</th> --}}
                                <th>Tanggal Rekap</th>
                                <th>Total</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcan
        @endcomponent

        <!-- MODAL -->
        <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title" id="title">Modal Header</h4>
                    </div>
                    <div class="modal-body">
                        <h3>Detail Rekap Penjualan Non Tunai</h3>
                        <table class="table table-bordered table-striped" id="detailRekap">
                            <thead>
                                <tr>
                                    <th width="30px">No</th>
                                    <th>
                                        <div class="text-center">No Invoice</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- MODAL -->

    </section>
    <!-- /.content -->

@endsection
@section('javascript')
    <script>
        $(".showDetail").click(function() {
            alert("Handler for .click() called.");
        });

        function cekDetail(id) {
            $('#myModal').show();
            $('#detailRekap td').remove();
            $.ajax({
                type: "GET",
                url: "{{ url('rekap-penjualan/detail-json') }}/" + id, //json get site
                dataType: 'json',
                success: function(response) {
                    console.log(response);
                    var no = 1
                    arrData = response;
                    $('#title').html('Rekap Penjualan Non Tunai')
                    for (i = 0; i < arrData.length; i++) {
                        var table = '<tr><td><div class="text-center">' + no++ + '</div></td>' +
                            '<td><div class="text-center">' + arrData[i].invoice_no + '</div></td>';
                        $('#detailRekap tbody').append(table);
                    }
                }
            });
        }
    </script>
@endsection
