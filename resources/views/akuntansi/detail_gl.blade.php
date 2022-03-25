@extends('layouts.app')
@section('title', __('lang_v1.payment_accounts'))

@section('content')
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Detail General Ledger
        <small>@lang('account.manage_your_account')</small>
    </h1>
</section>
<?php
function formatDate($date){
    $tgl=date('d-m-Y', strtotime($date));
    return $tgl;
}
function formatRupiah($val){
    $a=number_format($val, 0, '.', '.');
    return $a;
}
?>
<!-- Main content -->
<section class="content">
             
    @component('components.widget', ['class' => 'box-primary', 'title' =>"General Ledger ".$akun->nama_akun ])
        
        @can('akuntansi.akun')
        @slot('tool')
                <div class="box-tools">
                     {!! Form::open(['url' => URL::to('akuntansi/detail-gl/'.$id), 'method' => 'post', 'class'=>'form-inline' ]) !!}
                        <div class="form-inline">
                            @if($user->role == 1)
                            <div class="form-group">
                              {!! Form::label('Lokasi : ') !!}
                                {!! Form::select('location_id', 
                                            $business_locations, $location_id, ['class' => 'form-control select2', 'placeholder' => 'Pilih Lokasi', 'required' => 'required']); !!}
                            </div>&nbsp;
                            @endif
                            <label>Pilih Bulan : </label>&nbsp;
                            <select data-plugin-selectTwo class="form-control select2" name="bulan" id="bulan" required>
                                <option value="">--Pilih Bulan--</option>
                                <option value="01">Januari</option>
                                <option value="02">Februari</option>
                                <option value="03">Maret</option>
                                <option value="04">April</option>
                                <option value="05">Mei</option>
                                <option value="06">Juni</option>
                                <option value="07">Juli</option>
                                <option value="08">Agustus</option>
                                <option value="09">September</option>
                                <option value="10">Oktober</option>
                                <option value="11">November</option>
                                <option value="12">Desember</option>
                            </select>
                            &nbsp;
                            <select data-plugin-selectTwo class="form-control select2" name="tahun" id="tahun" required>
                                <option value="">--Pilih Tahun--</option>
                                @for ($i = date('Y') - 5; $i <= date('Y'); $i++)
                                <option value="{{$i}}">{{$i}}</option>
                                @endfor
                            </select>&nbsp;
                            <button class="btn btn-primary"  onclick="cekAbsensiDate()"><i class="fa fa-search"></i></button>
                        </div>
                     {!! Form::close() !!}
                 </div>
            @endslot
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="">
                    <thead>
                        <tr>
                            <th class="text-center">Tanggal</th>
                            <th class="text-center">Debit</th>
                            <th class="text-center">Kredit</th>
                            <th class="text-center">Total Saldo</th> 
                        </tr>
                    </thead>
                    <?php $sub_total=($saldo_awal->jumlah_saldo != null ? $saldo_awal->jumlah_saldo : 0)?>
                    <tbody>
                        <tr>
                            <td class="text-center"></td>
                            <td class="text-right"></td>
                            <td class="text-right"></td>
                            <td class="text-right">{{formatRupiah($sub_total)}}</td>
                        </tr>
                    </tbody>
                    @foreach($data as $value)
                    <?php
                    $total=$value['detail']->jumlah_debit - $value['detail']->jumlah_kredit;
                    $sub_total+=$total;
                    ?>
                    <tbody>
                        <tr>
                            <td class="text-center">{{formatDate($value['date'])}}</td>
                            <td class="text-right">{{$value['detail']->jumlah_debit != null ? formatRupiah($value['detail']->jumlah_debit) : 0}}</td>
                            <td class="text-right">{{$value['detail']->jumlah_kredit != null ? formatRupiah($value['detail']->jumlah_kredit) : 0}}</td>
                            <td class="text-right">{{formatRupiah($sub_total)}}</td>
                        </tr>
                    </tbody>
                    @endforeach
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-center">Total</th>
                            <th class="text-right">{{formatRupiah($sub_total)}}</th>
                        </tr>
                    </tfoot>
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
        var bulan=@php print_r($bulan) @endphp;
        $('#bulan').val(bulan[1]);
        $('#tahun').val(bulan[0]);
    });
</script>
@endsection