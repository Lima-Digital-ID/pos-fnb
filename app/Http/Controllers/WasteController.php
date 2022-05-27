<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Ingredient;
use Datatables;

class WasteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->ajax()) {
            $waste = \DB::table('tb_rekap_penjualan_online')
                ->select([
                    'tb_rekap_penjualan_online.id',
                    'transactions.invoice_no',
                    'tb_rekap_penjualan_online.tanggal_rekap',
                    'tb_rekap_penjualan_online.total',
                ])
                ->leftJoin('tb_rekap_penjualan_online_detail', 'tb_rekap_penjualan_online_detail.id_rekap_penjualan', 'tb_rekap_penjualan_online.id')
                ->leftJoin('transactions', 'transactions.id', 'tb_rekap_penjualan_online_detail.inv_id')
                ->groupBy('tb_rekap_penjualan_online.id');

            return Datatables::of($waste)
                // ->addColumn(
                //     'action',
                //     function ($rekap) {
                //         return '<a href="{{[$id]}}" class="btn btn-xs btn-info showDetail" data-toggle="modal" data-target="#exampleModal" data-id="" onClick="javasciprt: cekDetail(' . $rekap->id . ')"><i class="glyphicon glyphicon-eye-open"></i> Detail</a>';
                //     }
                // )
                // ->rawColumns(['action'])
                ->make(true);
        }
        return view('waste.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->params['ingredient'] = Ingredient::get();
        $this->params['product'] = Product::get();
        $this->params['price_category'] = \DB::table('tb_kategori_harga')
                                            ->get();
        return view('waste.create',$this->params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // return $request->all();
            // $validated = $request->validate(
            //     [
            //         // 'product' => 'required',
            //         // 'qty_product' => 'required',
            //         // 'price_kategory' => 'required',
            //         'bahan' => 'required',
            //         'qty' => 'required',
            //         'transaction_date' => 'required',
            //     ],
            //     [
            //         // 'product.required' => 'Produk harus diisi.',
            //         // 'qty_product.required' => 'Kuantitas harus diisi.',
            //         // 'price_kategory.required' => 'Kategori harga harus diisi.',
            //         'bahan.required' => 'Bahan harus diisi.',
            //         'qty.required' => 'Kuantitas harus diisi.',
            //         'transaction_date.required' => 'Tanggal harus diisi.',
            //     ]
            // );

            $waste = array(
                'no_reference' => $request->no_referensi,
                'date' => $request->date,
                'grand_total' => '3',
                'ingredient_total' => '2',
                'product_total' => '1'
            );
            $idWaste = \DB::table('tb_waste')->insertGetId($waste);

            foreach ($request->product as $key => $value) {
                $productWaste = array(
                    'id_waste' => $idWaste,
                    'id_product' => $value,
                    'qty' => $request->qty_product[$key],
                    'category_price' => $request->price_kategory[$key],
                    'price_product' => '2',
                    'subtotal' => '1',
                    // 'price_product' => $request->price_product[$key],
                    // 'subtotal' => $request->subtotal[$key],
                );
                \DB::table('tb_waste_product_detail')->insert($productWaste);
            }

            foreach ($request->bahan as $key => $value) {
                $ingredientWaste = array(
                    'id_waste' => $idWaste,
                    'id_ingredient' => $value,
                    'qty' => $request->qty[$key],
                    'price_ingredient' => $request->price_ingredient[$key],
                    'subtotal' => $request->subtotal[$key],
                );
                \DB::table('tb_waste_ingredient_detail')->insert($ingredientWaste);
            }
        } catch (\Exception $e) {
            return $e;
        } catch (\Illuminate\Database\QueryException $e) {
            return $e;
        }
        return redirect()->route('waste.create');
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

    public function getPriceCategory($id_kategori,$id_produk)
    {
        $price_category = \DB::table('tb_harga_produk')
                            ->select('tb_harga_produk.id', 'tb_harga_produk.harga', 'tb_harga_produk.product_id')
                            ->leftJoin('tb_kategori_harga','tb_kategori_harga.id','tb_harga_produk.id_kategori')
                            ->where('product_id', $id_kategori)
                            ->where('id_kategori', $id_produk)
                            ->get();
        return json_encode($price_category);
    }
}
