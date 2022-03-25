@extends('layouts.app')
@section('title', __('lang_v1.payment_accounts'))

@section('content')
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>List Akun
        <small>@lang('account.manage_your_account')</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
             
    @component('components.widget', ['class' => 'box-primary', 'title' =>"List Akun" ])
        @can('akuntansi.akun')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" 
                    href="{{action('AkuntanController@createAkun')}}" >
                    <i class="fa fa-plus"></i> @lang( 'messages.add' )</a>
                 </div>
            @endslot
        @endcan
        @can('akuntansi.akun')
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="list_akun">
                    <thead>
                        <tr>
                            <th>No Akun</th>
                            <th>Nama Akun</th>
                            <th>Level</th>
                            <th>Main</th>
                            <!-- <th>Action</th>  -->
                        </tr>
                    </thead>
                </table>
            </div>
        @endcan
    @endcomponent
    <div class="modal fade account_model" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
<script src="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js?v=' . $asset_v) }}"></script>
<script>
    $(document).ready( function(){
        var users_table = $('#list_akun').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '/akuntansi',
                    // columnDefs: [ {
                    //     "targets": [4],
                    //     "orderable": false,
                    //     "searchable": false
                    // } ],
                    "columns":[
                        // {"data":"id_akun"},
                        {"data":"no_akun"},
                        {"data":"nama_akun"},
                        {"data":"level"},
                        {"data":"main", "render" : function(data, type, row){ return (row.main != null ? row.main : '')}},
                        // {"data":"action"}
                    ]
                });
        });
</script>
@endsection