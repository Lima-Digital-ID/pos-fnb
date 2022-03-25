@extends('layouts.app')
@section('title', 'Promo')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Promo
    </h1>
    
</section>

<!-- Main content -->
<section class="content">

	<div class="box">
        <div class="box-header">
        	<h3 class="box-title">Promo</h3>
            	<div class="box-tools">
                    <a type="button" class="btn btn-block btn-primary" 
                    href="{{action('PromoController@create')}}" >
                    <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
                    </a>
                </div>
        </div>
        <div class="box-body">
                <div class="table-responsive">
            	<table class="table table-bordered table-striped" id="promo_table">
            		<thead>
            			<tr>
            				<th>Nama Promo</th>
            				<th>Lokasi</th>
                            <th>Diskon (%)</th>
                            <th>Promo Mulai</th>
                            <th>Promo Berahir</th>
                            <th>Limit Stok Promo</th>
                            <th>Ketentuan Limit</th>
                            <th>Status</th>
                            <th>@lang( 'messages.action' )</th>
            			</tr>
            		</thead>
            	</table>
                </div>
        </div>
    </div>

    <div class="modal fade discount_modal" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->
@stop
@section('javascript')
<script type="text/javascript">
    $(document).ready( function(){
        promo_table = $('#promo_table').DataTable({
            processing: true,
            serverSide: true,
            "ajax": {
                "url": "/promo",
            },
            columns: [
                    { data: 'promo_name'},
                    { data: 'name'},
                    { data: 'promo_diskon'},
                    { data: 'promo_start', render : function(data, type, row){
                        return formatDate(row.promo_start);
                    }},
                    { data: 'promo_end', render : function(data, type, row){
                        return formatDate(row.promo_end);
                    }},
                    { data: 'promo_limit'},
                    { data: 'promo_sk_limit', render : function(data, type, row){
                        return (row.promo_sk_limit == "no" ? 'Tidak Ada Limit' : (row.promo_sk_limit == 'day' ? 'Per Hari' : 'Stok'));
                    }},
                    { data: 'promo_status', render : function(data, type, row){
                        return row.promo_status == 1 ? 'Aktif' : 'Tidak Aktif';
                    }},
                    { data: 'action', name: 'action'}
                ],
        });


        $(document).on('click', 'button.delete_promo_button', function() {
            swal({
                title: LANG.sure,
                icon: 'warning',
                buttons: true,
                dangerMode: true,
            }).then(willDelete => {
                if (willDelete) {
                    var href = $(this).data('href');
                    var data = $(this).serialize();

                    $.ajax({
                        method: 'DELETE',
                        url: href,
                        dataType: 'json',
                        data: data,
                        success: function(result) {
                            if (result.success == true) {
                                toastr.success(result.msg);
                                promo_table.ajax.reload();
                            } else {
                                toastr.error(result.msg);
                            }
                        },
                    });
                }
            });
        });
        // Array to track the ids of the details displayed row
    });
    function formatDate(date){
        var myDate = new Date(date);
        var tgl=date.split('-');
        // var output = myDate.getDate() + "-" +  (myDate.getMonth()+1) + "-" + myDate.getFullYear();
        var output = tgl[2] + "-" +  tgl[1] + "-" + tgl[0];
        return output;
    }

</script>
@endsection
