@extends('layouts.app')
@section('title', __( 'sale.list_pos'))

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header no-print">
    <h1>POS
    </h1>
</section>

<!-- Main content -->
<section class="content no-print">
    @component('components.filters', ['title' => __('report.filters')])
        @include('sell.partials.sell_list_filters')
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => __( 'sale.list_pos')])
        @can('sell.create')
            @slot('tool')
                <div class="box-tools">
                    <a class="btn btn-block btn-primary" href="{{action('SellPosController@create')}}">
                    <i class="fa fa-plus"></i> @lang('messages.add')</a>
                </div>
            @endslot
        @endcan
        @can('sell.view')
            <div class="table-responsive">
                <table class="table table-bordered table-striped ajax_view" id="sell_table">
                    <thead>
                        <tr>
                            <th>@lang('messages.date')</th>
                            <th>@lang('sale.invoice_no')</th>
                            <th>@lang('sale.customer_name')</th>
                            <th>@lang('sale.location')</th>
                            <th>@lang('sale.payment_status')</th>
                            <th>@lang('sale.total_amount')</th>
                            <th>@lang('sale.total_paid')</th>
                            <!--<th>@lang('purchase.payment_due')</th>-->
                            <th>@lang('contact.contact')</th>
                            <th>Keterangan</th>
                            <th>Kategori</th>
                            <th>Pegawai</th>
                            <th>@lang('messages.action')</th>
                        </tr>
                    </thead>
                    <tfoot>
                        <tr class="bg-gray font-17 footer-total text-center">
                            <td colspan="4"><strong>@lang('sale.total'):</strong></td>
                            <td id="footer_payment_status_count"></td>
                            <td><span class="display_currency" id="footer_sale_total" data-currency_symbol ="true"></span></td>
                            <td><span class="display_currency" id="footer_total_paid" data-currency_symbol ="true"></span></td>
                            <!-- <td class="text-left"><small>@lang('lang_v1.sell_due') - <span class="display_currency" id="footer_total_remaining" data-currency_symbol ="true"></span><br>@lang('lang_v1.sell_return_due') - <span class="display_currency" id="footer_total_sell_return_due" data-currency_symbol ="true"></span></small></td> -->
                            <td></td>
                            <td class="text-left" id="total_type_payment"></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endcan
    @endcomponent
</section>
<!-- /.content -->
<div class="modal fade payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade edit_payment_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<div class="modal fade register_details_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>
<div class="modal fade close_register_modal" tabindex="-1" role="dialog" 
    aria-labelledby="gridSystemModalLabel">
</div>

<!-- This will be printed -->
<!-- <section class="invoice print_section" id="receipt_section">
</section> -->


@stop

@section('javascript')
<script type="text/javascript">
$(document).ready( function(){
    //Date range as a button
    //Date range as a button
    $('#sell_list_filter_date_range').daterangepicker(
        dateRangeSettings,
        function (start, end) {
            $('#sell_list_filter_date_range').val(start.format(moment_date_format) + ' ~ ' + end.format(moment_date_format));
            sell_table.ajax.reload();
        }
    );
    $('#sell_list_filter_date_range').on('cancel.daterangepicker', function(ev, picker) {
        $('#sell_list_filter_date_range').val('');
        sell_table.ajax.reload();
    });
    
    sell_table = $('#sell_table').DataTable({
        processing: true,
        serverSide: true,
        aaSorting: [[0, 'desc']],
        "ajax": {
            "url": "/sells",
            "data": function ( d ) {
                if($('#sell_list_filter_date_range').val()) {
                    var start = $('#sell_list_filter_date_range').data('daterangepicker').startDate.format('YYYY-MM-DD');
                    var end = $('#sell_list_filter_date_range').data('daterangepicker').endDate.format('YYYY-MM-DD');
                    d.start_date = start;
                    d.end_date = end;
                }
                d.is_direct_sale = 0;

                d.id_kategori_harga = $('#sell_list_filter_id_kategori_harga').val();
                d.location_id = $('#sell_list_filter_location_id').val();
                d.customer_id = $('#sell_list_filter_customer_id').val();
                d.payment_status = $('#sell_list_filter_payment_status').val();
            }
        },
        columnDefs: [ {
            "targets": [8, 7],
            "orderable": false,
            "searchable": false
        } ],
        columns: [
            { data: 'transaction_date', name: 'transaction_date'  },
            { data: 'invoice_no', name: 'invoice_no'},
            { data: 'name', name: 'contacts.name'},
            { data: 'business_location', name: 'bl.name'},
            { data: 'payment_status', name: 'payment_status'},
            { data: 'final_total', name: 'final_total'},
            { data: 'total_paid', name: 'total_paid', 'searchable' : false},
            // { data: 'total_remaining', name: 'total_remaining'},
            { data: 'mobile', },
            { data: 'payment_first'},
            { data: 'kategori',name:'kh.kategori'},
            { data: 'nama_pegawai'},
            { data: 'action', name: 'action'}
        ],
        "fnDrawCallback": function (oSettings) {
            
            $('#footer_sale_total').text(sum_table_col($('#sell_table'), 'final-total'));

            $('#footer_total_paid').text(sum_table_col($('#sell_table'), 'total-paid'));

            $('#footer_total_remaining').text(sum_table_col($('#sell_table'), 'payment_due'));
            $('#footer_total_sell_return_due').text(sum_table_col($('#sell_table'), 'sell_return_due'));

            $('#footer_payment_status_count ').html(__sum_status_html($('#sell_table'), 'payment-status-label'));
            payment_total($('#sell_table'), 'payment_first', 'total-paid')
            __currency_convert_recursively($('#sell_table'));
        },
        createdRow: function( row, data, dataIndex ) {
            $( row ).find('td:eq(4)').attr('class', 'clickable_td');
        }
    });

    $(document).on('change', '#sell_list_filter_location_id, #sell_list_filter_id_kategori_harga,  #sell_list_filter_customer_id, #sell_list_filter_payment_status',  function() {
        sell_table.ajax.reload();
    });
});
function payment_total(table, class_name, class_name2) {
    var sum = 0;
    var temp = [];
    table.find('tbody').find('tr').each(function() {
        var is_there=false;
        var index=0;
        var isi = temp.length;
        var payment_first = $(this).find('.' + class_name).data('orig-value');
        $.each(temp, function(i, item){
            if (item.type == payment_first) {
                is_there = true;
                index = i;
            }
        })
        var total = parseFloat($(this).find('.' + class_name2).data('orig-value'));
        if (is_there == true) {
            temp[index]['type']=payment_first;
            temp[index]['total']+=total;
        }else{
            var data = {type : payment_first , total : total};
            temp.push(data)
        }
    });
    var label='<small>';
    $.each(temp, function(i, item){
        label+=(item.type ? item.type : '-') +' : <span class="display_currency" id="total_type_payment" data-currency_symbol ="true">'+ item.total+'</span><br>';
    })
    label+='</small>';
    console.log(label)
    $('#total_type_payment').html(label);
}
</script>
<script src="{{ asset('js/payment.js?v=' . $asset_v) }}"></script>
@endsection