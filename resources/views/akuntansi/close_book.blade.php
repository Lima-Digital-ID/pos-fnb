@extends('layouts.app')
@section('title', __('lang_v1.payment_accounts'))

@section('content')
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">

<!-- Content Header (Page header) -->
<section class="content-header">
    <!-- <h1>Neraca Saldo
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
    @component('components.widget', ['class' => 'box-primary', 'title' =>"Tutup Buku" ])
        @can('akuntansi.close-book')
         {!! Form::open(['url' => action('AkuntanController@closeBook'), 'method' => 'post', 'class'=>'form-group' ]) !!}
            <div class="form-group">
                <div class="form-group">
                  {!! Form::label('Royalty Fee : ') !!}
                    {!! Form::number('royalty_fee', 0, ['id' => 'royalty_fee', 'class' => 'form-control', 'placeholder' => 'Total Royalty Fee', 'required' => 'required', 'readonly']); !!}
                </div>
                @if($user->role == 1)
                <div class="form-group">
                    {!! Form::label('Lokasi : ') !!}
                    {!! Form::select('location_id', 
                                $business_locations, $location_id, ['id' => 'location_id', 'class' => 'form-control select2', 'placeholder' => 'Pilih Lokasi', 'required' => 'required', 'onchange' => 'cekRoyalty()']); !!}
                </div>
                @endif
                <label>Pilih Bulan : </label>&nbsp;
                <select data-plugin-selectTwo class="form-control select2" name="bulan" id="bulan" required onchange="cekRoyalty()">
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
            </div>
            <div class="form-group">
                <label>Pilih Tahun : </label>&nbsp;
                <select data-plugin-selectTwo class="form-control select2" name="tahun" id="tahun" required onchange="cekRoyalty()">
                    <option value="">--Pilih Tahun--</option>
                    @for ($i = date('Y') - 5; $i <= date('Y'); $i++)
                    <option value="{{$i}}">{{$i}}</option>
                    @endfor
                </select>
            </div>
            <button class="btn btn-success" type="submit" name="submit" value="submit">Simpan</button>
         {!! Form::close() !!}
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
    var bulan=@php echo json_encode($bulan) @endphp ;
    // console.log(bulan);
    // $('.box-title').html('Neraca Saldo Bulan '+formatBulan(bulan));
    function formatBulan(val){
        var bulan = ['Januari', 'Februari', 'Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        var getMonth=val[1];
        return bulan[getMonth-1]+' '+val[0];
    }
    $('#bulan').val('{{$bulan[1]}}').change();
    $('#tahun').val('{{$bulan[0]}}').change();

    function cekRoyalty(){
        var bulan=$('#bulan').val();
        var tahun=$('#tahun').val();
        var location_id=$('#location_id').val();
        if (location_id != '' && bulan != '' && tahun != '') {
            $.ajax({
                "url"   : '{{action('AkuntanController@countRoyaltyFee')}}',
                "type"  : 'post',
                "dataType"  : 'json',
                "data"  : {date : tahun+'-'+bulan, location_id : location_id},
                "success" : function(response){
                    $('#royalty_fee').val(response);
                }
            })
        }
    }
</script>
@endsection