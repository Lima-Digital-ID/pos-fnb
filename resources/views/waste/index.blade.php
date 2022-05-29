@extends('layouts.app')
@section('title', __('Waste'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>Waste
            <small>@lang('Mengelola Waste anda')</small>
        </h1>
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Semua Waste anda')])
            @can('waste.create')
                @slot('tool')
                    <div class="box-tools">
                        <a href="{{ action('WasteController@create') }}" class="btn btn-block btn-primary"><i
                                class="fa fa-plus"></i> @lang('messages.add')</a>
                    </div>
                @endslot
            @endcan
            @can('waste.view')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="waste">
                        <thead>
                            <tr>
                                {{-- <th width="30px">#</th> --}}
                                <th>Tanggal</th>
                                <th>No Referensi</th>
                                <th>Total Bahan</th>
                                <th>Total Produk</th>
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
                        <h3>Detail Waste</h3>
                        <table class="table table-bordered table-striped" id="detailRekapProduk">
                            <thead>
                                <tr>
                                    <th width="30px">No</th>
                                    <th>
                                        <div class="text-center">Nama Produk</div>
                                    </th>
                                    <th>
                                        <div class="text-center">Kuantitas</div>
                                    </th>
                                    <th>
                                        <div class="text-center">Kategori Harga</div>
                                    </th>
                                    <th>
                                        <div class="text-center">Harga Produk</div>
                                    </th>
                                    <th>
                                        <div class="text-center">Subtotal Produk</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        <table class="table table-bordered table-striped" id="detailRekapBahan">
                            <thead>

                                <tr>
                                    <th width="30px">No</th>
                                    <th>
                                        <div class="text-center">Nama Bahan</div>
                                    </th>
                                    <th>
                                        <div class="text-center">Kuantitas</div>
                                    </th>
                                    <th>
                                        <div class="text-center">Harga Bahan</div>
                                    </th>
                                    <th>
                                        <div class="text-center">Subtotal Bahan</div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
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
            $('#detailRekapProduk td').remove();
            $('#detailRekapBahan td').remove();
            $.ajax({
                type: "GET",
                url: "{{ url('waste/get-detail-produk/') }}/" + id, //json get site
                dataType: 'json',
                success: function(response) {
                    arrData = response;
                    var no = 1
                    for (i = 0; i < arrData.length; i++) {
                        var table = '<tr><td><div class="text-center">' + no++ + '</div></td>' +
                            '<td><div class="text-center">' + arrData[i].name + '</div></td>' +
                            '<td><div class="text-center">' + arrData[i].qty + '</div></td>' +
                            '<td><div class="text-center">' + arrData[i].category_price + '</div></td>' +
                            '<td><div class="text-center">' + new Intl.NumberFormat("id-ID", {style: "currency", currency: "IDR"}).format(arrData[i].price_product) + '</div></td>' +
                            '<td><div class="text-center">' + new Intl.NumberFormat("id-ID", {style: "currency", currency: "IDR"}).format(arrData[i].price_product * arrData[i].qty) +
                            '</div></td>'
                        $('#detailRekapProduk tbody').append(table);
                    }
                }
            });
            $.ajax({
                type: "GET",
                url: "{{ url('waste/get-detail-bahan/') }}/" + id, //json get site
                dataType: 'json',
                success: function(response) {
                    arrData = response;
                    $('#title').html('Waste ' + arrData[0].no_reference)
                    var no = 1
                    for (i = 0; i < arrData.length; i++) {
                        var table = '<tr><td><div class="text-center">' + no++ + '</div></td>' +
                            '<td><div class="text-center">' + arrData[i].nama_bahan + '</div></td>' +
                            '<td><div class="text-center">' + arrData[i].qty + '</div></td>' +
                            '<td><div class="text-center">' + new Intl.NumberFormat("id-ID", {style: "currency", currency: "IDR"}).format(arrData[i].price_ingredient) + '</div></td>' +
                            '<td><div class="text-center">' + new Intl.NumberFormat("id-ID", {style: "currency", currency: "IDR"}).format(arrData[i].price_ingredient * arrData[i].qty) +
                            '</div></td>';
                        $('#detailRekapBahan tbody').append(table);
                    }
                }
            });
        }
    </script>
@endsection
