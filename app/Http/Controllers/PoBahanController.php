<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\TaxRate;
use App\Ingredient;
use App\PoBahan;
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
                'tbl_po_bahan.id',
                'no_referensi',
                'tax_rates.name',
                'date',
                'tb_bahan.nama_bahan',
                'tbl_d_po_bahan.qty',
                'tbl_d_po_bahan.price',
                'tbl_d_po_bahan.subtotal',
                'tbl_d_po_bahan.subtotal_tax',
            ])
                ->join('tax_rates', 'tbl_po_bahan.id_pajak', 'tax_rates.id')
                ->join('tbl_d_po_bahan', 'tbl_po_bahan.id_po_bahan', 'tbl_d_po_bahan.id_po_bahan')
                ->join('tb_bahan', 'tbl_d_po_bahan.id_bahan', 'tb_bahan.id_bahan');
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
        $lastId = \DB::table('tbl_po_bahan')->latest('id')->first();
        // dd($lastId);
        $po = array(
            'id_pajak' => $request->id_pajak,
            'no_referensi' => $request->no_referensi,
            'date' => $request->date . ":00",
            'id_po_bahan' =>  $lastId == null ? 1 :  $lastId->id_pajak + 1,
        );
        // dd($po);
        \DB::table('tbl_po_bahan')->insert($po);
        $lastIdAdj = \DB::table('tbl_po_bahan')->latest('id')->first();

        foreach ($request->get('bahan') as $key => $value) {
            $detail = [
                'id_po_bahan' => $lastIdAdj->id_po_bahan,
                'id_bahan' => $value,
                'qty' => $request->get('qty')[$key],
                'price' => $request->get('price')[$key],
                'subtotal' => $request->get('subtotal')[$key],
                'subtotal_tax' => $request->get('subtotaltax')[$key],
            ];
            \DB::table('tbl_d_po_bahan')->insert($detail);
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
