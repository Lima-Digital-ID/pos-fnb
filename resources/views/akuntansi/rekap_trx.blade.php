@extends('layouts.app')
@section('title', __('lang_v1.payment_accounts'))

@section('content')
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">

<!-- Content Header (Page header) -->
<section class="content-header">
    <!-- <h1>Laporan Petty Cash
        <small>@lang('account.manage_your_account')</small>
    </h1> -->
</section>
    @php
        function formatRupiah($num){
            return number_format($num, 0, '.', '.');
        }
        function formatDate($date){
            $date=date_create($date);
            return date_format($date, 'd-m-Y');
        }
    @endphp

<!-- Main content -->
<section class="content">             
    @component('components.widget', ['class' => 'box-primary', 'title' =>"Laporan Petty Cash" ])
        @can('akuntansi.neraca')
            @slot('tool')
                <div class="box-tools">
                     {!! Form::open(['url' => action('AkuntanController@rekapPc'), 'method' => 'post', 'class'=>'form-inline' ]) !!}
                        <div class="form-inline">
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
                <table class="table table-bordered">
                    <thead id="table">
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Nama Akun</th>
                            <th>Kode</th>
                            <th>Keterangan</th>
                            <th>Debet</th>
                            <th>Kredit</th>
                            <th>Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                        $total_saldo=$total_debit=$total_kredit=$a=0;
                        @endphp
                        @foreach ($detail as $key => $value)
                            @if(!empty($value['data']))
                                @php $a++; $b=0 @endphp
                                @for($i=0; $i < count($value['data']); $i++)
                                    @foreach($value['data'][$i] as $k => $v)
                                        @php $b++ @endphp
                                        @if($b == 1)
                                        <tr>
                                            <td>{{$a}}</td>
                                            <td>{{formatDate($value['date'])}}</td>
                                        @else
                                        <tr>
                                            <td></td>
                                            <td></td>
                                        @endif
                                            <td>{{$v->nama_akun}}</td>
                                            <td>{{$v->no_akun}}</td>
                                            <td>{{$v->deskripsi}}</td>
                                        @if($v->id_akun == 20)
                                            @php 
                                            $total_saldo-=$v->jumlah;
                                            $total_debit+=$v->jumlah;
                                            @endphp
                                            <td></td>
                                            <td align="right">Rp. {{formatRupiah($v->jumlah)}}</td>
                                            <td align="right">Rp. {{formatRupiah($total_saldo)}}</td>
                                        @else
                                            @php 
                                            $total_saldo+=$v->jumlah; 
                                            $total_kredit+=$v->jumlah;
                                            @endphp
                                            <td align="right">Rp. {{formatRupiah($v->jumlah)}}</td>
                                            <td></td>
                                            <td align="right">Rp. {{formatRupiah($total_saldo)}}</td>
                                        @endif
                                        </tr>
                                    @endforeach
                                @endfor

                            @endif
                        @endforeach
                        <tr id="table">
                            <th colspan="5" class="text-center">Total Saldo</th>
                            <th class="text-right">Rp. {{formatRupiah($total_kredit)}}</th>
                            <th class="text-right">Rp. {{formatRupiah($total_debit)}}</th>
                            <th class="text-right">Rp. {{formatRupiah($total_saldo)}}</th>
                        </tr>
                    </tbody>
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
<script type="text/javascript">
    var bulan=@php print_r($bulan) @endphp;
    console.log(bulan);
    $('.box-title').html('Laporan Transaksi Bulan '+formatBulan(bulan));
    function formatBulan(val){
        var bulan = ['Januari', 'Februari', 'Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        var getMonth=val[1];
        return bulan[getMonth-1]+' '+val[0];
    }
</script>
@endsection