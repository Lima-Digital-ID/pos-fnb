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
                    <table class="table table-bordered" id="detailKas">
                        <thead style="background-color:#3c8dbc; color:white">
                            <tr>
                                <th>Pendapatan dari Penjualan</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $sum_pendapatan=0;
                            foreach ($data['pendapatan'] as $key => $value) {
                                if ($value->id_akun != 89) {
                            @endphp
                            <tr>
                                @php
                                if ($value->id_akun != 90) {
                                    $total=$value->jumlah_kredit-$value->jumlah_debit;
                                    $sum_pendapatan+=$total;
                                @endphp
                                    <td>{{$value->nama_akun}}</td>
                                    <td align="right">Rp. {{formatRupiah($total)}}</td>
                                @php
                                }else if ($value->id_akun == 90){
                                    $total=$value->jumlah_debit-$value->jumlah_kredit;  
                                    $sum_pendapatan-=$total;
                                @endphp
                                    <td>{{$value->nama_akun}}</td>
                                    <td align="right">- Rp. {{formatRupiah($total)}}</td>
                                @php
                                }
                                @endphp
                            </tr>
                            @php
                                }
                            }
                            $sum_hpp=0;
                            $bruto=$sum_pendapatan-$sum_hpp;
                            @endphp
                            <tr style="background-color:#ddd">
                                <th>Total Pendapatan dari Penjualan</th>
                                <th class="text-right">Rp. {{formatRupiah($sum_pendapatan)}}</th>
                            </tr>
                        </tbody>
                        <tbody>
                            <tr>
                                <th></th>
                                <th></th>
                            </tr>
                        </tbody>
                        <!-- <tbody>
                            <tr>
                                <th></th>
                                <th></th>
                            </tr>
                        </tbody>
                        <tbody style="background-color:#3c8dbc; color:white">
                            <tr style="padding-top:10px">
                                <th>PENDAPATAN KOTOR</th>
                                <th class="text-right">Rp. {{formatRupiah($bruto)}}</th>
                            </tr>
                        </tbody> -->
                       <!--  <tbody>
                            <tr>
                                <th></th>
                                <th></th>
                            </tr>
                        </tbody> -->
                        <thead style="background-color:#3c8dbc; color:white">
                            <tr>
                                <th>Pengeluaran</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Pengeluaran rutin</td>
                                <td align="right">Rp. {{formatRupiah($pengeluaran->total)}}</td>
                            </tr>
                            @php
                            $sum_beban=$pengeluaran->total;
                            foreach ($data['beban'] as $key => $value) {
                                //if ($value->id_akun != 65 && $value->id_akun != 87 && $value->id_akun != 43 && $value->id_akun != 125 && $value->id_akun != 126 && $value->id_akun != 127 && $value->id_akun != 128 && $value->id_akun != 129) {
                                //if ($value->id_akun == 34 || $value->id_akun == 43 || $value->id_akun == 92 || $value->id_akun == 93) {
                                if ($value->id_akun == 43) {
                                    $total=($value->id_akun == 43 ? $gaji_report->gaji_without_kasbon : ($value->jumlah_debit-$value->jumlah_kredit));
                                    $sum_beban+= $total;
                            @endphp
                            <tr>
                                <td>{{$value->nama_akun}}</td>
                                <td align="right">Rp. {{formatRupiah($total)}}</td>
                            </tr>
                            @php
                                }
                            }
                            //$sum_royalty=$sum_pendapatan * ($royalty_fee/100);
                            //$sum_beban=$sum_beban + $sum_royalty;
                            $netto=$bruto-$sum_beban;
                            @endphp
                            <!-- <tr>
                                <td>Royalty Management {{$royalty_fee}} %</td>
                                <td align="right">Rp. {{formatRupiah(($sum_pendapatan * ($royalty_fee/100)))}}</td>
                            </tr> -->
                            <tr style="color:red;background-color:#ddd">
                                <th>Total Pengeluaran Beban</th>
                                <th class="text-right">Rp. {{formatRupiah($sum_beban)}}</th>
                            </tr>
                        </tbody>
                        <thead>
                            <tr>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <?php 
                        $royalti = $sum_pendapatan * ($royalty_fee/100);

                        $total_deposit=$pengeluaran_other->total_sewa + $pengeluaran_other->total_amortisasi + $pengeluaran_other->total_thr + $pengeluaran_other->total_manajemen+$royalti;
                            
                            // $tempnetto = $netto-$total_deposit;
                            
                            // $total_deposit += $royalti;
                        ?>
                        <tbody>
                            <tr style="background-color:#ddd">
                                <th>Total Pengeluaran Manajemen</th>
                                <th class="text-right">Rp. {{formatRupiah($pengeluaran_other->total_manajemen)}}</th>
                            </tr>
                            <tr style="background-color:#ddd">
                                <th>Total Pengeluaran Sewa</th>
                                <th class="text-right">Rp. {{formatRupiah($pengeluaran_other->total_sewa)}}</th>
                            </tr>
                            <tr style="background-color:#ddd">
                                <th>Total Tabungan Amortisasi</th>
                                <th class="text-right">Rp. {{formatRupiah($pengeluaran_other->total_amortisasi)}}</th>
                            </tr>
                            <tr style="background-color:#ddd">
                                <th>Total Tabungan THR</th>
                                <th class="text-right">Rp. {{formatRupiah($pengeluaran_other->total_thr)}}</th>
                            </tr>
                            <tr style="background-color:#ddd">
                                <th>Total Royalty Fee</th>
                                <th class="text-right">Rp. {{formatRupiah($royalti)}}</th>
                            </tr>
                            <tr style="color:red;background-color:#ddd">
                                <th>Total</th>
                                <th class="text-right">Rp. {{formatRupiah($total_deposit)}}</th>
                            </tr>
                        </tbody>
                        <tbody>
                            <tr>
                                <th></th>
                                <th></th>
                            </tr>
                        </tbody>
                        <?php $netto-=$total_deposit ?>
                        <tbody style="background-color:#3c8dbc; color:white">
                            <tr style="padding-top:10px">
                                <th>PENDAPATAN BERSIH</th>
                                <th class="text-right">Rp. {{formatRupiah($netto)}}</th>
                            </tr>
                        </tbody>
                        <tbody style="background-color:#3c8dbc; color:white">
                            <tr style="padding-top:10px">
                                <th>Prosentase</th>
                                <th class="text-right">{{ $netto == 0 || $sum_pendapatan == 0 ? 0 : (round(($netto/$sum_pendapatan), 2) * 100)}} %</th>
                            </tr>
                        </tbody>
                        <tbody>
                            <tr>
                                <th></th>
                                <th></th>
                            </tr>
                        </tbody>
                        <tbody style="background-color:#3c8dbc; color:white">
                            <tr style="padding-top:10px">
                                <th>Dividen</th>
                                <th></th>
                            </tr>
                        </tbody>
                        <tbody>
                            <tr>
                                <th>Mitra ({{$dividen_mitra}} %)</th>
                                <th class="text-right">Rp. {{formatRupiah($netto * ($dividen_mitra/100))}}</th>
                            </tr>
                            <tr>
                                <th>Coolio ({{$dividen_bisnis}} %)</th>
                                <th class="text-right">Rp. {{formatRupiah($netto * ($dividen_bisnis/100))}}</th>
                            </tr>
                        </tbody>
                        <tbody>
                            <tr>
                                <th></th>
                                <th></th>
                            </tr>
                        </tbody>
                        <?php
                        $transfer_manajemen=($netto * ($dividen_bisnis/100)) + $pengeluaran_other->total_manajemen + $royalti;
                        $total_transfer_mitra=($netto * ($dividen_mitra/100)) + ($transfer_mitra != null ? $transfer_mitra->total : 0);
                        ?>
                        <tbody>
                            <tr style="padding-top:10px">
                                <th>Dividen Coolio</th>
                                <th class="text-right">Rp. {{formatRupiah($netto * ($dividen_bisnis/100))}}</th>
                            </tr>
                            <tr style="padding-top:10px">
                                <th>Pengeluaran Manajemen</th>
                                <th class="text-right">Rp. {{formatRupiah($pengeluaran_other->total_manajemen)}}</th>
                            </tr>
                            <tr style="padding-top:10px">
                                <th>Total Royalty Fee</th>
                                <th class="text-right">Rp. {{formatRupiah($royalti)}}</th>
                            </tr>
                        </tbody>
                        <tbody style="background-color:#3c8dbc; color:white">
                            <tr style="padding-top:10px">
                                <th>Total Transfer Manajemen</th>
                                <th class="text-right">Rp. {{formatRupiah($transfer_manajemen)}}</th>
                            </tr>
                        </tbody>
                        <tbody>
                            <tr style="padding-top:10px">
                                <th>Dividen Mitra</th>
                                <th class="text-right">Rp. {{formatRupiah($netto * ($dividen_mitra/100))}}</th>
                            </tr>
                            <tr style="padding-top:10px">
                                <th>Transfer</th>
                                <th class="text-right">Rp. {{formatRupiah(($transfer_mitra != null ? $transfer_mitra->total : 0))}} @can('akuntansi.add_transfer') <button class="btn btn-sm btn-primary" data-toggle='modal' data-target='#input_transfer_mitra'><i class="fa fa-edit"></i></button> @endcan</th>
                            </tr>
                        </tbody>
                        <tbody style="background-color:#3c8dbc; color:white">
                            <tr style="padding-top:10px">
                                <th>Total Transfer Mitra</th>
                                <th class="text-right">Rp. {{formatRupiah($total_transfer_mitra)}} </th>
                            </tr>
                        </tbody>
                    </table>
            </div>
            @endif
        @endcan
    @endcomponent
    <div class="modal fade" id="input_transfer_mitra" role="dialog">
        <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
            <h4 class="modal-title">Input Transfer Mitra</h4>
          </div>
        <form id="jurnal_gaji" action="{{URL::to('akuntansi/save_transfer_mitra')}}" method="post">
        {{ csrf_field() }}
          <div class="modal-body">
                <input type="hidden" value="{{$date}}" name="bulan">
                <input type="hidden" value="{{$location_id}}" name="location_id">
                <label>Total Transfer</label>
                <input class="form-control" name="total"></input>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <button class="btn btn-primary">Simpan</button>
          </div>
        </form>
        </div>
      </div>
    </div>
</section>
<!-- /.content -->

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