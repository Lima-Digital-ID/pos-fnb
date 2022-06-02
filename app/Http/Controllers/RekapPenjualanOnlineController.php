<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\BusinessLocation;
use Datatables;

class RekapPenjualanOnlineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (request()->ajax()) {
            $rekap = \DB::table('tb_rekap_penjualan_online')
                ->select([
                    'tb_rekap_penjualan_online.id',
                    'transactions.invoice_no',
                    'tb_rekap_penjualan_online.tanggal_rekap',
                    'tb_rekap_penjualan_online.total',
                ])
                ->leftJoin('tb_rekap_penjualan_online_detail', 'tb_rekap_penjualan_online_detail.id_rekap_penjualan', 'tb_rekap_penjualan_online.id')
                ->leftJoin('transactions', 'transactions.id', 'tb_rekap_penjualan_online_detail.inv_id')
                ->groupBy('tb_rekap_penjualan_online.id');

            return Datatables::of($rekap)
                ->addColumn(
                    'action',
                    function ($rekap) {
                        return '<a href="{{[$id]}}" class="btn btn-xs btn-info showDetail" data-toggle="modal" data-target="#exampleModal" data-id="" onClick="javasciprt: cekDetail(' . $rekap->id . ')"><i class="glyphicon glyphicon-eye-open"></i> Detail</a>';
                    }
                )
                ->rawColumns(['action'])
                ->make(true);
        }
        return view('rekap-penjualan.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $business_id = request()->session()->get('user.business_id');

        $user_id = request()->session()->get('user.id');
        $user = User::where('id', $user_id)->first();   
        $location_id=$user->location_id;
        $business_locations = BusinessLocation::forDropdown($business_id);
        foreach ($business_locations as $key => $value) {
            if ($user->location_id != null) {
                if ($user->location_id != $key) {
                    unset($business_locations[$key]);
                }
            }
        }
        $this->params['location_id'] = $location_id;        
        $this->params['business_locations'] = $business_locations;        
        return view('rekap-penjualan.create', $this->params);
    }
    
    public function getInv()
    {
        $inv = \DB::table('transactions')
            ->select('transactions.id', 'transactions.invoice_no')
            ->where('is_rekap', 'belum')
            ->where('id_kategori_harga','!=', '0')
            ->where('id_kategori_harga','!=', '1')
            ->where('location_id', $_GET['location_id'])
            ->get();
        echo json_encode($inv);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rekap = [
            'location_id' => $request->get('location_id'),
            'tanggal_rekap' => date('Y-m-d'),
            'total' => $request->get('total'),
        ];
        \DB::table('tb_rekap_penjualan_online')->insert($rekap);
        $lastId = \DB::table('tb_rekap_penjualan_online')->latest('id')->first();

        $data_trx=array(
            'deskripsi'     => 'Potongan Aplikasi',
            'location_id'   => $request->get('location_id'),
            'tanggal'       => $rekap['tanggal_rekap'],
            'notes'       => $lastId->id,
        );
        \DB::table('tbl_trx_akuntansi')->insert($data_trx);
        $id_last=\DB::table('tbl_trx_akuntansi')->max('id_trx_akun');
        $total = 0;
        foreach ($request->get('id_inv') as $key => $value) {
            $getTotal = \DB::table('transactions')->select('final_total')->where('id',$value)->first();
            $total += $getTotal->final_total;
            $detail = [
                'id_rekap_penjualan' => $lastId->id,
                'inv_id' => $value,
            ];
            \DB::table('tb_rekap_penjualan_online_detail')->insert($detail);
            \DB::statement("update transactions set is_rekap = 'sudah' where id = '$value' ");
        }
        $potongan_aplikasi = $total - $rekap['total'];
        $data1=array(
            'id_trx_akun'   => $id_last,
            'id_akun'       => 130,
            'jumlah'        => $potongan_aplikasi,
            'tipe'          => 'DEBIT',
            'keterangan'    => 'akun',
        );
        \DB::table('tbl_trx_akuntansi_detail')->insert($data1);

        return view('rekap-penjualan.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function detailJson($id)
    {
        $getDetail = \DB::table('tb_rekap_penjualan_online_detail')
            ->select('transactions.invoice_no')
            ->leftJoin('transactions', 'transactions.id', 'tb_rekap_penjualan_online_detail.inv_id')
            ->where('id_rekap_penjualan', $id)
            ->get();
        echo json_encode($getDetail);
    }
}
