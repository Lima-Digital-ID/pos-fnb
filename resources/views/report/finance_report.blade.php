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
                            $business_locations, $location_id, ['id' => 'location_id', 'class' => 'form-control select2', 'required' => 'required']); !!}
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
    @component('components.widget', ['class' => 'box-primary', 'title' =>"Laporan Keuangan" ])
            <div class="row no-print">
                <div class="col-sm-12">
                    <button type="button" class="btn btn-primary" 
                    aria-label="Print" onclick="window.print();"
                    ><i class="fa fa-print"></i> @lang( 'messages.print' )</button>
                </div>
            </div>
            <br>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th style="min-width: 100px;">Tanggal</th>
                            @foreach($data_kasbon[0]['kasbon'] as $value)
                            <th style="min-width: 100px;">Kasbon {{$value->nama_pegawai}}</th>
                            @endforeach
                            <th style="min-width: 100px;">Cash</th>
                            <th style="min-width: 100px;">Card</th>
                            <th style="min-width: 100px;">Ovo</th>
                            <th style="min-width: 100px;">Pengeluaran Bulanan</th>
                            <th style="min-width: 100px;">Keterangan Pengeluaran Bulanan</th>
                            <th style="min-width: 100px;">Pengeluaran Gaji</th>
                            <th style="min-width: 100px;">Pengeluaran Lembur</th>
                            <th style="min-width: 100px;">Pengeluaran Komisi</th>
                            <th style="min-width: 100px;">Keterangan Nama Gaji</th>
                            <th style="min-width: 100px;">Pengeluaran Bahan Baku</th>
                            <th style="min-width: 100px;">Keterangan Peng. A.Baru/Iklan/Servc/B.Baku</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                        $cash=$card=$ovo=$pengeluaran=$alat_baru=0; 
                        $total_gaji=$total_komisi=$total_lembur=$total_bahan_baku=0; 
                        @endphp
                        @foreach($data_kasbon as $key => $value)
                            @php 
                            $cash+=$value['transactions'][0]->cash;
                            $card+=$value['transactions'][0]->card;
                            $ovo+=$value['transactions'][0]->ovo;
                            @endphp
                            @if(count($value['pengeluaran']) == 0)
                            <tr>
                                <td>{{$key}}</td>
                                <td>{{ formatDate($value['tanggal']) }}</td>
                                @foreach($value['kasbon'] as $v)
                                <td class="text-right">{{formatRupiah($v->total_kasbon)}}</td>
                                @endforeach
                                <td class="text-right">{{ formatRupiah($value['transactions'][0]->cash)}}</td>
                                <td class="text-right">{{ formatRupiah($value['transactions'][0]->card)}}</td>
                                <td class="text-right">{{ formatRupiah($value['transactions'][0]->ovo)}}</td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                            </tr>
                            @else
                                @foreach($value['pengeluaran'] as $k => $v_out)
                                    @php 
                                    if($v_out->notes != 'Alat Baru' && $v_out->notes != 'Bahan Baku Salon'){
                                        $pengeluaran+=$v_out->total;
                                    }else if($v_out->notes == 'Bahan Baku Salon'){
                                        $total_bahan_baku+=$v_out->total;
                                    }else{
                                        $alat_baru+=$v_out->total;
                                    }
                                    
                                    @endphp
                                    @if($k == 0)
                                        <tr>
                                            <td>{{$key}}</td>
                                            <td>{{ formatDate($value['tanggal']) }}</td>
                                            @foreach($value['kasbon'] as $v)
                                            <td class="text-right">{{formatRupiah($v->total_kasbon)}}</td>
                                            @endforeach
                                            <td class="text-right">{{ formatRupiah($value['transactions'][0]->cash)}}</td>
                                            <td class="text-right">{{ formatRupiah($value['transactions'][0]->card)}}</td>
                                            <td class="text-right">{{ formatRupiah($value['transactions'][0]->ovo)}}</td>
                                            <td class="text-right">{{$v_out->notes != 'Alat Baru' && $v_out->notes != 'Bahan Baku Salon' ?  formatRupiah($v_out->total) : ''}}</td>
                                            <td class="text-center">{{$v_out->notes != 'Alat Baru' && $v_out->notes != 'Bahan Baku Salon' ?  $v_out->deskripsi_pengeluaran : ''}}</td>
                                            <td class="text-right"></td>
                                            <td class="text-right"></td>
                                            <td class="text-right"></td>
                                            <td class="text-right"></td>
                                            <td class="text-right">{{$v_out->notes == 'Bahan Baku Salon' ?  formatRupiah($v_out->total) : ''}}</td>
                                            <td class="text-right">{{$v_out->notes == 'Alat Baru' ?  formatRupiah($v_out->total) : ''}}</td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td></td>
                                            <td></td>
                                            @foreach($value['kasbon'] as $v)
                                            <td class="text-right"></td>
                                            @endforeach
                                            <td class="text-right"></td>
                                            <td class="text-right"></td>
                                            <td class="text-right"></td>
                                            <td class="text-right">{{$v_out->notes != 'Alat Baru' && $v_out->notes != 'Bahan Baku Salon' ?  formatRupiah($v_out->total) : ''}}</td>
                                            <td class="text-center">{{$v_out->notes != 'Alat Baru' && $v_out->notes != 'Bahan Baku Salon' ?  $v_out->deskripsi_pengeluaran : ''}}</td>
                                            <td class="text-right"></td>
                                            <td class="text-right"></td>
                                            <td class="text-right"></td>
                                            <td class="text-right"></td>
                                            <td class="text-right">{{$v_out->notes == 'Bahan Baku Salon' ?  formatRupiah($v_out->total) : ''}}</td>
                                            <td class="text-right">{{$v_out->notes == 'Alat Baru' ?  formatRupiah($v_out->total) : ''}}</td>
                                        </tr>
                                    @endif    
                                @endforeach
                            @endif
                        @endforeach
                        @foreach($gaji_report as $key => $value)
                            @php 
                            $total_gaji+=$value->total_gaji;
                            $total_lembur+=$value->total_lembur;
                            $total_komisi+=$value->total_komisi;
                            @endphp
                            <tr>
                                <td></td>
                                <td></td>
                                @foreach($data_kasbon[0]['kasbon'] as $v)
                                <td></td>
                                @endforeach
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right">{{ formatRupiah($value->total_gaji)}}</td>
                                <td class="text-right">{{ formatRupiah($value->total_lembur)}}</td>
                                <td class="text-right">{{ formatRupiah($value->total_komisi)}}</td>
                                <td>{{ $value->nama_pegawai}}</td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                            </tr>
                        @endforeach
                        @foreach($stock_adjustment as $key => $value)
                            @php
                            //$total_bahan_baku+=$value->total; 
                            @endphp
                            <tr>
                                <td></td>
                                <td></td>
                                @foreach($data_kasbon[0]['kasbon'] as $v)
                                <td></td>
                                @endforeach
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right"></td>
                                <td class="text-right">{{ formatRupiah($value->total)}}</td>
                                <td>{{ $value->name}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th></th>
                            <th></th>
                            @foreach($data_kasbon[0]['kasbon'] as $value)
                            <th></th>
                            @endforeach
                            <th class="text-right">{{ formatRupiah($cash)}}</th>
                            <th class="text-right">{{ formatRupiah($card)}}</th>
                            <th class="text-right">{{ formatRupiah($ovo)}}</th>
                            <th class="text-right">{{ formatRupiah($pengeluaran)}}</th>
                            <th></th>
                            <th class="text-right">{{ formatRupiah($total_gaji)}}</th>
                            <th class="text-right">{{ formatRupiah($total_lembur)}}</th>
                            <th class="text-right">{{ formatRupiah($total_komisi)}}</th>
                            <th></th>
                            <th class="text-right">{{ formatRupiah($total_bahan_baku)}}</th>
                            <th class="text-right">{{ formatRupiah($alat_baru)}}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
    @endcomponent
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