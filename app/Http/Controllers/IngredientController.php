<?php

namespace App\Http\Controllers;

use App\Ingredient;
use App\BusinessLocation;
use App\SatuanBahan;
use App\SatuanBesar;
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
                'tb_bahan.id_bahan',
                'nama_bahan',
                'harga_bahan',
                'business_locations.name',
                'tb_satuan_bahan.satuan',
                'tb_satuan_besar.satuan_besar',
                'tb_stok_bahan.stok',
                'limit_stok',
                'limit_pemakaian'
            ])
                ->selectRaw('tb_stok_bahan.stok/stok_besar as stok_besar')
                ->leftJoin('tb_stok_bahan', 'tb_bahan.id_bahan', 'tb_stok_bahan.id_bahan')
                ->leftJoin('business_locations', 'tb_stok_bahan.location_id', 'business_locations.id')
                ->join('tb_satuan_bahan', 'tb_satuan_bahan.id_satuan', 'tb_bahan.id_satuan')
                ->leftJoin('tb_satuan_besar', 'tb_satuan_besar.id_satuan_besar', 'tb_bahan.id_satuan_besar');
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
        $this->params['satuanBesar'] = SatuanBesar::get();
        $this->params['lokasi'] = BusinessLocation::get();

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
                'price_ingredient' => 'required',
                'id_satuan_besar' => 'required',
                'stok_besar' => 'required',
                'limit_stok' => 'required',
                'limit_pemakaian' => 'required',
            ],
            [
                'nama_bahan.required' => 'Nama Bahan harus diisi.',
                'id_satuan.required' => 'Satuan Bahan harus diisi.',
                'price_ingredient.required' => 'Harga Bahan harus diisi.',
                'id_satuan_besar.required' => 'Satuan Besar harus diisi.',
                'stok_besar.required' => 'Stok Satuan Besar harus diisi.',
                'limit_stok.required' => 'Limit Stok Bahan harus diisi.',
                'limit_pemakaian.required' => 'Limit Pemakaian Bahan harus diisi.',
            ]
        );

        try {
            $bahan = array(
                'nama_bahan' => $validated['nama_bahan'],
                'id_satuan' => $validated['id_satuan'],
                'harga_bahan' => $validated['price_ingredient'],
                'id_satuan_besar' => $validated['id_satuan_besar'],
                'stok_besar' => $validated['stok_besar'],
                'limit_stok' => $validated['limit_stok'],
                'limit_pemakaian' => $validated['limit_pemakaian']
            );
            \DB::table('tb_bahan')->insert($bahan);
            $lastId = \DB::table('tb_bahan')->latest('id_bahan')->first();
            $stok = array(
                'id_bahan' => $lastId->id_bahan,
                'stok' => '0',
                'location_id' => '1',
                // 'stok' => $validated['stok'],
                // 'location_id' => $request->get('location_id'),

            );
            \DB::table('tb_stok_bahan')->insert($stok);
            // dd($stok);
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
        $this->params['satuanBesar'] = SatuanBesar::get();
        $this->params['data'] = Ingredient::findOrFail($id);
        // return $this->params['data'];
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
                'price_ingredient' => 'required',
                'id_satuan_besar' => 'required',
                'stok_besar' => 'required',
                'limit_stok' => 'required',
                'limit_pemakaian' => 'required',
            ],
            [
                'nama_bahan.required' => 'Nama Bahan harus diisi.',
                'id_satuan.required' => 'Satuan Bahan harus diisi.',
                'price_ingredient.required' => 'Harga Bahan harus diisi.',
                'id_satuan_besar.required' => 'Satuan Besar harus diisi.',
                'stok_besar.required' => 'Stok Satuan Besar harus diisi.',
                'limit_stok.required' => 'Limit Stok Bahan harus diisi.',
                'limit_pemakaian.required' => 'Limit Pemakaian Bahan harus diisi.',
            ]
        );

        try {
            $bahan = array(
                'nama_bahan' => $validated['nama_bahan'],
                'id_satuan' => $validated['id_satuan'],
                'harga_bahan' => $validated['price_ingredient'],
                'id_satuan_besar' => $validated['id_satuan_besar'],
                'stok_besar' => $validated['stok_besar'],
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
        echo json_encode($bahan);
    }

    public function getIngredientByLocation($id)
    {
        $bahan = Ingredient::select([
            'nama_bahan',
            'business_locations.name',
            'tb_stok_bahan.id_bahan'
        ])
            ->join('tb_stok_bahan', 'tb_bahan.id_bahan', 'tb_stok_bahan.id_bahan')
            ->join('business_locations', 'tb_stok_bahan.location_id', 'business_locations.id')
            ->join('tb_satuan_bahan', 'tb_satuan_bahan.id_satuan', 'tb_bahan.id_satuan')
            ->where('tb_stok_bahan.location_id', $id)
            ->get();
        return json_encode($bahan);
    }

    public function get_limit_stok()
    {
        $stok = \DB::table('tb_stok_bahan')->get();
        // dd($stok);
        if (request()->ajax()) {
            $limit_stok = Ingredient::select([
                // 'tb_bahan.id_bahan',
                'nama_bahan',
                'business_locations.name',
                'tb_satuan_bahan.satuan',
                'tb_stok_bahan.stok',
                'limit_stok',
                'limit_pemakaian'
            ])
                ->join('tb_stok_bahan', 'tb_bahan.id_bahan', 'tb_stok_bahan.id_bahan')
                ->join('business_locations', 'tb_stok_bahan.location_id', 'business_locations.id')
                ->join('tb_satuan_bahan', 'tb_satuan_bahan.id_satuan', 'tb_bahan.id_satuan')
                ->whereRaw('tb_stok_bahan.stok < limit_stok ');

            // dd($limit_stok);

            return Datatables::of($limit_stok)
                ->make(true);
        }
        return view('bahan.limit-stok');
    }

    public function getLimitPemakaian()
    {
        if (request()->ajax()) {
            $limitPemakaian = Ingredient::select([
                // 'tb_bahan.id_bahan',
                'nama_bahan',
                'business_locations.name',
                'tb_satuan_bahan.satuan',
                'tb_stok_bahan.stok',
                'limit_stok',
                'limit_pemakaian'
            ])
                ->join('tb_stok_bahan', 'tb_bahan.id_bahan', 'tb_stok_bahan.id_bahan')
                ->join('business_locations', 'tb_stok_bahan.location_id', 'business_locations.id')
                ->join('tb_satuan_bahan', 'tb_satuan_bahan.id_satuan', 'tb_bahan.id_satuan')
                ->whereRaw('tb_stok_bahan.stok < limit_pemakaian');
            // dd($limitPemakaian);

            return Datatables::of($limitPemakaian)
                ->make(true);
        }
        return view('bahan.limit-pemakaian');
    }
}
