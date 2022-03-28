<?php

namespace App\Http\Controllers;

use App\BusinessLocation;
use App\Ingredient;
use App\User;
use App\StockBahanAdj;
use App\Utils\ModuleUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Datatables;
use Illuminate\Http\Request;

class StockBahanAdjustmenController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $productUtil;
    protected $transactionUtil;
    protected $moduleUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(ProductUtil $productUtil, TransactionUtil $transactionUtil, ModuleUtil $moduleUtil)
    {
        $this->productUtil = $productUtil;
        $this->transactionUtil = $transactionUtil;
        $this->moduleUtil = $moduleUtil;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (request()->ajax()) {
            $adj = StockBahanAdj::select([
                'id',
                'tb_bahan.nama_bahan',
                'no_referensi',
                'date',
                'jenis_penyesuaian',
                'tbl_d_stok_bahan_adjust.stok_adjust',
                'alasan'
            ])
                ->join('tbl_d_stok_bahan_adjust', 'tbl_stok_bahan_adjust.id_stock_adj', 'tbl_d_stok_bahan_adjust.id_stock_adj')
                ->join('tb_bahan', 'tbl_d_stok_bahan_adjust.id_bahan', 'tb_bahan.id_bahan');
            // dd($adj);

            return Datatables::of($adj)
                // ->addColumn(
                //     'action',
                //     '@can("bahan.update")
                //     <a href="{{action(\'IngredientController@edit\', [$id_bahan])}}" class="btn btn-xs btn-primary edit_bahan_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a>
                //         &nbsp;
                //     @endcan
                //     @can("bahan.delete")
                //     <form action="{{ action(\'IngredientController@destroy\', [$id_bahan]) }}" method="POST">
                //     ' . csrf_field() . '
                //     ' . method_field("DELETE") . '
                //     <button type="submit" class="btn btn-xs btn-danger"
                //         onclick="return confirm(\'Are You Sure Want to Delete?\')"
                //         ><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</a>
                //     </form>
                //     @endcan'
                // )
                // ->rawColumns(['action'])
                ->make(true);
        }
        return view('stok_bahan_adjustment.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('purchase.create')) {
            abort(403, 'Unauthorized action.');
        }

        $this->params['bahan'] = Ingredient::get();
        return view('stok_bahan_adjustment.create', $this->params);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $lastId = \DB::table('tbl_stok_bahan_adjust')->latest('id')->first();
        // dd($lastId == null ? 1 : $lastId->id_stock_adj + 1);
        $stokAdj = array(
            'no_referensi' => $request->no_referensi,
            // 'date' => $request->date . ":00",
            'id_stock_adj' =>  $lastId == null ? 1 :  $lastId->id_stock_adj + 1,
            'jenis_penyesuaian' => $request->jenis_penyesuaian,
            'alasan' => $request->alasan,
        );
        // dd($request->date . ":00");
        \DB::table('tbl_stok_bahan_adjust')->insert($stokAdj);
        $lastIdAdj = \DB::table('tbl_stok_bahan_adjust')->latest('id')->first();
        // dd($lastIdAdj->id_stock_adj);

        foreach ($request->get('bahan') as $key => $value) {
            $detail = [
                'id_stock_adj' => $lastIdAdj->id_stock_adj,
                'id_bahan' => $value,
                'stok_adjust' => $request->get('stok_adjust')[$key],
            ];
            \DB::table('tbl_d_stok_bahan_adjust')->insert($detail);
            \DB::table('tb_bahan')->where('id_bahan', $value)->update(array('stok' => $request->get('stok_adjust')[$key]));
        }
        // dd($detail);
        return redirect()->route('stock-bahan-adjustment.create');
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
