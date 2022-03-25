@extends('layouts.app')

@section('title', 'Edit Promo')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Edit Promo</h1>
</section>
<section class="content">
{!! Form::open(['url' => action('PromoController@update', [$promo->id]), 'method' => 'put', 'enctype' => 'multipart/form-data' ]) !!}
  <div class="row">
    <div class="col-md-12">
  @component('components.widget')

        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('name', __( 'unit.name' ) . ':*') !!}
              {!! Form::text('name', $promo->promo_name, ['class' => 'form-control', 'required', 'placeholder' => __( 'unit.name' ) ]); !!}
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('location_id', __('sale.location') . ':*') !!}
              {!! Form::select('location_id', $locations, $promo->location_id, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2', 'required']); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-sm-6" hidden>
          <div class="form-group">
            {!! Form::label('discount_type', __('sale.discount_type') . ':*') !!}
              {!! Form::select('discount_type', ['fixed' => __('lang_v1.fixed'), 'percentage' => __('lang_v1.percentage')], 'percentage', ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2', 'required']); !!}
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('discount_amount', __( 'sale.discount_amount' ) . ':*') !!}
              {!! Form::text('discount_amount', $promo->promo_diskon, ['class' => 'form-control input_number', 'required', 'placeholder' => __( 'sale.discount_amount' ) ]); !!}
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('starts_at', __( 'lang_v1.starts_at' ) . ':') !!}
              {!! Form::text('starts_at', $starts_at, ['class' => 'form-control', 'required', 'placeholder' => __( 'lang_v1.starts_at' ), 'readonly' ]); !!}
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('ends_at', __( 'lang_v1.ends_at' ) . ':') !!}
              {!! Form::text('ends_at', $ends_at, ['class' => 'form-control', 'required', 'placeholder' => __( 'lang_v1.ends_at' ), 'readonly' ]); !!}
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('Limit Penggunaan Promo:') !!}
              {!! Form::text('limit', $promo->promo_limit, ['class' => 'form-control', 'required']); !!}
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('Ketentuan Limit Promo:*') !!}
              {!! Form::select('limit_sk', ['stok' => 'Stok Limit', 'day' => 'Per Hari', 'no' => 'Tidak ada limit'], $promo->promo_sk_limit, ['class' => 'form-control select2', 'required']); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-2">
          <div class="form-group">
              {!! Form::checkbox('status', 1, $promo->promo_status, ['class' => 'input-icheck', 'id' => 'status']); !!}
            {!! Form::label('status', 'Aktif') !!}
          </div>
        </div>
        <div class="clearfix"></div><br>
        <div class="col-md-6">
          <div class="form-group">
            <button type="submit" class="btn btn-primary">@lang( 'messages.update' )</button>
            <!-- <button type="button" class="btn btn-default" data-dismiss="modal">@lang( 'messages.close' )</button> -->
          </div>
        </div>
    
  @endcomponent
    </div>
  </div>
{!! Form::close() !!}
  @stop
@section('javascript')
<script>
   $('.discount_date').datetimepicker({
        format: moment_date_format + ' ' + moment_time_format,
        ignoreReadonly: true,
    });
   $('#starts_at').datepicker({
         autoclose: true,
         format: datepicker_date_format
   });
   $('#ends_at').datepicker({
         autoclose: true,
         format: datepicker_date_format
   });
</script>
@endsection
