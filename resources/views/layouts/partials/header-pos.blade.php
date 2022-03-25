@inject('request', 'Illuminate\Http\Request')
<div class="col-md-12 no-print pos-header">
  <input type="hidden" id="pos_redirect_url" value="{{action('SellPosController@create')}}">
  <div class="row">

    <div class="col-md-10">

      <button type="button" title="Total Pendapatan Tunai" data-toggle="tooltip" data-placement="bottom" class="btn btn-success btn-flat m-6 btn-xs m-5"
          >
            <strong id="show_tunai">Uang Tunai :</strong>
      </button>
      <button type="button" title="Total Pendapatan Non Tunai" data-toggle="tooltip" data-placement="bottom" class="btn btn-success btn-flat m-6 btn-xs m-5"
          >
            <strong id="show_non_tunai">Non Tunai : </strong>
      </button>
      <button type="button" title="Total Pengeluaran" data-toggle="tooltip" data-placement="bottom" class="btn btn-success btn-flat m-6 btn-xs m-5"
          >
            <strong id="show_petty">Petty : </strong>
      </button>
      <button type="button" title="Total Pengeluaran" data-toggle="tooltip" data-placement="bottom" class="btn btn-success btn-flat m-6 btn-xs m-5"
          >
            <strong id="show_expense">Pengeluaran : </strong>
      </button>

      <button type="button" id="refresh" title="Refresh Data" data-toggle="tooltip" data-placement="bottom" class="btn btn-info btn-flat m-6 btn-xs m-5"
        onclick="location.reload();"  >
            <strong><i class="fa fa-refresh fa-lg"></i></strong>
      </button>

      <a href="{{ action('SellPosController@index')}}" title="{{ __('lang_v1.go_back') }}" data-toggle="tooltip" data-placement="bottom" class="btn btn-info btn-flat m-6 btn-xs m-5 pull-right">
        <strong><i class="fa fa-backward fa-lg"></i></strong>
      </a>

      <button type="button" id="close_register" title="{{ __('cash_register.close_register') }}" data-toggle="tooltip" data-placement="bottom" class="btn btn-danger btn-flat m-6 btn-xs m-5 btn-modal pull-right" data-container=".close_register_modal" 
          data-href="{{ action('CashRegisterController@getCloseRegister')}}">
            <strong><i class="fa fa-window-close fa-lg"></i></strong>
      </button>
      
      <button type="button" id="register_details" title="{{ __('cash_register.register_details') }}" data-toggle="tooltip" data-placement="bottom" class="btn btn-success btn-flat m-6 btn-xs m-5 btn-modal pull-right" data-container=".register_details_modal" 
          data-href="{{ action('CashRegisterController@getRegisterDetails')}}">
            <strong><i class="fa fa-briefcase fa-lg" aria-hidden="true"></i></strong>
      </button>

      <button title="@lang('lang_v1.calculator')" id="btnCalculator" type="button" class="btn btn-success btn-flat pull-right m-5 btn-xs mt-10 popover-default" data-toggle="popover" data-trigger="click" data-content='@include("layouts.partials.calculator")' data-html="true" data-placement="bottom">
            <strong><i class="fa fa-calculator fa-lg" aria-hidden="true"></i></strong>
        </button>


      <button type="button" title="{{ __('lang_v1.full_screen') }}" data-toggle="tooltip" data-placement="bottom" class="btn btn-primary btn-flat m-6 hidden-xs btn-xs m-5 pull-right" id="full_screen">
            <strong><i class="fa fa-window-maximize fa-lg"></i></strong>
      </button>

      <button type="button" id="view_suspended_sales" title="{{ __('lang_v1.view_suspended_sales') }}" data-toggle="tooltip" data-placement="bottom" class="btn bg-yellow btn-flat m-6 btn-xs m-5 btn-modal pull-right" data-container=".view_modal" 
          data-href="{{ action('SellController@index')}}?suspended=1">
            <strong><i class="fa fa-pause-circle-o fa-lg"></i></strong>
      </button>

      <button type="button" class="btn btn-info btn-flat m-6 btn-xs m-5 btn-modal pull-right" data-toggle="modal" data-target="#antrian_modal">
            <strong><i title="List Antrian" data-toggle="tooltip" data-placement="left" class="fa fa-list fa-lg" aria-hidden="true"></i></strong>
      </button>

      <!-- <button id="getAntrianList" type="button" class="btn btn-info btn-flat pull-right m-5 btn-xs mt-10" data-toggle="popover1" data-trigger="click" data-content='@include("layouts.partials.antrian")' data-html="true" data-placement="bottom">
            <strong><i title="List Antrian" data-toggle="tooltip" data-placement="left" class="fa fa-list fa-lg" aria-hidden="true"></i></strong>
        </button> -->

      @if(Module::has('Repair'))
        @include('repair::layouts.partials.pos_header')
      @endif

    </div>

    <div class="col-md-2">
      <div class="m-6 pull-right mt-15 hidden-xs"><strong>{{ @format_date('now') }}</strong></div>
    </div>
    
  </div>
</div>
