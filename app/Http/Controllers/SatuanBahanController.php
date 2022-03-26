<?php

namespace App\Http\Controllers;

use App\SatuanBahan;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\QueryException;
use App\Utils\Util;

class SatuanBahanController extends Controller
{
    /**
     * All Utils instance.
     *
     */
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
        if (!auth()->user()->can('satuan_bahan.view') && !auth()->user()->can('satuan_bahan.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {

            $satuanBahan = SatuanBahan::select([
                'id_satuan',
                'satuan',
            ]);
            // dd($satuanBahan);

            return Datatables::of($satuanBahan)
                ->addColumn(
                    'action',
                    '@can("satuan_bahan.update")
                    <a href="{{action(\'SatuanBahanController@edit\', [$id_satuan])}}" class="btn btn-xs btn-primary edit_bahan_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a>
                        &nbsp;
                    @endcan
                    @can("satuan_bahan.delete")
                        
                    <a href="{{action(\'SatuanBahanController@destroy\', [$id_satuan])}}" class="btn btn-xs btn-danger delete_bahan_button"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</a>
                    @endcan'
                )
                ->rawColumns(['action'])
                ->make(true);
        }
        // $satuanBahan = SatuanBahan::select(
        //     'id_satuan',
        //     'satuan'
        // )->get();
        // print_r($satuanBahan);
        return view('satuan_bahan.index');
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

        return view('satuan_bahan.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('satuan_bahan.create')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate(
            [
                'satuan' => 'required',
            ],
            [
                'satuan.required' => 'Nama Satuan Bahan harus diisi.',
            ]
        );

        try {
            $satuan = array(
                'satuan' => $validated['satuan'],
            );
            \DB::table('tb_satuan_bahan')->insert($satuan);
        } catch (Exception $e) {
            return 'Terjadi kesalahan.' . $e;
        } catch (QueryException $e) {
            return 'Terjadi kesalahan pada database.' . $e;
        }

        return redirect()->route('satuan_bahan.create');
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
        if (!auth()->user()->can('bahan.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('edit.create')) {
            abort(403, 'Unauthorized action.');
        }
        $this->params['data'] = SatuanBahan::findOrFail($id);
        // print_r($this->params['data']);
        return view('satuan_bahan.edit', $this->params);
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
                'satuan' => 'required',
            ],
            [
                'satuan.required' => 'Nama Bahan harus diisi.',
            ]
        );

        try {
            $satuanBahan = array(
                'satuan' => $request->input('satuan'),
            );
            // dd($bahan);
            \DB::table('tb_satuan_bahan')->where('id_satuan', $id)
                ->update($satuanBahan);
        } catch (Exception $e) {
            return 'Terjadi kesalahan.' . $e;
        } catch (QueryException $e) {
            return 'Terjadi kesalahan pada database.' . $e;
        }
        return redirect()->route('satuan_bahan.index');
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
            // $satuan = SatuanBahan::findOrFail($id);
            \DB::delete('delete from tb_satuan_bahan where id_satuan = ?', [$id]);
            // $satuan->delete();
            // dd($satuan);
        } catch (Exception $e) {
            return back()->withError('Terjadi kesalahan.');
        } catch (QueryException $e) {
            return back()->withError('Terjadi kesalahan pada database.');
        }

        return redirect()->route('satuan_bahan.index')->withStatus('Data berhasil dihapus.');
    }
}
