<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Account;
use App\AccountTransaction;
use App\TransactionPayment;
use App\User;
use App\BusinessLocation;

use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\View;
use App\Utils\Util;

use DB;

class AkuntanController extends Controller
{
    protected $commonUtil;

    /**
     * Constructor
     *
     * @param Util $commonUtil
     * @return void
     */
    public function __construct(Util $commonUtil)
    {
        $this->commonUtil = $commonUtil;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        if (!auth()->user()->can('akuntansi.akun')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {

            $data=DB::table('tbl_akun')->get();
            $data1=array();
            foreach ($data as $key => $value) {
                $main=DB::table('tbl_akun')->where('id_akun', $value->id_main_akun)->first();
                $row=array();
                $row['id_akun']= $value->id_akun;
                $row['no_akun']= $value->no_akun;
                $row['nama_akun']= $value->nama_akun;
                $row['level']= $value->level;
                $row['debit']= $value->sifat_debit;
                $row['kredit']= $value->sifat_kredit;
                $row['main']= ($main != null ? $main->nama_akun : '');
                $data1[]=$row;
            }
            return DataTables::of($data1)
                                ->addColumn(
                                    'action',
                                    '<button data-href="" data-container=".account_model" class="btn btn-xs btn-primary btn-modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>'
                                )
                                ->make(true);
        }
        return view('akuntansi.list_akun');
    }
    public function createAkun(){
        $data['parent_option']=array();
        $data['parent_option'][''] = 'Pilih Parent';
        $parent_option_js=array();
        foreach (DB::table('tbl_akun')->where('level', 0)->get() as $key => $value) {
            $data['parent_option'][$value->id_akun]=$value->nama_akun;
            $parent_option_js[]=array(
                'label'     => $value->id_akun,
                'value'     => $value->no_akun,
            );
        }
        $data['parent_option_js']=json_encode($parent_option_js);
        return view('akuntansi.create_account')->with(compact('data'));
    }
    public function storeAkun(Request $request)
    {
        // print_r($request->input());exit();
        if (!auth()->user()->can('akuntansi.akun')) {
            abort(403, 'Unauthorized action.');
        }
        $row=DB::table('tbl_akun')->where('id_akun', $request->input('id_parent'))->first();
        $id_main_akun=0;
        $level=$request->input('level');
        $nama_akun=$request->input('nama_akun');
        $no_akun=$request->input('no_akun');
        $id_parent=$turunan1=$turunan2=$turunan3=0;
        $id_parent=($request->input('id_parent') ? $request->input('id_parent') : '');
        $turunan1=($request->input('level2') ? $request->input('level2') : '');
        $turunan2=($request->input('level3') ? $request->input('level3') : '');

        if ($level == 1) {
            $id_main_akun=$request->input('id_parent');
        }else if ($level == 2) {
            $id_main_akun=$request->input('level2');
        }else if ($level == 3) {
            $id_main_akun=$request->input('level3');
        }
        $data=array(
            'no_akun'         => $no_akun,
            'nama_akun'       => $nama_akun,
            'level'           => $level,
            'id_main_akun'    => $id_main_akun,
            'sifat_debit'     => $row->sifat_debit,
            'sifat_kredit'    => $row->sifat_kredit,
        );
        DB::table('tbl_akun')->insert($data);
        $row=DB::table('tbl_akun')->max('id_akun');
        $data_d=array(
            'id_akun'           => $row,
            'id_parent'         => $id_parent,
            'turunan1'          => $turunan1,
            'turunan2'          => $turunan2,
            'turunan3'          => $turunan3,
        );
        DB::table('tbl_akun_detail')->insert($data_d);
        return redirect('akuntansi');
    }
    public function getNoAkun($id){
        header('Content-Type: application/json');
        $data=DB::table('tbl_akun')
                ->select(DB::raw('MAX(id_main_akun) as id_main_akun'), DB::raw('MAX(no_akun) as no_akun'), DB::raw('COUNT(id_akun) as total_akun'))
                ->where('id_main_akun', $id)
                ->first();
        $data2=DB::table('tbl_akun')->where('id_akun', $id)->first();
        $akun= array('no_akun'=>$data->no_akun,'id_main_akun'=>$data->id_main_akun, 'no_akun_main'=>$data2->no_akun, 'total'=>$data->total_akun);
        echo json_encode($akun);
    }
    public function getLevel($id) {
        header('Content-Type: application/json');
        $data=DB::table('tbl_akun')
                ->where('id_main_akun', $id)
                ->get();
        echo json_encode($data);
    }
    public function jurnal(Request $request)
    {
        if (!auth()->user()->can('akuntansi.jurnal')) {
            abort(403, 'Unauthorized action.');
        }
        
        $user_id = request()->session()->get('user.id');
        $user = User::where('id', $user_id)->first();

        $date=date('Y-m-d');
        if ($request->input('date')) {
            $date=$request->input('date');
        }
        $list_trx=DB::table('tbl_trx_akuntansi')
                    ->where('tanggal', $date)
                    ->orderBy('tanggal');
        if ($user->role != 1) {
            $list_trx->where('location_id', $user->location_id);
        }
        $results=$list_trx->get();

        $data=array();
        foreach ($results as $key => $value) {
            $data[$key]=array(
                'id_trx_akun' => $value->id_trx_akun,
                'deskripsi' => $value->deskripsi,
                'tanggal'   => $value->tanggal
            );
            $data[$key]['detail']=$this->getDetailKas($value->id_trx_akun);
        }
        return view('akuntansi.journal_list')->with(compact('data', 'date'));
    }
    private function getDetailKas($id)
    {
        $data=DB::table('tbl_trx_akuntansi')
                ->join('tbl_trx_akuntansi_detail', 'tbl_trx_akuntansi.id_trx_akun','=','tbl_trx_akuntansi_detail.id_trx_akun')
                ->join('tbl_akun', 'tbl_akun.id_akun','=','tbl_trx_akuntansi_detail.id_akun')
                ->where('tbl_trx_akuntansi.id_trx_akun', $id)
                ->orderBy('tbl_trx_akuntansi_detail.keterangan')
                ->get();
        return $data;
    }
    private function getSaldo($id_parent=null, $date, $location_id, $jurnalClose){
        $query=DB::table('tbl_akun_detail')->join('tbl_akun', 'tbl_akun.id_akun', 'tbl_akun_detail.id_akun')
                        ->where('turunan1', 0)
                        ->orderBy('tbl_akun.no_akun', 'ASC');
        if ($id_parent != null) {
            $query->where('id_parent', $id_parent);
        }
        $turunan1=$query->get();
        $data=array();
        $j=0;
        foreach ($turunan1 as $key => $value) {
            $turunan2=DB::table('tbl_akun_detail')->join('tbl_akun', 'tbl_akun.id_akun', 'tbl_akun_detail.id_akun')->where('turunan1', $value->id_akun)->where('turunan2', 0)->get();
            if (count($turunan2) == 0) {
                $saldo=DB::table('tbl_saldo_akun')
                        ->where('id_akun', $value->id_akun)
                        ->where('tanggal', $date);
                if ($location_id != null) {
                    $saldo->where('location_id', $location_id);
                }
                $results=$saldo->first();
                // $n=0;
                $data[$j]['nama']=$value->nama_akun;
                $data[$j]['id_parent']=$value->id_parent;
                $data[$j]['data'][0]['detail']=$this->countSaldo($value->id_akun, $date, $location_id, $jurnalClose);
                $data[$j]['data'][0]['saldo']=$results;
                $j++;
            }else{
                $data[$j]['nama']=$value->nama_akun;
                $data[$j]['id_parent']=$value->id_parent;
                $n=0;
                foreach ($turunan2 as $k => $v) {
                    $saldo=DB::table('tbl_saldo_akun')
                            ->where('id_akun', $v->id_akun)
                            ->where('tanggal', $date);
                    if ($location_id != null) {
                        $saldo->where('location_id', $location_id);
                    }
                    $results=$saldo->first();

                    $data[$j]['data'][$n]['detail']=$this->countSaldo($v->id_akun, $date, $location_id, $jurnalClose);
                    $data[$j]['data'][$n]['saldo']=$results;
                    $n++;
                }
                $j++;
            }
        }
        return $data;
    }
    private function countSaldo($id, $date, $location_id, $jurnalClose){
         $results = DB::select( DB::raw('SELECT COALESCE((SELECT SUM(trd.jumlah) as jumlah FROM tbl_trx_akuntansi_detail 
            trd JOIN tbl_trx_akuntansi tra ON trd.id_trx_akun=tra.id_trx_akun JOIN tbl_akun_detail tad ON 
            tad.id_akun=trd.id_akun WHERE tra.tanggal LIKE "'.$date.'%" '.($location_id != null ? "AND tra.location_id=".$location_id : "").' '.($jurnalClose == null ? "AND tra.notes IS NULL " : "").' AND tad.id_akun=tbl_akun_detail.id_akun 
            AND trd.tipe="KREDIT"), 0) + COALESCE((SELECT SUM(trd.jumlah) as jumlah FROM tbl_trx_akuntansi_detail 
            trd JOIN tbl_trx_akuntansi tra ON trd.id_trx_akun=tra.id_trx_akun JOIN tbl_akun_detail tad ON 
            tad.id_akun=trd.id_akun WHERE tra.tanggal LIKE "'.$date.'%" '.($location_id != null ? "AND tra.location_id=".$location_id : "").' '.($jurnalClose == null ? "AND tra.notes IS NULL " : "").' AND tad.turunan1=tbl_akun_detail.id_akun 
            AND trd.tipe="KREDIT"), 0) + COALESCE((SELECT SUM(trd.jumlah) as jumlah FROM tbl_trx_akuntansi_detail 
            trd JOIN tbl_trx_akuntansi tra ON trd.id_trx_akun=tra.id_trx_akun JOIN tbl_akun_detail tad ON 
            tad.id_akun=trd.id_akun WHERE tra.tanggal LIKE "'.$date.'%" '.($location_id != null ? "AND tra.location_id=".$location_id : "").' '.($jurnalClose == null ? "AND tra.notes IS NULL " : "").' AND tad.turunan2=tbl_akun_detail.id_akun 
            AND trd.tipe="KREDIT"), 0) AS jumlah_kredit, COALESCE((SELECT SUM(trd.jumlah) as jumlah FROM tbl_trx_akuntansi_detail 
            trd JOIN tbl_trx_akuntansi tra ON trd.id_trx_akun=tra.id_trx_akun JOIN tbl_akun_detail tad ON 
            tad.id_akun=trd.id_akun WHERE tra.tanggal LIKE "'.$date.'%" '.($location_id != null ? "AND tra.location_id=".$location_id : "").' '.($jurnalClose == null ? "AND tra.notes IS NULL " : "").' AND tad.id_akun=tbl_akun_detail.id_akun 
            AND trd.tipe="DEBIT"), 0) + COALESCE((SELECT SUM(trd.jumlah) as jumlah FROM tbl_trx_akuntansi_detail 
            trd JOIN tbl_trx_akuntansi tra ON trd.id_trx_akun=tra.id_trx_akun JOIN tbl_akun_detail tad ON 
            tad.id_akun=trd.id_akun WHERE tra.tanggal LIKE "'.$date.'%" '.($location_id != null ? "AND tra.location_id=".$location_id : "").' '.($jurnalClose == null ? "AND tra.notes IS NULL " : "").' AND tad.turunan1=tbl_akun_detail.id_akun 
            AND trd.tipe="DEBIT"), 0) + COALESCE((SELECT SUM(trd.jumlah) as jumlah FROM tbl_trx_akuntansi_detail 
            trd JOIN tbl_trx_akuntansi tra ON trd.id_trx_akun=tra.id_trx_akun JOIN tbl_akun_detail tad ON 
            tad.id_akun=trd.id_akun WHERE tra.tanggal LIKE "'.$date.'%" '.($location_id != null ? "AND tra.location_id=".$location_id : "").' '.($jurnalClose == null ? "AND tra.notes IS NULL " : "").' AND tad.turunan2=tbl_akun_detail.id_akun 
            AND trd.tipe="DEBIT"), 0) AS jumlah_debit, tbl_akun_detail.*, tbl_akun.nama_akun, tbl_akun.no_akun FROM tbl_akun_detail JOIN tbl_akun ON tbl_akun_detail.id_akun=tbl_akun.id_akun WHERE tbl_akun_detail.id_akun='.$id.''));
        return $results;
    }
    public function neraca(Request $request)
    {
        if (!auth()->user()->can('akuntansi.neraca')) {
            abort(403, 'Unauthorized action.');
        }

        $user_id = request()->session()->get('user.id');
        $user = User::where('id', $user_id)->first();   
        $date=0;
        $location_id=0;
        if ($request->input('bulan')) {
            $date=$request->input('tahun').'-'.$request->input('bulan');
            $location_id=$request->input('location_id');
        }else{
            $date=date('Y-m');
            $location_id=$user->location_id;
        }
        $bulan=json_encode(explode('-', $date));
        // $detail_saldo=array();
        // $data_saldo=$this->cekAllJurnal($date, $user->location_id);
        // foreach ($data_saldo as $key => $value) {
        //     $saldo=DB::table('tbl_saldo_akun')
        //             ->where('id_akun', $value->id_akun)
        //             ->where('tanggal', $date);
        //     if ($user->location_id != null) {
        //         $saldo->where('location_id', $user->location_id);
        //     }
        //     $results=$saldo->first();
        //     $detail_saldo[$key]['data']=$value;
        //     $detail_saldo[$key]['detail']=$results;
        // }
        $asset=$this->getSaldo(null, $date, $location_id, null);
        
        $business_location = BusinessLocation::all();
        $business_locations=array();
        foreach ($business_location as $key => $value) {
            $business_locations[$value->id]=$value->name;
        }
        $data=array(
            'date'      => $date,
            'bulan'     => json_encode(explode('-', $date)),
            'saldo'     => $asset
        );

        return view('akuntansi.balance_sheet')->with(compact('data', 'bulan', 'user', 'location_id', 'business_locations'));
    }
/*     public function labaRugi(Request $request){
        if (!auth()->user()->can('akuntansi.profit-loss')) {
            abort(403, 'Unauthorized action.');
        }

        $user_id = request()->session()->get('user.id');
        $user = User::where('id', $user_id)->first();   
        $user_location=$user->location_id;
        $location_id=0;
        $date=0;
        $business_location = BusinessLocation::all();
        $business_locations=array();
        foreach ($business_location as $key => $value) {
            $business_locations[$value->id]=$value->name;
        }
        if ($user->location_id != null) {
            $location_id=$user->location_id;
        }

        $query=BusinessLocation::select('*');
        
        if ($request->input('bulan')) {
            $date=$request->input('tahun').'-'.$request->input('bulan');
            $location_id=$request->input('location_id');
            $query->where('id', $location_id);
        }else if(request()->session()->get('location_id')){
            $date=request()->session()->get('bulan');
            $location_id=request()->session()->get('location_id');
            $query->where('id', $location_id);
        }else{
            $date=date('Y-m');
            $location_id=$user->location_id;
            $query->where('id', $location_id);
        }
        
        $bl=$query->get();
        $dividen_mitra=$dividen_bisnis=0;
        if ($location_id != null) {
            // $location_id=$user->location_id;
            $dividen_mitra=$bl[0]->dividen_mitra;
            $dividen_bisnis=$bl[0]->dividen_bisnis;
        }
        
        $royalty_fee=0;
        foreach ($bl as $key => $value) {
            $royalty_fee+=$value->royalty_fee;
        }

        $data['bulan']=json_encode(explode('-', $date));
        $data['pendapatan']=$this->getLabaRugi(6, $date, $location_id);
        $data['beban']=$this->getLabaRugi(7, $date, $location_id);
        
        $query_pengeluaran=DB::table('tbl_pengeluaran')
                    ->select(DB::raw("SUM(tbl_pengeluaran.total) as total"))
                    ->join('users', 'users.id', '=', 'tbl_pengeluaran.user_id')
                    ->where('tanggal', 'like', '%'.$date.'%')
                    ->where('tipe', 'pengeluaran');
        if ($location_id != 0) {
            $query_pengeluaran->where('users.location_id', $location_id);
        }
        $pengeluaran=$query_pengeluaran->first();
        
        $query_pengeluaran_other=DB::table('tbl_pengeluaran_other')
                    ->select(DB::raw("COALESCE(SUM(IF(notes='Pengeluaran Manajemen' && is_entry=true, tbl_pengeluaran_other.total, 0)) - SUM(IF(notes='Pengeluaran Manajemen' && is_entry=false, tbl_pengeluaran_other.total, 0)), 0) as total_manajemen"), DB::raw('SUM(IF(notes="Pengeluaran Sewa" && is_entry=true, tbl_pengeluaran_other.total, 0)) AS total_sewa'), DB::raw('SUM(IF(notes="Tabungan THR" && is_entry=true, tbl_pengeluaran_other.total, 0)) AS total_thr'), DB::raw('SUM(IF(notes="Tabungan Amortisasi" && is_entry=true, tbl_pengeluaran_other.total, 0)) AS total_amortisasi'), DB::raw('SUM(IF(notes="Deposit Pegawai" && is_entry=true, tbl_pengeluaran_other.total, 0)) AS total_deposit'))
                    ->join('users', 'users.id', '=', 'tbl_pengeluaran_other.user_id')
                    ->where('tanggal', 'like', $date.'%');
                    // ->where('notes', 'Pengeluaran Manajemen')
        if ($location_id != 0) {
            $query_pengeluaran_other->where('tbl_pengeluaran_other.location_id', $location_id);
        }
        $pengeluaran_other=$query_pengeluaran_other->first();

        $query_transfer_mitra=DB::table('tbl_transfer_mitra')
                    ->where('bulan', $date);
        if ($location_id != 0) {
            $query_transfer_mitra->where('location_id', $location_id);
        }
        $transfer_mitra=$query_transfer_mitra->first();
        
        $query_gaji=DB::table('tbl_history_gaji')
                        ->select(DB::raw('COALESCE(SUM(total_gaji), 0) as total_gaji'), DB::raw('COALESCE(SUM(gaji_without_kasbon), 0) as gaji_without_kasbon'))
                        ->join('tbl_pegawai', 'tbl_pegawai.id_pegawai', '=', 'tbl_history_gaji.id_pegawai')
                        ->where('tbl_history_gaji.bulan', $date);

        if ($location_id != 0) {
            $query_gaji->where('tbl_pegawai.location_id', $location_id);
        }
        $gaji_report=$query_gaji->first();

        return view('akuntansi.profit_loss_list')->with(compact('data', 'user_location', 'pengeluaran', 'business_locations', 'user', 'location_id', 'royalty_fee', 'dividen_bisnis', 'dividen_mitra', 'pengeluaran_other', 'date', 'transfer_mitra', 'gaji_report'));
    }
    */
    public function labaRugi(Request $request){
        if (!auth()->user()->can('akuntansi.profit-loss')) {
            abort(403, 'Unauthorized action.');
        }

        $user_id = request()->session()->get('user.id');
        $user = User::where('id', $user_id)->first();   
        $user_location=$user->location_id;
        $location_id=0;
        $date=0;
        $business_location = BusinessLocation::all();
        $business_locations=array();
        foreach ($business_location as $key => $value) {
            $business_locations[$value->id]=$value->name;
        }
        if ($user->location_id != null) {
            $location_id=$user->location_id;
        }

        $query=BusinessLocation::select('*');
        
        if ($request->input('bulan')) {
            $date=$request->input('tahun').'-'.$request->input('bulan');
            $location_id=$request->input('location_id');
            $query->where('id', $location_id);
        }else if(request()->session()->get('location_id')){
            $date=request()->session()->get('bulan');
            $location_id=request()->session()->get('location_id');
            $query->where('id', $location_id);
        }else{
            $date=date('Y-m');
            $location_id=$user->location_id;
            $query->where('id', $location_id);
        }
        
        $bl=$query->get();

        $data['bulan']=json_encode(explode('-', $date));
        $pengeluaran = DB::table('tbl_pengeluaran')
        ->selectRaw('COALESCE(sum(total),0) as jml')
        ->where("tipe","=","pengeluaran")
        ->where("location_id",$location_id)
        ->where("tanggal","like","%$date%")->first();
        $akuntansi = array(
            'penjualan' => $this->getDetailAkuntansi(20,$date,'Pendapatan Transaksi',$location_id),
            'hpp' => $this->getDetailAkuntansi(65,$date,'',$location_id),
            'potongan_aplikasi' => $this->getDetailAkuntansi(130,$date,'',$location_id),
            'waste_bahan' => $this->getDetailAkuntansi(131,$date,'',$location_id),
            'waste_produk' => $this->getDetailAkuntansi(132,$date,'',$location_id),
            'promo_produk' => $this->getDetailAkuntansi(133,$date,'',$location_id),
            'pengeluaran' => $pengeluaran->jml,
            'pengeluaran_manajemen' => $this->getPengeluaranOther('Pengeluaran Manajemen',$date,$location_id),
            'pengeluaran_sewa' => $this->getPengeluaranOther('Pengeluaran Sewa',$date,$location_id),
            'tabungan_thr' => $this->getPengeluaranOther('Tabungan THR',$date,$location_id),
            'tabungan_amortisasi' => $this->getPengeluaranOther('Tabungan Amortisasi',$date,$location_id),
        );
        return view('akuntansi.laba_rugi')->with(compact('data','user_location', 'business_locations', 'user', 'location_id', 'date','akuntansi'));
    }
    
    public function getPengeluaranOther($notes,$date,$location_id)
    {
        $pengeluaranOther = DB::table('tbl_pengeluaran_other')
        ->selectRaw('COALESCE(sum(total),0) as total')
        ->where("notes",$notes)
        ->where("location_id",$location_id)
        ->where("tanggal","like","%$date%")->first();

        return $pengeluaranOther->total;
    }


    public function getDetailAkuntansi($idAkun,$date,$desc="",$location_id)
    {
        $data = DB::table('tbl_trx_akuntansi_detail as d')
        ->selectRaw('COALESCE(sum(jumlah),0) as jml')
        ->join('tbl_trx_akuntansi as a','d.id_trx_akun','a.id_trx_akun')
        ->where("location_id",$location_id);
        if($desc!=""){
            $data = $data->where('deskripsi','like',"%$desc%");
        }
        $data = $data->where('tanggal','like',"%$date%");
        if(is_array($idAkun)){
            $whereRaw = "";
            $count = count($idAkun);
            foreach ($idAkun as $key => $value) {
                $or = ($key+1)!=$idAkun ? ' or' : '';
                $whereRaw.="(id_akun = '".$value."' )".$or;
            }
            $data = $data->whereRaw($whereRaw);
        }else{
            $data = $data->where('id_akun',$idAkun);
        }
        $data = $data->first();

        return $data->jml;
    }

    private function cekAllJurnal($date, $location_id){
        $results = DB::select( DB::raw('SELECT COALESCE((SELECT SUM(trd.jumlah) as jumlah FROM tbl_trx_akuntansi_detail 
            trd JOIN tbl_trx_akuntansi tra ON trd.id_trx_akun=tra.id_trx_akun JOIN tbl_akun_detail tad ON 
            tad.id_akun=trd.id_akun WHERE tra.tanggal LIKE "'.$date.'%" '.($location_id != null ? "AND tra.location_id=".$location_id : "").' AND tad.id_akun=tbl_akun_detail.id_akun 
            AND trd.tipe="KREDIT"), 0) + COALESCE((SELECT SUM(trd.jumlah) as jumlah FROM tbl_trx_akuntansi_detail trd 
            JOIN tbl_trx_akuntansi tra ON trd.id_trx_akun=tra.id_trx_akun JOIN tbl_akun_detail tad ON 
            tad.id_akun=trd.id_akun WHERE tra.tanggal LIKE "'.$date.'%" '.($location_id != null ? "AND tra.location_id=".$location_id : "").' AND tad.turunan1=tbl_akun_detail.id_akun AND 
            trd.tipe="KREDIT"), 0) AS jumlah_kredit, COALESCE((SELECT SUM(trd.jumlah) as jumlah 
            FROM tbl_trx_akuntansi_detail trd JOIN tbl_trx_akuntansi tra ON trd.id_trx_akun=tra.id_trx_akun JOIN 
            tbl_akun_detail tad ON tad.id_akun=trd.id_akun WHERE tra.tanggal LIKE "'.$date.'%" '.($location_id != null ? "AND tra.location_id=".$location_id : "").' AND 
            tad.id_akun=tbl_akun_detail.id_akun AND trd.tipe="DEBIT"), 0) + COALESCE((SELECT SUM(trd.jumlah) as jumlah 
            FROM tbl_trx_akuntansi_detail trd JOIN tbl_trx_akuntansi tra ON trd.id_trx_akun=tra.id_trx_akun JOIN 
            tbl_akun_detail tad ON tad.id_akun=trd.id_akun WHERE tra.tanggal LIKE "'.$date.'%" '.($location_id != null ? "AND tra.location_id=".$location_id : "").' AND 
            tad.turunan1=tbl_akun_detail.id_akun AND trd.tipe="DEBIT"), 0) AS jumlah_debit, MAX(tbl_akun_detail.id_akun) AS 
            id_akun, MAX(tbl_akun.no_akun) AS no_akun, MAX(tbl_akun.nama_akun) AS nama_akun, MAX(tbl_akun_detail.turunan1) AS 
            turunan1, MAX(tbl_akun_detail.id_parent) AS id_parent FROM `tbl_akun_detail` JOIN tbl_akun ON 
            tbl_akun_detail.id_akun=tbl_akun.id_akun WHERE turunan1=0 GROUP BY tbl_akun_detail.id_akun ORDER BY tbl_akun.no_akun'));
        return $results;
    }

    private function getLabaRugi($id, $date, $location_id){
        $results = DB::select( DB::raw('SELECT COALESCE((SELECT SUM(trd.jumlah) as jumlah FROM tbl_trx_akuntansi_detail 
            trd JOIN tbl_trx_akuntansi tra ON trd.id_trx_akun=tra.id_trx_akun JOIN tbl_akun_detail tad ON 
            tad.id_akun=trd.id_akun WHERE tra.tanggal LIKE "'.$date.'%" '.($location_id != null ? "AND tra.location_id=".$location_id : "").' AND tad.id_akun=tbl_akun_detail.id_akun 
            AND trd.tipe="KREDIT"), 0) + COALESCE((SELECT SUM(trd.jumlah) as jumlah FROM tbl_trx_akuntansi_detail trd 
            JOIN tbl_trx_akuntansi tra ON trd.id_trx_akun=tra.id_trx_akun JOIN tbl_akun_detail tad ON 
            tad.id_akun=trd.id_akun WHERE tra.tanggal LIKE "'.$date.'%" '.($location_id != null ? "AND tra.location_id=".$location_id : "").' AND tad.turunan1=tbl_akun_detail.id_akun AND 
            trd.tipe="KREDIT"), 0) AS jumlah_kredit, COALESCE((SELECT SUM(trd.jumlah) as jumlah 
            FROM tbl_trx_akuntansi_detail trd JOIN tbl_trx_akuntansi tra ON trd.id_trx_akun=tra.id_trx_akun JOIN 
            tbl_akun_detail tad ON tad.id_akun=trd.id_akun WHERE tra.tanggal LIKE "'.$date.'%" '.($location_id != null ? "AND tra.location_id=".$location_id : "").' AND 
            tad.id_akun=tbl_akun_detail.id_akun AND trd.tipe="DEBIT"), 0) + COALESCE((SELECT SUM(trd.jumlah) as jumlah 
            FROM tbl_trx_akuntansi_detail trd JOIN tbl_trx_akuntansi tra ON trd.id_trx_akun=tra.id_trx_akun JOIN 
            tbl_akun_detail tad ON tad.id_akun=trd.id_akun WHERE tra.tanggal LIKE "'.$date.'%" '.($location_id != null ? "AND tra.location_id=".$location_id : "").' AND 
            tad.turunan1=tbl_akun_detail.id_akun AND trd.tipe="DEBIT"), 0) AS jumlah_debit, MAX(tbl_akun_detail.id_akun) AS 
            id_akun, MAX(tbl_akun.no_akun) AS no_akun, MAX(tbl_akun.nama_akun) AS nama_akun, MAX(tbl_akun_detail.turunan1) AS 
            turunan1, MAX(tbl_akun_detail.id_parent) AS id_parent FROM `tbl_akun_detail` JOIN tbl_akun ON 
            tbl_akun_detail.id_akun=tbl_akun.id_akun WHERE turunan1=0 AND id_parent='.$id.' GROUP BY tbl_akun_detail.id_akun ORDER BY tbl_akun.no_akun'));
        return $results;
    }
    /**
     * Show the form for creating a new resource.
     * @return Response
     */

    public function createJournal()
    {
        if (!auth()->user()->can('akuntansi.jurnal')) {
            abort(403, 'Unauthorized action.');
        }

        $user_id = request()->session()->get('user.id');
        $user = User::where('id', $user_id)->first();
        $location_id = $user->location_id;

        $business_location = BusinessLocation::all();
        $business_locations=array();
        foreach ($business_location as $key => $value) {
            $business_locations[$value->id]=$value->name;
        }
        
        $data['akun_option']=array();
        $data['akun_option'][''] = 'Pilih Akun / No Akun';
        $akun_option_js=array();
        $count=$c=0;
        $dataLevel0=DB::table('tbl_akun')->where('level', 0)->get();
        foreach ($dataLevel0 as $key => $value) {
            $id_akun=$no_akun=$nama_akun=0;
            $dataLevel1=DB::table('tbl_akun')->where('level', 1)->where('id_main_akun', $value->id_akun)->get();
            foreach ($dataLevel1 as $k => $v) {
                $id_akun=$v->id_akun;
                $no_akun=$v->no_akun;
                $nama_akun=$v->nama_akun;
                $dataLevel2=DB::table('tbl_akun')->where('level', 2)->where('id_main_akun', $v->id_akun)->get();
                foreach ($dataLevel2 as $k2 => $v2) {
                    $id_akun=$v2->id_akun;
                    $no_akun=$v2->no_akun;
                    $nama_akun=$v2->nama_akun;
                    $dataLevel3=DB::table('tbl_akun')->where('level', 3)->where('id_main_akun', $v2->id_akun)->get();
                    foreach ($dataLevel3 as $k3 => $v3) {
                        $c++;
                        $id_akun=$v3->id_akun;
                        $no_akun=$v3->no_akun;
                        $nama_akun=$v3->nama_akun;
                        $data['akun_option'][$id_akun]=$no_akun. ' | '.$nama_akun;
                        $akun_option_js[]=array(
                            'label'     => $id_akun,
                            'value'     => $no_akun. ' | '.$nama_akun
                        );
                    }
                    $data['akun_option'][$id_akun]=$no_akun. ' | '.$nama_akun;
                    $akun_option_js[]=array(
                        'label'     => $id_akun,
                        'value'     => $no_akun. ' | '.$nama_akun
                    );
                }
                $data['akun_option'][$id_akun]=$no_akun. ' | '.$nama_akun;
                $akun_option_js[]=array(
                    'label'     => $id_akun,
                    'value'     => $no_akun. ' | '.$nama_akun
                );
            }
        }
        $data['akun_option_js']=json_encode($akun_option_js);

        return view('akuntansi.create_journal')
                ->with(compact('data', 'business_locations', 'location_id', 'user'));
    }

    public function storeJurnal(Request $request)
    {
        // print_r($request->input());exit();
        if (!auth()->user()->can('akuntansi.jurnal')) {
            abort(403, 'Unauthorized action.');
        }

        $deskripsi=$request->input('deskripsi');
        $location_id=$request->input('location_id');
        $tgl=$request->input('tanggal');
        $id_akun=$request->input('akun');
        $jumlah=$request->input('jumlah_akun');
        $tipe_akun=$request->input('tipe_akun');
        $id_lawan=$request->input('lawan_akun');
        $jumlah_lawan=$request->input('jumlah_lawan_akun');
        $tipe_akun_lawan=$request->input('tipe_lawan_akun');
        $data_trx=array(
                        'deskripsi'     => $deskripsi,
                        'location_id'     => $location_id,
                        'tanggal'       => $tgl,
                    );
        $insert=DB::table('tbl_trx_akuntansi')->insert($data_trx);
        // $insert=1;
        if ($insert) {
            $id_last=DB::table('tbl_trx_akuntansi')->max('id_trx_akun');
            $data=array(
                        'id_trx_akun'   => $id_last,
                        'id_akun'       => $id_akun,
                        'jumlah'        => $jumlah,
                        'tipe'          => ($request->input('tipe_akun') == 0 ? "KREDIT" : "DEBIT"),
                        'keterangan'    => 'akun',
                    );
            DB::table('tbl_trx_akuntansi_detail')->insert($data);
            for ($i=0; $i < count($id_lawan); $i++) { 
                if ($id_lawan[$i] != null) {
                    $data=array(
                        'id_trx_akun'   => $id_last,
                        'id_akun'       => $id_lawan[$i],
                        'jumlah'        => $jumlah_lawan[$i],
                        'tipe'          => ($tipe_akun_lawan[$i] == 0 ? "KREDIT" : "DEBIT"),
                        'keterangan'    => 'lawan',
                    );
                    // $this->updateSaldo($id_lawan[$i], $jumlah_lawan[$i], $tipe_akun_lawan[$i]);
                    DB::table('tbl_trx_akuntansi_detail')->insert($data);
                }
            }
        }
        return redirect('akuntansi/jurnal');
    }
    private function jurnalTutupBuku($data){
        $data_trx=array(
                        'deskripsi'     => 'Jurnal Tutup Buku '.$data['tgl'],
                        'location_id'     => $data['location_id'],
                        'tanggal'       => $data['tgl'],
                        'notes'       => 'jurnal tutup buku',
                    );
        $insert=DB::table('tbl_trx_akuntansi')->insert($data_trx);
        if ($insert) {
            $id_last=DB::table('tbl_trx_akuntansi')->max('id_trx_akun');
            $data1=array(
                        'id_trx_akun'   => $id_last,
                        'id_akun'       => $data['id_akun'],
                        'jumlah'        => $data['total'],
                        'tipe'          => ($data['tipe_akun'] == 0 ? "KREDIT" : "DEBIT"),
                        'keterangan'    => 'akun',
                    );
            DB::table('tbl_trx_akuntansi_detail')->insert($data1);
            $data1=array(
                'id_trx_akun'   => $id_last,
                'id_akun'       => $data['id_lawan'],
                'jumlah'        => $data['total'],
                'tipe'          => ($data['tipe_akun_lawan'] == 0 ? "KREDIT" : "DEBIT"),
                'keterangan'    => 'lawan',
            );
            DB::table('tbl_trx_akuntansi_detail')->insert($data1);
        }
    }
    public function closeBook(Request $request){
        if (!auth()->user()->can('akuntansi.close-book')) {
            abort(403, 'Unauthorized action.');
        }
        $user_id = request()->session()->get('user.id');
        $user = User::where('id', $user_id)->first();
        $location_id = $user->location_id;

        $date=0;
        if ($request->input('bulan')) {
            $date=$request->input('tahun').'-'.$request->input('bulan');
        }else{
            $date=date('Y-m');
        }

        $bulan=explode('-', $date);
        
        if ($request->input('submit')) {

            $location_id=$request->input('location_id');
            $time = strtotime($date);
            $final = date('Y-m', strtotime("+1 month", $time));
            $cekJurnal=DB::table('tbl_saldo_akun')->where('tanggal', $final)->where('location_id', $location_id)->count();
            // $jurnal=$this->cekAllJurnal($date, $location_id);
            // $bl=BusinessLocation::where('id', $location_id)->first();

            $jumlah_hari=cal_days_in_month(CAL_GREGORIAN, $bulan[1], $bulan[0]);
            $pendapatan=$this->getSaldo(6, $date, $location_id, null);
            $beban=$this->getSaldo(7, $date, $location_id, null);
            foreach ($pendapatan as $key => $value) {
                foreach ($value['data'] as $v) {
                    $total=$v['detail'][0]->jumlah_kredit - $v['detail'][0]->jumlah_debit;
                    $data=array(
                        'id_akun'   => 114,
                        'id_lawan'   => $v['detail'][0]->id_akun,
                        'total'   => ($total < 0 ? abs($total) : $total),
                        // 'total'   => $total,
                        'tipe_akun'   => ($total < 0 ? 1 : 0),
                        'tipe_akun_lawan'   => ($total < 0 ? 0 : 1),
                        'location_id'   => $location_id,
                        'tgl'   => $date.'-'.$jumlah_hari,
                        'name'   => $v['detail'][0]->nama_akun,
                    );
                    if ($total != 0) {
                        $this->jurnalTutupBuku($data);
                    }
                }
            }
            foreach ($beban as $key => $value) {
                foreach ($value['data'] as $v) {
                    $total=$v['detail'][0]->jumlah_debit - $v['detail'][0]->jumlah_kredit;
                    $data=array(
                        'id_akun'   => 114,
                        'id_lawan'   => $v['detail'][0]->id_akun,
                        'total'   => ($total < 0 ? abs($total) : $total),
                        // 'total'   => $total,
                        'tipe_akun'   => ($total < 0 ? 0 : 1),
                        'tipe_akun_lawan'   => ($total < 0 ? 1 : 0),
                        'location_id'   => $location_id,
                        'tgl'   => $date.'-'.$jumlah_hari,
                        'name'   => $v['detail'][0]->nama_akun,
                    );
                    if ($total != 0) {
                        $this->jurnalTutupBuku($data);
                    }
                }
            }
            exit();
            $asset=$this->getSaldo(null, $date, $location_id, null);

            $total_pendapatan=$total_beban=$total_kas=0;
            foreach ($asset as $key => $value) {
                foreach ($value['data'] as $v){
                    $saldo=$v['saldo'];
                    if ($value['id_parent'] == 3 || $value['id_parent'] == 7) {
                        $jumlah_saldo=(($saldo != null ? $saldo->jumlah_saldo : 0) + $v['detail'][0]->jumlah_debit) - $v['detail'][0]->jumlah_kredit;

                        // if ($value->id_parent == 7) {
                        //     $total_beban+=($value->jumlah_debit - $value->jumlah_kredit);
                        // }
                        // if ($value->id_akun == 20) {
                        //     $total_kas=$jumlah_saldo;
                        // }
                        $data_saldo=array(
                            'id_akun'   => $v['detail'][0]->id_akun,
                            'jumlah_saldo'  => $jumlah_saldo,
                            'tanggal'   => $date,
                            'location_id'   => $location_id,
                            'is_updated'   => 0,
                        );
                        // print_r($data_saldo);
                        // echo "<br>";
                        $ceksaldo=DB::table('tbl_saldo_akun')->where('tanggal', $final)->where('location_id', $location_id)->where('id_akun', $v['detail'][0]->id_akun)->first();
                        if ($ceksaldo == null) {
                            DB::table('tbl_saldo_akun')->insert($data_saldo);
                        }else{
                            DB::table('tbl_saldo_akun')->where('id_saldo', $ceksaldo->id_saldo)->update($data_saldo);
                        }
                    }else{
                        if ($v['detail'][0]->id_akun != 56) {
                            $jumlah_saldo=(($saldo != null ? $saldo->jumlah_saldo : 0) + $v['detail'][0]->jumlah_kredit) - $v['detail'][0]->jumlah_debit;
                            // if ($value['id_parent'] == 6) {
                            //     $total_pendapatan+=($value->jumlah_kredit - $value->jumlah_debit);
                            // }
                            $data_saldo=array(
                                'id_akun'   => $v['detail'][0]->id_akun,
                                'jumlah_saldo'  => $jumlah_saldo,
                                'tanggal'   => $date,
                                'location_id'   => $location_id,
                                'is_updated'   => 0,
                            );
                            // print_r($data_saldo);
                            // echo "<br>";
                            $ceksaldo=DB::table('tbl_saldo_akun')->where('tanggal', $final)->where('location_id', $location_id)->where('id_akun', $v['detail'][0]->id_akun)->first();
                            if ($ceksaldo == null) {
                                DB::table('tbl_saldo_akun')->insert($data_saldo);
                            }else{
                                DB::table('tbl_saldo_akun')->where('id_saldo', $ceksaldo->id_saldo)->update($data_saldo);
                            }
                        }
                    }
                }
            }
            $output = ['success' => 1,
                            'msg' => 'success'
                        ];
            return redirect('akuntansi/close-book')->with('status', $output);
        }
        // if ($request->input('submit')) {

        //     $location_id=$request->input('location_id');
        //     $time = strtotime($date);
        //     $final = date('Y-m', strtotime("+1 month", $time));
        //     $cekJurnal=DB::table('tbl_saldo_akun')->where('tanggal', $final)->where('location_id', $location_id)->count();
        //     $jurnal=$this->cekAllJurnal($date, $location_id);
            

        //     $total_pendapatan=$total_beban=$total_kas=0;
        //     foreach ($jurnal as $key => $value) {
        //         $saldo=DB::table('tbl_saldo_akun')->where('id_akun', $value->id_akun)->where('location_id', $location_id)->where('tanggal', $date)->first();
        //         if ($value->id_parent == 3 || $value->id_parent == 7) {
        //             $jumlah_saldo=(($saldo != null ? $saldo->jumlah_saldo : 0) + $value->jumlah_debit) - $value->jumlah_kredit;
        //             if ($value->id_parent == 7) {
        //                 $total_beban+=($value->jumlah_debit - $value->jumlah_kredit);
        //             }
        //             if ($value->id_akun == 20) {
        //                 $total_kas=$jumlah_saldo;
        //             }
        //             $data_saldo=array(
        //                 'id_akun'   => $value->id_akun,
        //                 'jumlah_saldo'  => $jumlah_saldo,
        //                 'tanggal'   => $final,
        //                 'location_id'   => $location_id,
        //                 'is_updated'   => 0,
        //             );
        //             $ceksaldo=DB::table('tbl_saldo_akun')->where('tanggal', $final)->where('location_id', $location_id)->where('id_akun', $value->id_akun)->first();
        //             if ($ceksaldo == null) {
        //                 DB::table('tbl_saldo_akun')->insert($data_saldo);
        //             }else{
        //                 DB::table('tbl_saldo_akun')->where('id_saldo', $ceksaldo->id_saldo)->update($data_saldo);
        //             }
        //         }else{
        //             if ($value->id_akun != 56) {
        //                 $jumlah_saldo=(($saldo != null ? $saldo->jumlah_saldo : 0) + $value->jumlah_kredit) - $value->jumlah_debit;
        //                 if ($value->id_parent == 6) {
        //                     $total_pendapatan+=($value->jumlah_kredit - $value->jumlah_debit);
        //                 }
        //                 $data_saldo=array(
        //                     'id_akun'   => $value->id_akun,
        //                     'jumlah_saldo'  => $jumlah_saldo,
        //                     'tanggal'   => $final,
        //                     'location_id'   => $location_id,
        //                     'is_updated'   => 0,
        //                 );
        //                 $ceksaldo=DB::table('tbl_saldo_akun')->where('tanggal', $final)->where('location_id', $location_id)->where('id_akun', $value->id_akun)->first();
        //                 if ($ceksaldo == null) {
        //                     DB::table('tbl_saldo_akun')->insert($data_saldo);
        //                 }else{
        //                     DB::table('tbl_saldo_akun')->where('id_saldo', $ceksaldo->id_saldo)->update($data_saldo);
        //                 }
        //             }
        //         }
        //     }
        //     $output = ['success' => 1,
        //                     'msg' => 'success'
        //                 ];
        //     return redirect('akuntansi/close-book')->with('status', $output);
        // }

        $business_location = BusinessLocation::all();
        $business_locations=array();
        foreach ($business_location as $key => $value) {
            $business_locations[$value->id]=$value->name;
        }

        return view('akuntansi.close_book')
                ->with(compact('bulan', 'location_id', 'business_locations', 'user'));
    }
    public function countRoyaltyFee(Request $request){
        $date=$request->input('date');
        $location_id=$request->input('location_id');
        $data=$this->getLabaRugi(6, $date, $location_id);
        $bl=BusinessLocation::select('royalty_fee')
                                    ->where('id', $location_id)
                                    ->first();
        $sum_pendapatan=0;
        foreach ($data as $key => $value) {
            $sum_pendapatan+=($value->jumlah_kredit - $value->jumlah_debit);
        }
        $royalty_fee=$sum_pendapatan * ($bl->royalty_fee / 100);
        return $royalty_fee;
    }
    public function rekapPc(Request $request){
        if (!auth()->user()->can('akuntansi.rekap-pc')) {
            abort(403, 'Unauthorized action.');
        }
        $user_id = request()->session()->get('user.id');
        $user = User::where('id', $user_id)->first();
        $location_id = $user->location_id;

        $date=0;
        if ($request->input('bulan')) {
            $date=$request->input('tahun').'-'.$request->input('bulan');
            $jumlah_hari=cal_days_in_month(CAL_GREGORIAN, $request->input('bulan'), $request->input('tahun'));
        }else{
            $date=date('Y-m');
            $jumlah_hari=cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
        }
        $detail=array();
        $bulan=json_encode(explode('-', $date));
        for ($i=1; $i <= $jumlah_hari; $i++) { 
            $tanggal=$date.'-'.(strlen($i) < 2 ? '0'.$i : $i);
            $detail[$i]['date']=$tanggal;
            $list_pc=$this->listPettyCashByDate($tanggal, $location_id);
            foreach ($list_pc as $key => $value) {
                $detail[$i]['data'][$key]=$this->getTrxPetty($value->id_trx_akun, $location_id);
            }
        }
        return view('akuntansi.rekap_pc')
                ->with(compact('detail', 'bulan'));
    }
    public function rekapTransaksi(Request $request){
        if (!auth()->user()->can('akuntansi.rekap-trx')) {
            abort(403, 'Unauthorized action.');
        }
        $user_id = request()->session()->get('user.id');
        $user = User::where('id', $user_id)->first();
        $location_id = $user->location_id;

        $date=0;
        if ($request->input('bulan')) {
            $date=$request->input('tahun').'-'.$request->input('bulan');
            $jumlah_hari=cal_days_in_month(CAL_GREGORIAN, $request->input('bulan'), $request->input('tahun'));
        }else{
            $date=date('Y-m');
            $jumlah_hari=cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y'));
        }
        $detail=array();
        $bulan=json_encode(explode('-', $date));
        for ($i=1; $i <= $jumlah_hari; $i++) { 
            $tanggal=$date.'-'.(strlen($i) < 2 ? '0'.$i : $i);
            $detail[$i]['date']=$tanggal;
            $list_pc=$this->listTrx($tanggal, $location_id);
            foreach ($list_pc as $key => $value) {
                $detail[$i]['data'][$key]=$this->getTrx($value->id_trx_akun, $location_id);
            }
        }
        
        return view('akuntansi.rekap_trx')
                ->with(compact('detail', 'bulan'));
    }
    private function getTrxPetty($id, $location_id){
        $query = DB::table('tbl_trx_akuntansi_detail')
                            ->join('tbl_trx_akuntansi', 'tbl_trx_akuntansi.id_trx_akun', '=', 'tbl_trx_akuntansi_detail.id_trx_akun')
                            ->join('tbl_akun', 'tbl_akun.id_akun', '=', 'tbl_trx_akuntansi_detail.id_akun')
                            ->select('tbl_trx_akuntansi_detail.*','tbl_akun.*', 'tbl_trx_akuntansi.deskripsi')
                            ->where('tbl_trx_akuntansi_detail.id_trx_akun', $id)
                            ->where('tipe', 'DEBIT');

        if ($location_id != null) {
            $query->where('tbl_trx_akuntansi.location_id', $location_id);
        }
        $results=$query->get();
        return $results;
    }
    private function getTrx($id, $location_id){
        $query = DB::table('tbl_trx_akuntansi_detail')
                            ->join('tbl_trx_akuntansi', 'tbl_trx_akuntansi.id_trx_akun', '=', 'tbl_trx_akuntansi_detail.id_trx_akun')
                            ->join('tbl_akun', 'tbl_akun.id_akun', '=', 'tbl_trx_akuntansi_detail.id_akun')
                            ->where('tbl_trx_akuntansi_detail.id_trx_akun', $id)
                            // ->whereIn('tbl_trx_akuntansi_detail.id_akun', [91, 39])
                            ->where('tipe', 'KREDIT')
                            ->select('tbl_trx_akuntansi_detail.*','tbl_akun.*', 'tbl_trx_akuntansi.deskripsi');
        if ($location_id != null) {
            $query->where('tbl_trx_akuntansi.location_id', $location_id);
        }
        $results=$query->get();
        return $results;
    }
    private function listPettyCashByDate($date, $location_id){
        $query=DB::table('tbl_trx_akuntansi')
                            ->join('tbl_trx_akuntansi_detail', 'tbl_trx_akuntansi.id_trx_akun', '=', 'tbl_trx_akuntansi_detail.id_trx_akun')
                            ->select('tbl_trx_akuntansi.id_trx_akun')
                            ->where('tanggal', $date)
                            ->where('id_akun', 35);
                            
        if ($location_id != null) {
            $query->where('tbl_trx_akuntansi.location_id', $location_id);
        }
        $results=$query->get();
        return $results;
    }
    private function listTrx($date, $location_id){
        $query= DB::table('tbl_trx_akuntansi')
                            ->join('tbl_trx_akuntansi_detail', 'tbl_trx_akuntansi.id_trx_akun', '=', 'tbl_trx_akuntansi_detail.id_trx_akun')
                            ->where('tanggal', $date)
                            ->whereIn('id_akun', [46, 91, 115, 39])
                            // ->orWhere('id_akun','=', 39)
                            ->select('tbl_trx_akuntansi.id_trx_akun')
                            ->groupBy('tbl_trx_akuntansi.id_trx_akun');
        if ($location_id != null) {
            $query->where('tbl_trx_akuntansi.location_id', $location_id);
        }
        $results=$query->get();
        return $results;
    }
    // private function listTrx($date){
    //     $results = DB::select('SELECT * FROM tbl_trx_akuntansi JOIN tbl_trx_akuntansi_detail ON 
    //         tbl_trx_akuntansi_detail.id_trx_akun=tbl_trx_akuntansi.id_trx_akun WHERE tbl_trx_akuntansi_detail.id_akun=91 
    //         OR tbl_trx_akuntansi_detail.id_akun=39 AND tanggal="'.$date.'"');
    //     return $results;
    // }
    // private function listPettyCashByDate($date){
    //     $results = DB::select( DB::raw('SELECT COALESCE((SELECT SUM(jumlah) FROM tbl_trx_akuntansi_detail trd1 JOIN 
    //         tbl_trx_akuntansi tra1 ON trd1.id_trx_akun=tra1.id_trx_akun WHERE tra1.tanggal="'.$date.'" AND trd1.tipe="KREDIT" 
    //         AND trd1.id_akun=trd.id_akun),0) AS jumlah_kredit, COALESCE((SELECT SUM(jumlah) FROM tbl_trx_akuntansi_detail trd1 
    //         JOIN tbl_trx_akuntansi tra1 ON trd1.id_trx_akun=tra1.id_trx_akun WHERE tra1.tanggal="'.$date.'" AND trd1.tipe="DEBIT" 
    //         AND trd1.id_akun=trd.id_akun),0) AS jumlah_debit, ta.no_akun, ta.id_akun, ta.nama_akun, tra.deskripsi, tra.tanggal, 
    //         trd.tipe FROM tbl_trx_akuntansi tra JOIN tbl_trx_akuntansi_detail trd ON tra.id_trx_akun=trd.id_trx_akun JOIN 
    //         tbl_akun ta ON ta.id_akun=trd.id_akun WHERE tra.tanggal="'.$date.'" AND trd.tipe="DEBIT" AND tra.id_trx_akun IN 
    //         (SELECT id_trx_akun FROM tbl_trx_akuntansi_detail WHERE id_akun=35) GROUP BY id_akun ORDER BY no_akun'));
    //     return $results;
    // }
    public function generalLedger()
    {
        if (!auth()->user()->can('akuntansi.akun')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {

            $data=DB::table('tbl_akun')->where('level', '!=', 0)->get();
            $data1=array();
            foreach ($data as $key => $value) {
                $main=DB::table('tbl_akun')->where('id_akun', $value->id_main_akun)->first();
                $row=array();
                $row['id_akun']= $value->id_akun;
                $row['no_akun']= $value->no_akun;
                $row['nama_akun']= $value->nama_akun;
                $row['level']= $value->level;
                $row['debit']= $value->sifat_debit;
                $row['kredit']= $value->sifat_kredit;
                $row['main']= ($main != null ? $main->nama_akun : '');
                $data1[]=$row;
            }
            return DataTables::of($data1)
                                ->addColumn(
                                    'action',
                                    '<button data-href="" data-container=".account_model" class="btn btn-xs btn-primary btn-modal"><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</button>'
                                )
                                ->make(true);
        }
        return view('akuntansi.general_ledger');
    }
    public function detailGL(Request $request, $id)
    {
        if (!auth()->user()->can('akuntansi.akun')) {
            abort(403, 'Unauthorized action.');
        }
        $query=DB::table('tbl_akun_detail')
                    ->where('id_akun', $id)
                    ->orWhere('turunan1', $id)
                    ->orWhere('turunan2', $id)
                    ->orWhere('turunan3', $id)
                    ->pluck('id_akun');
        
        $user_id = request()->session()->get('user.id');
        $user = User::where('id', $user_id)->first();
        $location_id = $user->location_id;

        $date=0;
        if ($request->input('bulan')) {
            $date=$request->input('tahun').'-'.$request->input('bulan');
        }else{
            $date=date('Y-m');
        }

        $date_before=date('Y-m', strtotime("- 1 months",  strtotime($date)));
        $saldo_before=DB::table('tbl_saldo_akun')
                            ->where('tanggal', $date_before)
                            ->whereIn('id_akun', $query)
                            ->select(DB::raw('SUM(jumlah_saldo) as jumlah_saldo'))
                            ->first();

        $bulan=explode('-', $date);
        $jumlah_hari=cal_days_in_month(CAL_GREGORIAN, $bulan[1], $bulan[0]);
        for ($i=1; $i <= $jumlah_hari; $i++) { 
            $tanggal=$date.'-'.(strlen($i) < 2 ? '0'.$i : $i);
            $countSaldo=DB::table('tbl_trx_akuntansi_detail as trd')
                                // ->select(DB::raw('SUM(jumlah) as jumlah'))
                                ->select(DB::raw("SUM(CASE WHEN trd.tipe = 'DEBIT' THEN jumlah ELSE 0 END) as jumlah_debit"), DB::raw("SUM(IF(trd.tipe = 'KREDIT', jumlah, 0)) as jumlah_kredit"))
                                ->join('tbl_trx_akuntansi as tra', 'trd.id_trx_akun', 'tra.id_trx_akun')
                                ->whereIn('trd.id_akun', $query)
                                ->where('tanggal', $tanggal)
                                // ->groupBy('trd.tipe')
                                ->first();
            $detail[$i]['date']=$tanggal;
            $detail[$i]['detail']=$countSaldo;
        }

        $business_location = BusinessLocation::all();
        $business_locations=array();
        foreach ($business_location as $key => $value) {
            $business_locations[$value->id]=$value->name;
        }

        $data=array(
            'data'  => $detail,
            'saldo_awal'    => $saldo_before,
            'akun'  => DB::table('tbl_akun')->where('id_akun', $id)->first(),
            'business_locations' => $business_locations,
            'user'  => $user,
            'location_id'   => $location_id,
            'bulan' => json_encode(explode('-', $date)),
            'id'    => $id
        );

        return view('akuntansi.detail_gl', $data);
    }
    public function saveTransferMitra(Request $request){
        $location_id=$request->location_id;
        $bulan=$request->bulan;
        $total=$request->total;
        $data=array(
            'location_id'   => $location_id,
            'bulan'         => $bulan,
            'total'         => $total
        );
        $cek=DB::table('tbl_transfer_mitra')->where('location_id', $location_id)->where('bulan', $bulan)->first();
        if ($cek == null) {
            $query=DB::table('tbl_transfer_mitra')->insert($data);
        }else{
            $query=DB::table('tbl_transfer_mitra')->where('id', $cek->id)->update(array('total' => $total, 'dtm_upd' => date('Y-m-d H:i:s')));
        }
        session(['location_id' => $location_id, 'bulan' => $bulan]);
        return redirect('akuntansi/profit-loss');
    }
}
