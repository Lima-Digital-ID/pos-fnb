<?php

namespace App\Http\Controllers;

use App\SatuanBesar;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Database\QueryException;
use App\Utils\Util;

class SatuanBesarController extends Controller
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

            $satuanBesar = SatuanBesar::select([
                'id_satuan_besar',
                'satuan_besar',
            ]);

            return Datatables::of($satuanBesar)
                ->addColumn(
                    'action',
                    '@can("satuan_besar.update")
                    <a href="{{action(\'SatuanBesarController@edit\', [$id_satuan_besar])}}" class="btn btn-xs btn-primary edit_bahan_button"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a>
                        &nbsp;
                    @endcan
                    @can("satuan_besar.delete")
                    <form action="{{ action(\'SatuanBesarController@destroy\', [$id_satuan_besar]) }}" method="POST">
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
        return view('satuan_besar.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('satuan_besar.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('satuan_besar.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('satuan_besar.create')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate(
            [
                'satuan_besar' => 'required',
            ],
            [
                'satuan_besar.required' => 'Nama Satuan Besar harus diisi.',
            ]
        );

        try {
            $satuanBesar = array(
                'satuan_besar' => $validated['satuan_besar'],
            );
            \DB::table('tb_satuan_besar')->insert($satuanBesar);
        } catch (Exception $e) {
            return 'Terjadi kesalahan.' . $e;
        } catch (QueryException $e) {
            return 'Terjadi kesalahan pada database.' . $e;
        }

        return redirect()->route('satuan_besar.create');
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
        if (!auth()->user()->can('satuan_besar.create')) {
            abort(403, 'Unauthorized action.');
        }

        if (!auth()->user()->can('edit.create')) {
            abort(403, 'Unauthorized action.');
        }
        $this->params['data'] = SatuanBesar::findOrFail($id);
        // print_r($this->params['data']);
        return view('satuan_besar.edit', $this->params);
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
                'satuan_besar' => 'required',
            ],
            [
                'satuan_besar.required' => 'Nama Bahan harus diisi.',
            ]
        );

        try {
            $satuanBesar = array(
                'satuan_besar' => $request->input('satuan_besar'),
            );
            // dd($bahan);
            \DB::table('tb_satuan_besar')->where('id_satuan_besar', $id)
                ->update($satuanBesar);
        } catch (Exception $e) {
            return 'Terjadi kesalahan.' . $e;
        } catch (QueryException $e) {
            return 'Terjadi kesalahan pada database.' . $e;
        }
        return redirect()->route('satuan_besar.index');
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
            \DB::delete('delete from tb_satuan_besar where id_satuan_besar = ?', [$id]);
        } catch (Exception $e) {
            return back()->withError('Terjadi kesalahan.');
        } catch (QueryException $e) {
            return back()->withError('Terjadi kesalahan pada database.');
        }

        return redirect()->route('satuan_besar.index')->withStatus('Data berhasil dihapus.');
    }
}
