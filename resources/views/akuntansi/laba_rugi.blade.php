@extends('layouts.app')
@section('title', __('lang_v1.payment_accounts'))

@section('content')
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">

<!-- Content Header (Page header) -->
<section class="content-header">
    <!-- <h1>Jurnal Akuntansi
    </h1> -->
</section>
<style>
    tbody tr td:last-child{
        text-align: right;
    }
</style>
<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' =>"Laporan Laba Rugi" ])
        @can('akuntansi.profit-loss')
            @slot('tool')
                <div class="box-tools">
                     {!! Form::open(['url' => action('AkuntanController@labaRugi'), 'method' => 'post', 'class'=>'form-inline' ]) !!}
                        <div class="form-inline">
                            <div class="form-group {{$user_location != null ? 'hide' : ''}}">
                                <!--<div>-->
                                {!! Form::label('Lokasi : ') !!}
                                    {!! Form::select('location_id', 
                                                $business_locations, $location_id, ['class' => 'form-control select2', 'placeholder' => 'Pilih Lokasi', 'required' => 'required']); !!}
                                <!--</div>-->
                            </div>
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
        @endcan
        @can('akuntansi.profit-loss')
            @php
                function formatRupiah($num){
                    return number_format($num, 0, '.', '.');
                }
                function formatDate($date){
                    $date=date_create($date);
                    return date_format($date, 'd-m-Y');
                }
            @endphp
            @if($location_id != null)
            <h4 id="title"></h4>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead style="background-color:#3c8dbc; color:white">
                        <tr>
                            <th>Pendapatan dari Penjualan</th>
                            <th width="20%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $netto = $akuntansi['penjualan'] - $akuntansi['hpp'] - $akuntansi['potongan_aplikasi'];    
                        ?>
                        <tr>
                            <td>Penjualan Makanan dan Minuman</td>
                            <td>{{number_format($akuntansi['penjualan'],0,',','.')}}</td>
                        </tr>
                        <tr>
                            <td>HPP Makanan dan Minuman</td>
                            <td>{{number_format($akuntansi['hpp'],0,',','.')}}</td>
                        </tr>
                        <tr>
                            <td>Potongan Aplikasi</td>
                            <td>{{number_format($akuntansi['potongan_aplikasi'],0,',','.')}}</td>
                        </tr>
                        <tr bgColor="#cad6e3">
                            <td>Penjualan (Omset Netto)</td>
                            <td>{{number_format($netto,0,',','.')}}</td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-bordered">
                    <thead class="mt-3" style="background-color:#3c8dbc; color:white">
                        <tr>
                            <th>Pengeluaran</th>
                            <th width="20%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            $pengeluaran = $akuntansi['waste_bahan'] + $akuntansi['promo_produk'] + $akuntansi['waste_produk'] + $akuntansi['pengeluaran'];
                        ?>
                        <tr>
                            <td>Pengeluaran HPP Bahan Baku</td>
                            <td>{{number_format($akuntansi['waste_bahan'],0,',','.')}}</td>
                        </tr>
                        <tr>
                            <td>Pengeluaran Promo</td>
                            <td>{{number_format($akuntansi['promo_produk'],0,',','.')}}</td>
                        </tr>
                        <tr>
                            <td>Pengeluaran Waste</td>
                            <td>{{number_format($akuntansi['waste_produk'],0,',','.')}}</td>
                        </tr>
                        <tr>
                            <td>Pengeluaran Outlet</td>
                            <td>{{number_format($akuntansi['pengeluaran'],0,',','.')}}</td>
                        </tr>
                        <tr bgColor="#cad6e3">
                            <td>Total Pengeluaran</td>
                            <td>{{number_format($pengeluaran,0,',','.')}}</td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-bordered">
                    <?php 
                        $totalPengeluaranOther = $akuntansi['pengeluaran_manajemen'] + $akuntansi['pengeluaran_sewa'] + $akuntansi['tabungan_amortisasi'] + $akuntansi['tabungan_thr'];
                        $pendapatanBersih = $netto - $totalPengeluaranOther;
                        $prosentase = $netto==0 ? 0 : $pendapatanBersih/$netto*100;
                    ?>
                    <tbody>
                        <tr>
                            <td>Total Pengeluaran Lain Lain</td>
                            <td width="20%">{{number_format($akuntansi['pengeluaran_manajemen'],0,',','.')}}</td>
                        </tr>
                        <tr>
                            <td>Total Pengeluaran Sewa</td>
                            <td>{{number_format($akuntansi['pengeluaran_sewa'],0,',','.')}}</td>
                        </tr>
                        <tr>
                            <td>Total Tab Amortisasi</td>
                            <td>{{number_format($akuntansi['tabungan_amortisasi'],0,',','.')}}</td>
                        </tr>
                        <tr>
                            <td>Total Tab THR</td>
                            <td>{{number_format($akuntansi['tabungan_thr'],0,',','.')}}</td>
                        </tr>
                        <tr>
                            <td>Total Royalty Fee</td>
                            <td>0</td>
                        </tr>
                        <tr bgColor="#cad6e3">
                            <td>Pendapatan Bersih</td>
                            <td>{{number_format($pendapatanBersih,0,',','.')}}</td>
                        </tr>
                        <tr bgColor="#cad6e3">
                            <td>Prosentase</td>
                            <td>{{round($prosentase,2)}}%</td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-bordered">
                    <thead class="mt-3" style="background-color:#3c8dbc; color:white">
                        <tr>
                            <th>Deviden</th>
                            <th width="20%"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Principal 40%</td>
                            <td width="20%">{{number_format(40/100*$netto,0,',','.')}}</td>
                        </tr>
                        <tr>
                            <td>Investor 60%</td>
                            <td width="20%">{{number_format(60/100*$netto,0,',','.')}}</td>
                        </tr>
                    </tbody>
                </table>
                <table class="table table-bordered">
                    <?php 
                        $totalTransferPrincipal = (40/100*$netto) - $akuntansi['pengeluaran_manajemen'];
                    ?>
                    <tbody>
                        <tr bgColor="#cad6e3">
                            <td>Deviden Principal (Managemen Fee)</td>
                            <td width="20%">{{number_format(40/100*$netto,0,',','.')}}</td>
                        </tr>
                        {{-- <tr>
                            <td>Pengeluaran Lain Lain</td>
                            <td width="20%">{{number_format($akuntansi['pengeluaran_manajemen'],0,',','.')}}</td>
                        </tr>
                        <tr bgColor="#cad6e3">
                            <td>Total Transfer Principal</td>
                            <td width="20%">{{number_format($totalTransferPrincipal,0,',','.')}}</td>
                        </tr> --}}
                    </tbody>
                </table>
                <table class="table table-bordered">
                    <tbody>
                        <tr bgColor="#cad6e3">
                            <td>Deviden Investor</td>
                            <td width="20%">{{number_format(60/100*$netto,0,',','.')}}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @endif
        @endcan
    @endcomponent

@endsection

@section('javascript')
<script src="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js?v=' . $asset_v) }}"></script>
<script type="text/javascript">
    var bulan=@php print_r($data['bulan']) @endphp;
    $('#bulan').val(bulan[1]);
    $('#tahun').val(bulan[0]);
    $('#title').html('Laporan Laba Rugi Bulan '+formatBulan(bulan));
    function formatBulan(val){
        var bulan = ['Januari', 'Februari', 'Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        var getMonth=val[1];
        return bulan[getMonth-1]+' '+val[0];
    }
</script>
@endsection