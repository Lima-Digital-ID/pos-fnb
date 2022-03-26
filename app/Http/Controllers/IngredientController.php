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
                    <button data-href="{{action(\'IngredientController@edit\', [$id_bahan])}}" class="btn btn-xs btn-primary edit_bahan_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>
                        &nbsp;
                    @endcan
                    @can("bahan.delete")
                        <button data-href="{{action(\'IngredientController@destroy\', [$id_bahan])}}" class="btn btn-xs btn-danger delete_bahan_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>
                    @endcan'
                )
                ->rawColumns(['action'])
                ->make(true);
        }

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

        // return redirect()->route('bahan.create');
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

        return redirect()->route('ingredient.index')->withStatus('Data berhasil dihapus.');
    }
}
