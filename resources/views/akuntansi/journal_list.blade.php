@extends('layouts.app')
@section('title', __('lang_v1.payment_accounts'))

@section('content')
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">

<!-- Content Header (Page header) -->
<section class="content-header">
    <!-- <h1>Jurnal Akuntansi
    </h1> -->
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' =>"Jurnal Akuntansi" ])
        @can('akuntansi.jurnal')
            @slot('tool')
                <div class="box-tools">
                     {!! Form::open(['url' => action('AkuntanController@jurnal'), 'method' => 'post', 'class'=>'form-inline' ]) !!}
                        <input type="date" name="date" class="form-control" required>
                        <button class="btn btn-success">cari</button>
                        <a class="btn btn-primary" 
                        href="{{action('AkuntanController@createJournal')}}" >
                        <i class="fa fa-plus"></i> @lang( 'messages.add' )</a>
                     {!! Form::close() !!}
                 </div>
            @endslot
        @endcan
        @can('akuntansi.jurnal')
            @php
                function formatRupiah($num){
                    return number_format($num, 0, '.', '.');
                }
                function formatDate($date){
                    $date=date_create($date);
                    return date_format($date, 'd-m-Y');
                }
            @endphp
            <h4 id="titleJurnal">Jurnal Umum Bulan November 2019</h4>
            <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th width="110px">Tanggal</th>
                                <th>Keterangan</th>
                                <th>Reff</th>
                                <th>Debit</th>
                                <th>Kredit</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        @foreach ($data as $key => $value)
                            @php $k=0 @endphp
                            @foreach ($data[$key]['detail'] as $k => $v)
                                @if($k==0)
                            <tr style="background-color:#3c8dbc; color:white">
                                <td>{{formatDate($v->tanggal)}}</td>
                                    @if($v->keterangan == 'akun')                                
                                <td>{{$v->nama_akun}}</td>
                                    @else
                                <td align="center">{{$v->nama_akun}}</td>
                                    @endif
                                <td>{{$v->no_akun}}</td>

                                    @if($v->tipe == 'KREDIT')
                                
                                <td><div class="text-left"></div></td>
                                <td><div class="text-right">Rp. {{formatRupiah($v->jumlah)}}</div></td>
                                <th></th>
                            </tr>
                                    @else

                                <td><div class="text-right">Rp. {{formatRupiah($v->jumlah)}}</div></td>
                                <td><div class="text-left"></div></td>
                                <th></th>
                            </tr>
                                    @endif
                                @else
                            <tr>
                                <td></td>
                                    @if($v->keterangan == 'akun')                                
                                <td>{{$v->nama_akun}}</td>
                                    @else
                                <td align="center">{{$v->nama_akun}}</td>
                                    @endif
                                <td>{{$v->no_akun}}</td>
                                    @if($v->tipe == 'KREDIT')
                                
                                <td><div class="text-left"></div></td>
                                <td><div class="text-right">Rp. {{formatRupiah($v->jumlah)}}</div></td>
                                <th></th>
                            </tr>
                                    @else

                                <td><div class="text-right">Rp. {{formatRupiah($v->jumlah)}}</div></td>
                                <td><div class="text-left"></div></td>
                                <th></th>
                            </tr>
                                    @endif
                                @endif
                                
                                @php $k++ @endphp
                            
                            @endforeach
                            <tr>
                                <th></th>
                                <th>{{$value['deskripsi']}}</th>
                                <th colspan="4"></th>
                            </tr>
                        @endforeach
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
    var bulan='{{$date}}';
    console.log(bulan);
    $('#titleJurnal').html('Jurnal Umum '+formatBulan(bulan));
    function formatBulan(val){
        var bulan = ['Januari', 'Februari', 'Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        val=val.split('-');
        var getMonth=val[1];
        return val[2]+' '+bulan[getMonth-1]+' '+val[0];
    }
</script>
@endsection