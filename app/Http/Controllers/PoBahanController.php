<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TaxRate;
use App\Ingredient;
use App\PoBahan;
use App\BusinessLocation;
use Datatables;

class PoBahanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->ajax()) {
            $po = PoBahan::select([
                'tb_po_bahan.id',
                'no_referensi',
                'tax_rates.name as tax',
                'date',
                // 'tb_po_bahan.location_id',
                'business_locations.name as location',
                'tb_d_po_bahan.qty',
                'tb_d_po_bahan.price',
                'tb_d_po_bahan.subtotal',
                'tb_d_po_bahan.subtotal_tax',
            ])
                ->join('tax_rates', 'tb_po_bahan.id_pajak', 'tax_rates.id')
                ->join('tb_d_po_bahan', 'tb_po_bahan.id_po_bahan', 'tb_d_po_bahan.id_po_bahan')
                ->join('business_locations', 'tb_po_bahan.location_id', 'business_locations.id');
            // dd($adj);

            return Datatables::of($po)
                ->make(true);
        }
        return view('po-bahan.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->params['tax'] = TaxRate::get();
        $this->params['lokasi'] = BusinessLocation::get();
        $this->params['bahan'] = Ingredient::get();
        return view('po-bahan.create', $this->params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $po = array(
            'id_pajak' => $request->id_pajak,
            'no_referensi' => $request->no_referensi,
            'location_id' => $request->id_lokasi,
            'date' => $request->date,
        );
        // dd($po);
        \DB::table('tb_po_bahan')->insert($po);
        $lastIdAdj = \DB::table('tb_po_bahan')->latest('id')->first();

        foreach ($request->get('bahan') as $key => $value) {
            $detail = [
                'id_po_bahan' => $lastIdAdj->id_po_bahan,
                'id_bahan' => $value,
                'qty' => $request->get('qty')[$key],
                'price' => $request->get('price')[$key],
                'subtotal' => $request->get('subtotal')[$key],
                'subtotal_tax' => $request->get('subtotaltax')[$key],
            ];
            $kartuStok = [
                'id_bahan' => $value,
                'jml_stok' => $request->get('qty')[$key],
                'tipe' => 'po',
                'no_transaksi' => $request->no_referensi,
                'tanggal' => date('Y-m-d'),
            ];
            \DB::table('tb_d_po_bahan')->insert($detail);
            \DB::table('tb_kartu_stok')->insert($kartuStok);
            // $realStok = \DB::table('tb_stok_bahan')->where('id_bahan', $value)->first();
            \DB::statement("update tb_stok_bahan set stok = stok + " . $request->get('qty')[$key] . " where id_bahan = '$value' and location_id = '" . $request->get('id_lokasi') . "'  ");
        }
        return redirect()->route('po-bahan.create');
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
}
