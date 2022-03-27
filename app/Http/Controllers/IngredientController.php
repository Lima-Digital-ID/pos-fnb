<?php

namespace App\Http\Controllers;

use App\Ingredient;
use App\SatuanBahan;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\QueryException;
use App\Utils\Util;

class IngredientController extends Controller
{
    protected $commonUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('bahan.view') && !auth()->user()->can('bahan.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $ingredient = Ingredient::select([
                'id_bahan',
                'nama_bahan',
                'tb_satuan_bahan.satuan',
                'stok',
                'limit_stok',
                'limit_pemakaian'
            ])
                ->join('tb_satuan_bahan', 'tb_satuan_bahan.id_satuan', 'tb_bahan.id_satuan');
            // dd($ingredient);

            return Datatables::of($ingredient)
                ->addColumn(
                    'action',
                    '@can("bahan.update")
                    <a href="{{action(\'IngredientController@edit\', [$id_bahan])}}" class="btn btn-xs btn-primary edit_bahan_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a>
                        &nbsp;
                    @endcan
                    @can("bahan.delete")
                    <form action="{{ action(\'IngredientController@destroy\', [$id_bahan]) }}" method="POST">
                    ' . csrf_field() . '
                    ' . method_field("DELETE") . '
                    <button type="submit" class="btn btn-xs btn-danger"
                        onclick="return confirm(\'Are You Sure Want to Delete?\')"
                        ><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</a>
                    </form>
                    @endcan'
                )
                ->rawColumns(['action'])
                ->make(true);
        }
        $ingredient = Ingredient::select([
            'id_bahan',
            'nama_bahan',
            'tb_satuan_bahan.satuan',
            'stok',
            'limit_stok',
            'limit_pemakaian'
        ])
            ->join('tb_satuan_bahan', 'tb_satuan_bahan.id_satuan', 'tb_bahan.id_satuan');
        dd($ingredient);

        return view('bahan.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('bahan.create')) {
            abort(403, 'Unauthorized action.');
        }

        $this->params['satuan'] = SatuanBahan::get();

        return view('bahan.create', $this->params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('unit.create')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate(
            [
                'nama_bahan' => 'required',
                'id_satuan' => 'required',
                'stok' => 'required',
                'limit_stok' => 'required',
                'limit_pemakaian' => 'required',
            ],
            [
                'nama_bahan.required' => 'Nama Bahan harus diisi.',
                'id_satuan.required' => 'Satuan Bahan harus diisi.',
                'stok.required' => 'Stok Bahan harus diisi.',
                'limit_stok.required' => 'Limit Stok Bahan harus diisi.',
                'limit_pemakaian.required' => 'Limit Pemakaian Bahan harus diisi.',
            ]
        );

        try {
            $bahan = array(
                'nama_bahan' => $validated['nama_bahan'],
                'id_satuan' => $validated['id_satuan'],
                'stok' => $validated['stok'],
                'limit_stok' => $validated['limit_stok'],
                'limit_pemakaian' => $validated['limit_pemakaian']
            );
            \DB::table('tb_bahan')->insert($bahan);
        } catch (Exception $e) {
            return 'Terjadi kesalahan.' . $e;
        } catch (QueryException $e) {
            return 'Terjadi kesalahan pada database.' . $e;
        }

        return redirect()->route('bahan.create');
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
        if (!auth()->user()->can('edit.create')) {
            abort(403, 'Unauthorized action.');
        }
        $this->params['satuan'] = SatuanBahan::get();
        $this->params['data'] = Ingredient::findOrFail($id);
        // print_r($this->params['data']);
        return view('bahan.edit', $this->params);
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
        if (!auth()->user()->can('unit.create')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate(
            [
                'nama_bahan' => 'required',
                'id_satuan' => 'required',
                'stok' => 'required',
                'limit_stok' => 'required',
                'limit_pemakaian' => 'required',
            ],
            [
                'nama_bahan.required' => 'Nama Bahan harus diisi.',
                'id_satuan.required' => 'Satuan Bahan harus diisi.',
                'stok.required' => 'Stok Bahan harus diisi.',
                'limit_stok.required' => 'Limit Stok Bahan harus diisi.',
                'limit_pemakaian.required' => 'Limit Pemakaian Bahan harus diisi.',
            ]
        );

        try {
            $bahan = array(
                'nama_bahan' => $validated['nama_bahan'],
                'id_satuan' => $validated['id_satuan'],
                'stok' => $validated['stok'],
                'limit_stok' => $validated['limit_stok'],
                'limit_pemakaian' => $validated['limit_pemakaian']
            );
            // dd($bahan);
            \DB::table('tb_bahan')->where('id_bahan', $id)
                ->update($bahan);
        } catch (Exception $e) {
            return 'Terjadi kesalahan.' . $e;
        } catch (QueryException $e) {
            return 'Terjadi kesalahan pada database.' . $e;
        }

        return redirect()->route('bahan.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $ingredient = Ingredient::findOrFail($id);
            $ingredient->delete();
        } catch (Exception $e) {
            return back()->withError('Terjadi kesalahan.');
        } catch (QueryException $e) {
            return back()->withError('Terjadi kesalahan pada database.');
        }

        return redirect()->route('bahan.index')->withStatus('Data berhasil dihapus.');
    }

    public function getIngredient()
    {
        $bahan = Ingredient::get();
        // $result = $bahan->orderBy('VLD.qty_available', 'desc')
        //     ->get();
        return json_encode($bahan);
    }

    public function getIngredient1()
    {
        if (request()->ajax()) {
            $term = request()->input('term', '');
            $location_id = request()->input('location_id', '');

            $check_qty = request()->input('check_qty', false);

            $price_group_id = request()->input('price_group', '');

            $business_id = request()->session()->get('user.business_id');

            $bahan = Product::join('variations', 'products.id', '=', 'variations.product_id')
                ->active()
                ->whereNull('variations.deleted_at')
                ->leftjoin('units as U', 'products.unit_id', '=', 'U.id')
                ->leftjoin(
                    'variation_location_details AS VLD',
                    function ($join) use ($location_id) {
                        $join->on('variations.id', '=', 'VLD.variation_id');

                        //Include Location
                        if (!empty($location_id)) {
                            $join->where(function ($query) use ($location_id) {
                                $query->where('VLD.location_id', '=', $location_id);
                                //Check null to show products even if no quantity is available in a location.
                                //TODO: Maybe add a settings to show product not available at a location or not.
                                $query->orWhereNull('VLD.location_id');
                            });;
                        }
                    }
                );
            if (!empty($price_group_id)) {
                $bahan->leftjoin(
                    'variation_group_prices AS VGP',
                    function ($join) use ($price_group_id) {
                        $join->on('variations.id', '=', 'VGP.variation_id')
                            ->where('VGP.price_group_id', '=', $price_group_id);
                    }
                );
            }
            $bahan->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier');

            //Include search
            if (!empty($term)) {
                $bahan->where(function ($query) use ($term) {
                    $query->where('products.name', 'like', '%' . $term . '%');
                    $query->orWhere('sku', 'like', '%' . $term . '%');
                    $query->orWhere('sub_sku', 'like', '%' . $term . '%');
                });
            }

            //Include check for quantity
            if ($check_qty) {
                $bahan->where('VLD.qty_available', '>', 0);
            }

            $bahan->select(
                'products.id as product_id',
                'products.name',
                'products.type',
                'products.enable_stock',
                'variations.id as variation_id',
                'variations.name as variation',
                'VLD.qty_available',
                'variations.sell_price_inc_tax as selling_price',
                'variations.sub_sku',
                'U.short_name as unit'
            );
            if (!empty($price_group_id)) {
                $bahan->addSelect('VGP.price_inc_tax as variation_group_price');
            }
            $result = $bahan->orderBy('VLD.qty_available', 'desc')
                ->get();
            return json_encode($result);
        }
    }
}
