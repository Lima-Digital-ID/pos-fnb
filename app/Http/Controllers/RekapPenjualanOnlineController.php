<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        $this->params['inv'] = \DB::table('transactions')
            ->select('transactions.id', 'transactions.invoice_no')
            ->leftJoin('transaction_payments', 'transaction_payments.transaction_id', 'transactions.id')
            ->where('transactions.is_rekap', 'belum')
            ->where('transaction_payments.method', '!=', 'cash')
            ->get();
        return view('rekap-penjualan.create', $this->params);
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
            'tanggal_rekap' => date('Y-m-d'),
            'total' => $request->get('total'),
        ];
        \DB::table('tb_rekap_penjualan_online')->insert($rekap);
        $lastId = \DB::table('tb_rekap_penjualan_online')->latest('id')->first();
        foreach ($request->get('id_inv') as $key => $value) {
            $detail = [
                'id_rekap_penjualan' => $lastId->id,
                'inv_id' => $value,
            ];

            \DB::table('tb_rekap_penjualan_online_detail')->insert($detail);
            \DB::statement("update transactions set is_rekap = 'sudah' where id = '$value' ");
        }
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
