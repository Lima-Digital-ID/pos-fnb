@extends('layouts.app')
@section('title', __('Satuan Bahan'))

@section('content')

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>@lang( 'Satuan Bahan' )
            <small>@lang( 'Mengelola satuan bahan anda' )</small>
        </h1>
        <!-- <ol class="breadcrumb">
                                    <li><a href="#"><i class="fa fa-dashboard"></i> Level</a></li>
                                    <li class="active">Here</li>
                                </ol> -->
    </section>

    <!-- Main content -->
    <section class="content">
        @component('components.widget', ['class' => 'box-primary', 'title' => __('Semua satuan bahan anda')])
            @can('unit.create')
                @slot('tool')
                    <div class="box-tools">
                        <button type="button" class="btn btn-block btn-primary btn-modal"
                            data-href="{{ action('UnitController@create') }}" data-container=".unit_modal">
                            <i class="fa fa-plus"></i> @lang( 'messages.add' )</button>
                    </div>
                @endslot
            @endcan
            @can('unit.view')
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="unit_table">
                        <thead>
                            <tr>
                                <th>@lang( 'unit.name' )</th>
                                <th>@lang( 'unit.short_name' )</th>
                                <th>@lang( 'unit.allow_decimal' ) @show_tooltip(__('tooltip.unit_allow_decimal'))</th>
                                <th>@lang( 'messages.action' )</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            @endcan
        @endcomponent

        <div class="modal fade unit_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
        </div>

    </section>
    <!-- /.content -->

@endsection
