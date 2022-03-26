<?php

namespace App\Http\Controllers;

use App\Ingredient;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
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
        // if (!auth()->user()->can('bahan.view') && !auth()->user()->can('bahan.create')) {
        //     abort(403, 'Unauthorized action.');
        // }

        // if (request()->ajax()) {
        //     $business_id = request()->session()->get('user.business_id');

        //     $ingredient = Ingredient::with(['satuan'])
        //         ->select([
        //             'id_bahan', 'nama_bahan', 'satuan', 'stok', 'limit_stok', 'limit_pemakaian'
        //         ]);
        //     dd($ingredient);

        //     return Datatables::of($ingredient)
        //         ->addColumn(
        //             'action',
        //             '@can("bahan.update")
        //             <button data-href="{{action(\'IngredientController@edit\', [$id_bahan])}}" class="btn btn-xs btn-primary edit_bahan_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
        //                 &nbsp;
        //             @endcan
        //             @can("bahan.delete")
        //                 <button data-href="{{action(\'IngredientController@destroy\', [$id_bahan])}}" class="btn btn-xs btn-danger delete_bahan_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
        //             @endcan'
        //         )
        //         ->rawColumns(['action'])
        //         ->make(true);
        // }
        $ingredient = Ingredient::with('satuan')
            ->select(
                'id_bahan',
                'nama_bahan',
                'satuan',
                'stok',
                'limit_stok',
                'limit_pemakaian'
            );
        print_r($ingredient);

        // return view('bahan.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
