@extends('layouts.app')

@section('title', 'Add Promo')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
  <h1>Add Promo</h1>
</section>
<section class="content">
{!! Form::open(['url' => action('PromoController@store'), 'method' => 'post', 'enctype' => 'multipart/form-data' ]) !!}
  <div class="row">
    <div class="col-md-12">
  @component('components.widget')
        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('name', __( 'unit.name' ) . ':*') !!}
              {!! Form::text('name', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'unit.name' ) ]); !!}
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('location_id', __('sale.location') . ':*') !!}
              {!! Form::select('location_id', $locations, null, ['placeholder' => __('messages.please_select'), 'class' => 'form-control select2', 'required']); !!}
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
              {!! Form::text('discount_amount', null, ['class' => 'form-control input_number', 'required', 'placeholder' => 'Persen' ]); !!}
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('starts_at', __( 'lang_v1.starts_at' ) . ':') !!}
              {!! Form::text('starts_at', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'lang_v1.starts_at' ), 'readonly' ]); !!}
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('ends_at', __( 'lang_v1.ends_at' ) . ':') !!}
              {!! Form::text('ends_at', null, ['class' => 'form-control', 'required', 'placeholder' => __( 'lang_v1.ends_at' ), 'readonly' ]); !!}
          </div>
        </div>
        <div class="col-md-6">
          <div class="form-group">
            {!! Form::label('Limit Penggunaan Promo:') !!}
              {!! Form::text('limit', 0, ['class' => 'form-control', 'required']); !!}
          </div>
        </div>
        <div class="col-sm-6">
          <div class="form-group">
            {!! Form::label('Ketentuan Limit Promo:*') !!}
              {!! Form::select('limit_sk', ['stok' => 'Stok Limit', 'day' => 'Per Hari', 'no' => 'Tidak ada limit'], 'stok', ['class' => 'form-control select2', 'required']); !!}
          </div>
        </div>
        <div class="clearfix"></div>
        <div class="col-md-2" hidden>
          <div class="form-group">
              {!! Form::checkbox('status', 1, false, ['class' => 'input-icheck', 'id' => 'status']); !!}
            {!! Form::label('status', 'Aktif') !!}
          </div>
        </div>
        <div class="clearfix"></div><br>
        <div class="col-md-6">
          <div class="form-group">
            <button type="submit" class="btn btn-primary">@lang( 'messages.save' )</button>
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
