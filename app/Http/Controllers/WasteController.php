<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Product;
use App\Ingredient;
use Datatables;
use Illuminate\Support\Facades\DB;

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
            $waste = \DB::table('tb_waste');

            return Datatables::of($waste)
                ->addColumn(
                    'action',
                    function ($waste) {
                        return '<a href="{{[$id]}}" class="btn btn-xs btn-info showDetail" data-toggle="modal" data-target="#exampleModal" data-id="" onClick="javasciprt: cekDetail(' . $waste->id . ')"><i class="glyphicon glyphicon-eye-open"></i> Detail</a>';
                    }
                )
                ->rawColumns(['action'])
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
        $this->params['product'] = Product::leftJoin('brands', 'products.brand_id', '=', 'brands.id')
        ->join('units', 'products.unit_id', '=', 'units.id')
        ->leftJoin('categories as c1', 'products.category_id', '=', 'c1.id')
        ->leftJoin('categories as c2', 'products.sub_category_id', '=', 'c2.id')
        ->leftJoin('tax_rates', 'products.tax', '=', 'tax_rates.id')
        ->leftJoin('variation_location_details as vld', 'vld.product_id', '=', 'products.id')
        ->leftJoin('business_locations', 'business_locations.id', '=', 'products.location_id')
        ->join('variations as v', 'v.product_id', '=', 'products.id')
        ->where('products.type', '!=', 'modifier')
        ->select(
            'products.id',
            'products.name as product',
            'products.type',
            'c1.name as category',
            'c2.name as sub_category',
            'units.actual_name as unit',
            'brands.name as brand',
            'tax_rates.name as tax',
            'products.sku',
            'products.image',
            'products.enable_stock',
            'products.is_inactive',
            'business_locations.name as lokasi',
            DB::raw('SUM(vld.qty_available) as current_stock'),
            DB::raw('MAX(v.sell_price_inc_tax) as max_price'),
            DB::raw('MIN(v.sell_price_inc_tax) as min_price'),
            DB::raw('(select sum(bp.kebutuhan * tb.harga_bahan) from tb_bahan_product as bp join tb_bahan as tb on tb.id_bahan = bp.id_bahan where bp.product_id = products.id) as hpp')
        )->groupBy('products.id')->get();
        $this->params['price_category'] = DB::table('tb_kategori_harga')
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
            //         // 'bahan' => 'required',
            //         // 'qty' => 'required',
            //         'date' => 'required',
            //     ],
            //     [
            //         // 'product.required' => 'Produk harus diisi.',
            //         // 'qty_product.required' => 'Kuantitas harus diisi.',
            //         // 'price_kategory.required' => 'Kategori harga harus diisi.',
            //         // 'bahan.required' => 'Bahan harus diisi.',
            //         // 'qty.required' => 'Kuantitas harus diisi.',
            //         'date.required' => ':attribute harus diisi.',
            //     ]
            // );

            // dd(count($request->bahan));
            // dd($request->all());
            $waste = array(
                'no_reference' => $request->no_referensi,
                'date' => $request->date,
                'grand_total' => $request->grand_total,
                'ingredient_total' => $request->subtotal_bahan,
                'product_total' => $request->subtotal_produk,
            );
            DB::table('tb_waste')->insert($waste);
            $idWaste = DB::table('tb_waste')->latest('id')->first();
            foreach ($request->product as $key => $value) {
                if ($value != null) {
                    $productWaste = array(
                        'id_waste' => $idWaste->id,
                        'id_product' => $value,
                        'qty' => $request->qty_product[$key],
                        'price_product' => $request->price_product[$key],
                        'subtotal' => $request->subtotal_product[$key],
                    );
                    DB::table('tb_waste_product_detail')->insert($productWaste);
                }
            }

            foreach ($request->bahan as $key => $value) {
                if ($value != null) {
                    $ingredientWaste = array(
                        'id_waste' => $idWaste->id,
                        'id_ingredient' => $value,
                        'qty' => $request->qty[$key],
                        'price_ingredient' => $request->price_ingredient[$key],
                        'subtotal' => $request->subtotal[$key],
                    );
                    DB::table('tb_waste_ingredient_detail')->insert($ingredientWaste);
                }
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

    public function getDetailProduct($id)
    {
        $getDetail = \DB::table('tb_waste')
            ->select('products.name','tb_waste_product_detail.qty','tb_waste_product_detail.price_product','tb_waste_product_detail.category_price')
            ->join('tb_waste_product_detail', 'tb_waste_product_detail.id_waste', 'tb_waste.id')
            ->join('products', 'products.id', 'tb_waste_product_detail.id_product')
            ->where('tb_waste.id', $id)
            ->get();
        echo json_encode($getDetail);
    }

    public function getDetailIngredient($id)
    {
        $getDetail = \DB::table('tb_waste')
            ->select('tb_waste_ingredient_detail.qty','tb_bahan.nama_bahan','tb_waste_ingredient_detail.price_ingredient','tb_waste.no_reference')
            ->join('tb_waste_ingredient_detail', 'tb_waste_ingredient_detail.id_waste', 'tb_waste.id')
            ->join('tb_bahan', 'tb_bahan.id_bahan', 'tb_waste_ingredient_detail.id_ingredient')
            ->where('tb_waste.id', $id)
            ->get();
        echo json_encode($getDetail);
    }
}
