@extends('layouts.app')
@section('title', 'Laporan Keuangan')

@section('content')
<link rel="stylesheet" href="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.css?v='.$asset_v) }}">

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Laporan Keuangan
    </h1>
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
    <div class="row no-print">
        <div class="col-md-12">
            @component('components.filters', ['title' => __('report.filters')])
              {!! Form::open(['url' => URL::current(), 'method' => 'get', 'id' => 'form-trx-member' ]) !!}
                <div class="col-md-4">
                    <div class="form-group">
                      {!! Form::label('Lokasi : ') !!}
                        {!! Form::select('location_id', 
                            $business_locations, $location_id, ['id' => 'location_id', 'class' => 'form-control select2', 'required' => 'required', 'placeholder'=>'Pilih Lokasi']); !!}
                    </div>
                </div>
                <div class="col-md-8">
                    {!! Form::label('Pilih Bulan dan Tahun :') !!}
                    <div class="row">
                        <div class="col-md-6">
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
                        </div>
                        <div class="col-md-6">
                            <select data-plugin-selectTwo class="form-control select2" name="tahun" id="tahun" required>
                                <option value="">--Pilih Tahun--</option>
                                @for ($i = date('Y') - 5; $i <= date('Y'); $i++)
                                <option value="{{$i}}">{{$i}}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>

                
                <div class="col-sm-12">
                  <button type="submit" class="btn btn-primary pull-right">@lang('report.apply_filters')</button>
                </div> 
                {!! Form::close() !!}
            @endcomponent
        </div>
    </div>
    @if($location_id != null)
    @component('components.widget', ['class' => 'box-primary', 'title' =>"Laporan Keuangan" ])
            <div class="row no-print">
                <div class="col-sm-12">
                    <button type="button" class="btn btn-primary" 
                    aria-label="Print" onclick="window.print();"
                    ><i class="fa fa-print"></i> @lang( 'messages.print' )</button>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="">
                    <thead>
                        <tr>
                            <th style="min-width: 100px;">Tanggal</th>
                            <th style="min-width: 100px;">Pendapatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_omset=$jasa=$non_jasa=0;
                        ?>
                        @foreach($pendapatan as $value)
                        <?php
                        $non_jasa+=$value['pendapatan'];
                        ?>
                        <tr>
                            <td>{{$value['tanggal']}}</td>
                            <td>{{formatRupiah($value['pendapatan'])}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <?php
                        $total_omset=$non_jasa - $hpp - $potongan_aplikasi;
                    ?>
                    <tfoot>
                        <tr>
                            <th></th>
                            <th>{{formatRupiah($non_jasa)}}</th>
                        </tr>
                        <tr>
                            <th>Total Nilai Pendapatan</th>
                            <th colspan="2">{{formatRupiah($non_jasa)}}</th>
                        </tr>
                        <tr>
                            <th>HPP Makanan dan Minuman</th>
                            <th colspan="2">{{formatRupiah($hpp)}}</th>
                        </tr>
                        <tr>
                            <th>Potongan Aplikasi</th>
                            <th colspan="2">{{formatRupiah($potongan_aplikasi)}}</th>
                        </tr>
                        <tr>
                            <th>Pendapatan dari Penjualan</th>
                            <th colspan="2">{{formatRupiah($total_omset)}}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="">
                    <thead>
                        <tr>
                            <th style="min-width: 100px;">Nama Pegawai</th>
                            <th style="min-width: 100px;">Total Kasbon</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $piutang=0;
                        ?>
                        @foreach($kasbon as $value)
                        <?php
                        $piutang+=$value->total_kasbon;
                        ?>
                        <tr>
                            <td>{{$value->nama_pegawai}}</td>
                            <td>{{formatRupiah($value->total_kasbon)}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th>Total</th>
                            <th>{{formatRupiah($piutang)}}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="">
                    <thead>
                        <tr>
                            <th style="min-width: 100px;">Nama Pegawai</th>
                            <!-- <th style="min-width: 100px;">Gaji</th>
                            <th style="min-width: 100px;">Komisi</th>
                            <th style="min-width: 100px;">Lembur</th> -->
                            <th style="min-width: 100px;">Total Gaji</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $gaji=0;
                        ?>
                        @foreach($gaji_report as $value)
                        <?php
                        $gaji+=$value->gaji_without_kasbon;
                        ?>
                        <tr>
                            <td>{{$value->nama_pegawai}}</td>
                            <!-- <td>{{$value->gaji_utama}}</td>
                            <td>{{formatRupiah($value->total_komisi)}}</td>
                            <td>{{formatRupiah($value->total_lembur)}}</td> -->
                            <td>{{formatRupiah($value->gaji_without_kasbon)}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="">Total</th>
                            <th>{{formatRupiah($gaji)}}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="">
                    <thead>
                        <tr>
                            <th style="min-width: 100px;">Tanggal</th>    
                            <th style="min-width: 100px;">Deskripsi Pengeluaran</th>
                            <th style="min-width: 100px;">Non Manajemen</th>
                            <th style="min-width: 100px;">Manajemen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $total_non_manajemen=$total_manajemen=0;
                        ?>
                        @foreach($temp_pengeluaran as $v)
                        <?php
                        foreach ($v['pengeluaran'] as $key => $value) {
                            $total_non_manajemen+=$value->total;
                        ?>
                        <tr>
                            <td>{{$value->tanggal}}</td>
                            <td>{{$value->deskripsi_pengeluaran}}</td>
                            <td>{{formatRupiah($value->total)}}</td>
                            <td></td>
                        </tr>
                        <?php
                        }
                        ?>
                        <?php
                        foreach ($v['pengeluaran_other'] as $key => $value) {
                            $total_manajemen+=$value->total;
                        ?>
                        <tr>
                            <td>{{$value->tanggal}}</td>
                            <td>{{$value->deskripsi_pengeluaran}}</td>
                            <td></td>
                            <td>{{formatRupiah($value->total)}}</td>
                        </tr>
                        <?php
                        }
                        ?>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2">Total</th>
                            <th>{{formatRupiah($total_non_manajemen)}}</th>
                            <th>{{formatRupiah($total_manajemen)}}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php 
            //$deposite=$pengeluaran_other->tabungan_amortisasi + $pengeluaran_other->tabungan_thr + $pengeluaran_other->pengeluaran_sewa + $pengeluaran_other->pengurangan_deposit_pegawai;
            $deposite=$pengeluaran_other->tabungan_amortisasi + $pengeluaran_other->tabungan_thr + $pengeluaran_other->pengeluaran_sewa;
            $deposite_before=$pengeluaran_other_before->tabungan_amortisasi + $pengeluaran_other_before->tabungan_thr + $pengeluaran_other_before->pengeluaran_sewa + $pengeluaran_other_before->deposit_pegawai;
            $deposite_new=($pengeluaran_other->tabungan_amortisasi - $pengeluaran_other->pengurangan_tabungan_amortisasi) + ($pengeluaran_other->tabungan_thr - $pengeluaran_other->pengurangan_tabungan_thr) + ($pengeluaran_other->pengeluaran_sewa - $pengeluaran_other->pengurangan_pengeluaran_sewa) + ($pengeluaran_other->deposit_pegawai - $pengeluaran_other->pengurangan_deposit_pegawai);
            ?>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="">
                    <thead>
                        <tr>
                            <th colspan="2"></th>    
                            <th class="text-center">Saldo Sebelumnya</th>
                            <th class="text-center">Saldo Bulan Ini</th>
                            <th>Total</th>
                        </tr>
                        <tr>
                            <th colspan="2">Total Pengeluaran Sewa</th>    
                            <th class="text-right">{{formatRupiah($pengeluaran_other_before->pengeluaran_sewa)}}</th>
                            <th class="text-right">{{formatRupiah($pengeluaran_other->pengeluaran_sewa).' - '.formatRupiah($pengeluaran_other->pengurangan_pengeluaran_sewa)}}</th>
                            <th class="text-right">{{formatRupiah($pengeluaran_other_before->pengeluaran_sewa + ($pengeluaran_other->pengeluaran_sewa - $pengeluaran_other->pengurangan_pengeluaran_sewa))}}</th>
                        </tr>
                        <tr>
                            <th colspan="2">Total Tabungan THR</th>   
                            <th class="text-right">{{formatRupiah($pengeluaran_other_before->tabungan_thr)}}</th>
                            <th class="text-right">{{formatRupiah($pengeluaran_other->tabungan_thr).' - '.formatRupiah($pengeluaran_other->pengurangan_tabungan_thr)}}</th>
                            <th class="text-right">{{formatRupiah($pengeluaran_other_before->tabungan_thr + ($pengeluaran_other->tabungan_thr - $pengeluaran_other->pengurangan_tabungan_thr))}}</th>
                        </tr>
                        <tr>
                            <th colspan="2">Total Tabungan Amortisasi</th> 
                            <th class="text-right">{{formatRupiah($pengeluaran_other_before->tabungan_amortisasi)}}</th>
                            <th class="text-right">{{formatRupiah($pengeluaran_other->tabungan_amortisasi).' - '.formatRupiah($pengeluaran_other->pengurangan_tabungan_amortisasi)}}</th>
                            <th class="text-right">{{formatRupiah($pengeluaran_other_before->tabungan_amortisasi + ($pengeluaran_other->tabungan_amortisasi - $pengeluaran_other->pengurangan_tabungan_amortisasi))}}</th>
                        </tr>
                        <tr style="background-color:#3cbc7b; color:white">
                            <th colspan="2">Total Deposit Pegawai</th> 
                            <th class="text-right">{{formatRupiah($pengeluaran_other_before->deposit_pegawai)}}</th>
                            <th class="text-right">{{formatRupiah($pengeluaran_other->deposit_pegawai).' - '.formatRupiah($pengeluaran_other->pengurangan_deposit_pegawai)}}</th>
                            <th class="text-right">{{formatRupiah($pengeluaran_other_before->deposit_pegawai + ($pengeluaran_other->deposit_pegawai - $pengeluaran_other->pengurangan_deposit_pegawai))}}</th>
                        </tr>
                        <tr style="background-color:#3c8dbc; color:white">
                            <th colspan="2">Total Tabungan</th> 
                            <th class="text-right">{{formatRupiah($deposite_before)}}</th>
                            <th class="text-right">{{formatRupiah($deposite)}}</th>
                            <th class="text-right">{{formatRupiah($deposite_before+$deposite_new)}}</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <?php
                    $royalty = $business_location->royalty_fee;
                    $total_pendapatan=$total_omset - ($gaji + $total_manajemen + $total_non_manajemen + $deposite + ($total_omset * ($business_location->royalty_fee/100)));

                ?>
            {{-- <div class="table-responsive">
                <table class="table table-bordered table-striped" id="">
                    <thead>
                        <tr>
                            <th style="min-width: 100px;">Hasil Ahir</th>    
                            <th colspan="5" class="text-center">Total Penjualan -  Gaji - Pengeluaran Rutin - Pengurangan Tagihan Manajemen - Deposit</th>
                            <th style="min-width: 100px;">Total</th>
                        </tr>
                        <tr>
                            <th style="min-width: 100px;"></th>    
                            <th class="text-right">{{formatRupiah($total_omset)}}</th>
                            <th class="text-right">{{formatRupiah($gaji)}}</th>
                            <th class="text-right">{{formatRupiah($total_non_manajemen)}}</th>
                            <th class="text-right">{{formatRupiah($total_manajemen)}}</th>
                            <th class="text-right">
                                    {{formatRupiah($deposite +  ($total_omset * ($business_location->royalty_fee/100)))}}
                            </th>
                            <th class="text-right">{{formatRupiah($total_pendapatan)}}</th>
                        </tr>
                    </thead>
                </table>
            </div>
            <!--<div class="table-responsive">-->
            <!--    <table class="table table-bordered table-striped" id="">-->
            <!--        <thead>-->
            <!--            <tr>-->
            <!--                <th style="min-width: 100px;">Prosentase Pembagian</th>    -->
            <!--                <th>{{$business_location->dividen_bisnis}} %</th>-->
            <!--                <th class="text-right">{{formatRupiah($total_pendapatan)}}</th>-->
            <!--            </tr>-->
            <!--            <tr>-->
            <!--                <th style="min-width: 100px;"></th>    -->
            <!--                <th class="text-right">{{formatRupiah($total_pendapatan * ($business_location->dividen_bisnis/100))}}</th>-->
            <!--                <th></th>-->
            <!--            </tr>-->
            <!--        </thead>-->
            <!--    </table>-->
            <!--</div>-->
            <!--<div class="table-responsive">-->
            <!--    <table class="table table-bordered table-striped" id="">-->
            <!--        <thead>-->
            <!--            <tr>-->
            <!--                <th style="min-width: 100px;">Total Transfer Manajemen</th>    -->
            <!--                <th>{{$business_location->dividen_mitra}} %</th>-->
            <!--                <th class="text-right">{{formatRupiah($total_pendapatan)}}</th>-->
            <!--            </tr>-->
            <!--            <tr>-->
            <!--                <th style="min-width: 100px;"></th>    -->
            <!--                <th class="text-right">{{formatRupiah($total_pendapatan * ($business_location->dividen_mitra/100))}}</th>-->
            <!--                <th></th>-->
            <!--            </tr>-->
            <!--        </thead>-->
            <!--    </table>-->
            <!--</div>-->
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="">
                    <thead>
                        <tr>
                            <th style="min-width: 100px;">Royalty Fee</th>    
                            <th>{{$royalty}} %</th>
                            <th class="text-right">{{0}}</th>
                        </tr>
                        <tr>
                            <th style="min-width: 100px;"></th>    
                            <th class="text-right">
                                    {{formatRupiah($total_omset * ($business_location->royalty_fee/100))}}
                            </th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
            </div> --}}
            
            
    @endcomponent
    @endif
    <div class="modal fade account_model" tabindex="-1" role="dialog" 
    	aria-labelledby="gridSystemModalLabel">
    </div>

</section>
@endsection

@section('javascript')
<script src="{{ asset('plugins/bootstrap-datetimepicker/bootstrap-datetimepicker.min.js?v=' . $asset_v) }}"></script>
<style>
    td.details-control {
        background: url('https://datatables.net/examples/resources/details_open.png') no-repeat center center;
        cursor: pointer;
    }
    tr.details td.details-control {
        background: url('https://datatables.net/examples/resources/details_close.png') no-repeat center center;
    }
</style>
<script>
    
    $(document).ready( function(){
        list_trx_member=$('#list_trx_member').DataTable({
                processing: true,
                serverSide: true,
                "ajax": "/reports/sell-trx-member",
                columns: [
                    { data: 'contact_id'},
                    { data: 'name'},
                    { data: 'mobile'},
                    { data: 'total_trx'},
                ],
                "footerCallback": function ( row, data, start, end, display ) {
                    var api = this.api(), data;
         
                    // Remove the formatting to get integer data for summation
                    var intVal = function ( i ) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '')*1 :
                            typeof i === 'number' ?
                                i : 0;
                    };
         
                    // Total over all pages
                    total = api
                        .column( 3 )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
         
                    // Total over this page
                    pageTotal = api
                        .column( 3, { page: 'current'} )
                        .data()
                        .reduce( function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0 );
         
                    // Update footer
                    $( api.column( 3 ).footer() ).html(total);
                }
            });
            $('#location_id, #bulan, #tahun').change(function() {
                updateMemberTrxReport();
            });

        });
        // $(document).on('submit', '#form-trx-member', function(e) {
        //     e.preventDefault();
        //     updateMemberTrxReport();
        // });
    function updateMemberTrxReport() {
        var data = {
            location_id: $('#location_id').val(),
            bulan: $('#bulan').val(),
            tahun: $('#tahun').val(),
        };
        console.log(data);
        var out = [];

        for (var key in data) {
            out.push(key + '=' + encodeURIComponent(data[key]));
        }
        url_data = out.join('&');
        list_trx_member.ajax.url('/reports/sell-trx-member?' + url_data).load();
    }
    function formatRupiah(angka, prefix)
    {
        var reverse = angka.toString().split('').reverse().join(''),
        ribuan = reverse.match(/\d{1,3}/g);
        ribuan = ribuan.join('.').split('').reverse().join('');
        return ribuan;
    }
    function formatDate(date){
        var myDate = new Date(date);
        var tgl=date.split('-');
        // var output = myDate.getDate() + "-" +  (myDate.getMonth()+1) + "-" + myDate.getFullYear();
        var output = tgl[2] + "-" +  tgl[1] + "-" + tgl[0];
        return output;
    }
</script>
<script type="text/javascript">
    var bulan=@php print_r($month) @endphp;
    $('#bulan').val(bulan[1]);
    $('#tahun').val(bulan[0]);
    function formatBulan(val){
        var bulan = ['Januari', 'Februari', 'Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        var getMonth=val[1];
        return bulan[getMonth-1]+' '+val[0];
    }
</script>
@endsection