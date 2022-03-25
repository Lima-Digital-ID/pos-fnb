@extends('layouts.app')
@section('title', __('report.trending_products'))

@section('css')
    {!! Charts::styles(['highcharts']) !!}
@endsection

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>{{ __('report.trending_products')}}</h1>
</section>

<!-- Main content -->
<section class="content">
    <div class="row no-print">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
              {!! Form::open(['url' => action('ReportController@getTrendingProducts'), 'method' => 'get' ]) !!}
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('location_id',  __('purchase.business_location') . ':') !!}
                        {!! Form::select('location_id', $business_locations, null, ['class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => __('messages.please_select')]); !!}
                    </div>
                </div>
                <div class="col-md-4" hidden>
                    <div class="form-group">
                        {!! Form::label('Rentang Tanggal :') !!}
                        {!! Form::text('datetimes', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'datetimes', 'readonly']); !!}
                    </div>
                </div>
                <div class="col-md-3" hidden>
                    <div class="form-group">
                        {!! Form::label('trending_product_date_range',__('report.date_range') .  ':') !!}
                        {!! Form::text('date_range', null, ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'trending_product_date_range', 'readonly']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('Pilih Tanggal :') !!}
                        {!! Form::text('date', date('d-m-Y', strtotime($date)), ['placeholder' => __('lang_v1.select_a_date_range'), 'class' => 'form-control', 'id' => 'date', 'readonly']); !!}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        {!! Form::label('limit', __('lang_v1.no_of_products') . ':') !!} @show_tooltip(__('tooltip.no_of_products_for_trending_products'))
                        {!! Form::number('limit', 5, ['placeholder' => __('lang_v1.no_of_products'), 'class' => 'form-control', 'min' => 1]); !!}
                    </div>
                </div>
                
                <div class="col-sm-12">
                  <button type="submit" class="btn btn-primary pull-right">@lang('report.apply_filters')</button>
                  <a href="{{URL::to('reports/trending-products2')}}" class="btn btn-info pull-right" style="margin-right:10px">Report Trending 2</a>
                </div> 
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    
    <div class="row" hidden>
        <div class="col-xs-12">
            @component('components.widget', ['class' => 'box-primary'])
                @slot('title')
                    @lang('report.top_trending_products') @show_tooltip(__('tooltip.top_trending_products'))
                @endslot
                {!! $chart->html() !!}
            @endcomponent
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            @component('components.widget', ['class' => 'box-primary'])
                @slot('title')
                    @lang('report.top_trending_products') @show_tooltip(__('tooltip.top_trending_products'))
                @endslot
                {!! $trending->html() !!}
            @endcomponent
        </div>
    </div>
    <div class="row no-print">
        <div class="col-sm-12">
            <button type="button" class="btn btn-primary pull-right" 
            aria-label="Print" onclick="window.print();"
            ><i class="fa fa-print"></i> @lang( 'messages.print' )</button>
        </div>
    </div>

</section>
<!-- /.content -->

@endsection

@section('javascript')
    <script type="text/javascript">
      $('#date').datepicker({
             autoclose: true,
             format: datepicker_date_format
       });
    </script>
    <script src="{{ asset('js/report.js?v=' . $asset_v) }}"></script>

    <script>
    $(function() {
      $('input[name="datetimes"]').daterangepicker({
        autoUpdateInput: false,
          locale: {
              cancelLabel: 'Clear'
          },
        "timePicker": true,
        ranges: {
            'Today': [moment(), moment()],
            'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Last 7 Days': [moment().subtract(6, 'days'), moment()],
            'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            'This Month': [moment().startOf('month'), moment().endOf('month')],
            'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        },
        "timePicker24Hour": true,
        // "timePickerSeconds": true,
        // startDate: moment().startOf('hour'),
        // endDate: moment().startOf('hour').add(32, 'hour'),
        // locale: {
        //   format: 'YYYY-MM-DD hh:mm A'
        // }
      });

      $('input[name="datetimes"]').on('apply.daterangepicker', function(ev, picker) {
          $(this).val(picker.startDate.format('DD-MM-YYYY HH:mm') + ' ~ ' + picker.endDate.format('DD-MM-YYYY HH:mm'));
      });

      $('input[name="datetimes"]').on('cancel.daterangepicker', function(ev, picker) {
          $(this).val('');
      });
    });
    </script>
    {!! Charts::assets(['highcharts']) !!}
    {!! $chart->script() !!}
    {!! $trending->script() !!}
@endsection