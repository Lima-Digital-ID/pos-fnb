<?php

namespace App\Http\Controllers;

use App\CashRegister;
use App\User;
use Illuminate\Http\Request;

use App\Utils\CashRegisterUtil;

use DB;

class CashRegisterController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $cashRegisterUtil;

    /**
     * Constructor
     *
     * @param CashRegisterUtil $cashRegisterUtil
     * @return void
     */
    public function __construct(CashRegisterUtil $cashRegisterUtil)
    {
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->payment_types = ['cash' => 'Cash', 'card' => 'Card', 'cheque' => 'Cheque', 'bank_transfer' => 'Bank Transfer', 'other' => 'Other'];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('cash_register.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    private function cekProfitAll($id, $time){//total pendapatan dari rentang waktu yang di maksud
        $query = DB::table('cash_registers')
                        ->join('cash_register_transactions', 'cash_registers.id', '=', 'cash_register_transactions.cash_register_id')
                        ->select(DB::raw('SUM(IF(cash_register_transactions.pay_method = "cash", cash_register_transactions.amount, 0)) AS total_payment_cash'),
                                 DB::raw('SUM(IF(cash_register_transactions.pay_method != "cash", cash_register_transactions.amount, 0)) AS total_payment_not_cash'))
                        ->where('cash_registers.user_id' ,$id)
                        ->where('cash_register_transactions.amount','!=', 0)
                        ->where('cash_register_transactions.created_at','<', $time)
                        ->where('cash_registers.status' ,'close');
        $results=$query->first();
        return $results;
    }

    public function create()
    {
        //Check if there is a open register, if yes then redirect to POS screen.
        if ($this->cashRegisterUtil->countOpenedRegister() != 0) {
            return redirect()->action('SellPosController@create');
        }
        $id = request()->session()->get('user.id');
        $open_time = \Carbon::now()->toDateTimeString();
        $total_asset=$this->cekProfitAll($id, $open_time);
        $id = request()->session()->get('user.id');
        
        $pengeluaran=DB::table('tbl_pengeluaran')
                    ->join('tbl_detail_pengeluaran', 'tbl_detail_pengeluaran.id_pengeluaran', '=', 'tbl_pengeluaran.id')
                    ->select(DB::raw('SUM(IF(sumber="cash ", tbl_detail_pengeluaran.total, 0)) AS pengeluaran_cash'),
                        DB::raw('SUM(IF(sumber!="cash ", tbl_detail_pengeluaran.total, 0)) AS pengeluaran_not_cash'))
                    ->where('user_id', $id)
                    ->where('tbl_pengeluaran.tipe', '!=', 'petty')
                    ->first();

        $total['total_cash']=(($total_asset->total_payment_cash != null ? $total_asset->total_payment_cash : 0) - ($pengeluaran->pengeluaran_cash != null ? $pengeluaran->pengeluaran_cash : 0));
        $total['total_not_cash']=(($total_asset->total_payment_not_cash != null ? $total_asset->total_payment_not_cash : 0) - ($pengeluaran->pengeluaran_not_cash != null ? $pengeluaran->pengeluaran_not_cash : 0));
        
        return view('cash_register.create')->with(compact('total'));
    }
    
    private function cekProfit(){//untuk menampilkan total pendapatan untuk awal register
        $id = request()->session()->get('user.id');
        $query = DB::table('cash_registers')
                        ->join('cash_register_transactions', 'cash_registers.id', '=', 'cash_register_transactions.cash_register_id')
                        ->join('transaction_payments', 'cash_register_transactions.transaction_id', '=', 'transaction_payments.transaction_id')
                        ->select(DB::raw('SUM(IF(transaction_payments.method = "cash", transaction_payments.amount, 0)) AS total_payment_cash'),
                                 DB::raw('SUM(IF(transaction_payments.method != "cash", transaction_payments.amount, 0)) AS total_payment_not_cash'))
                        ->where('cash_registers.user_id' ,$id)
                        ->where('cash_register_transactions.amount','!=', 0)
                        ->where('cash_registers.status' ,'close');
        $results=$query->first();
        return $results;
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
            $initial_amount = 0;
            if (!empty($request->input('amount'))) {
                $initial_amount = $this->cashRegisterUtil->num_uf($request->input('amount'));
            }
            $user_id = $request->session()->get('user.id');
            $business_id = $request->session()->get('user.business_id');
            $user=User::find($user_id);

            if ($initial_amount != 0) {
                $data=array(
                    'name'      => $user->first_name.' '.$user->last_name,
                    'location_id'      => $user->location_id,
                    'amount'    => $initial_amount
                );
                // $this->journalRegistration($data);
            }
            $register = CashRegister::create([
                        'business_id' => $business_id,
                        'user_id' => $user_id,
                        'status' => 'open'
                    ]);
            $register->cash_register_transactions()->create([
                            'amount' => $initial_amount,
                            'pay_method' => 'cash',
                            'type' => 'credit',
                            'transaction_type' => 'initial'
                        ]);
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
        }

        return redirect()->action('SellPosController@create');
    }

    private function journalRegistration($data){
        $data_trx=array(
            'deskripsi'     => 'Penambahan Kas dari Register Pos '.$data['name'],
            'location_id'   => $data['location_id'],
            'tanggal'       => date('Y-m-d'),
        );
        DB::table('tbl_trx_akuntansi')->insert($data_trx);
        $id_last=DB::table('tbl_trx_akuntansi')->max('id_trx_akun');
        $data1=array(
                    'id_trx_akun'   => $id_last,
                    'id_akun'       => 20,
                    'jumlah'        => $data['amount'],
                    'tipe'          => 'DEBIT',
                    'keterangan'    => 'akun',
                );
        DB::table('tbl_trx_akuntansi_detail')->insert($data1);
        $data2=array(
                    'id_trx_akun'   => $id_last,
                    'id_akun'       => 93,
                    'jumlah'        => $data['amount'],
                    'tipe'          => 'KREDIT',
                    'keterangan'    => 'lawan',
                );
        DB::table('tbl_trx_akuntansi_detail')->insert($data2);
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\CashRegister  $cashRegister
     * @return \Illuminate\Http\Response
     */

    private function getCloseTimeBefore($id){
        $raw=CashRegister::where('user_id', $id);
        $data=$raw->get();
        return $data[$raw->count()-1];
    }

    public function show($id)
    {
        $register_details =  $this->cashRegisterUtil->getRegisterDetails($id);
        $user_id = $register_details->user_id;
        
        $open_time = $register_details['open_time'];
        // $close_time = $register_details['closed_at'];
        $close_time = $register_details['closed_at'] != null ? $register_details['closed_at'] : \Carbon::now()->toDateTimeString();
        $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time);
        $pengeluaran=$this->getPengeluaran($id);
        
        $total_asset=$this->cekProfitAll($user_id, $open_time);
        
        $pengeluaran_before=DB::table('tbl_pengeluaran')
                    ->join('cash_registers', 'cash_registers.id', '=', 'tbl_pengeluaran.cash_register_id')
                    ->join('tbl_detail_pengeluaran', 'tbl_detail_pengeluaran.id_pengeluaran', '=', 'tbl_pengeluaran.id')
                    ->select(DB::raw('SUM(IF(sumber="cash ", tbl_detail_pengeluaran.total, 0)) AS pengeluaran_cash'),
                        DB::raw('SUM(IF(sumber!="cash ", tbl_detail_pengeluaran.total, 0)) AS pengeluaran_not_cash'))
                    ->where('tbl_pengeluaran.user_id', $user_id)
                    ->where('tbl_pengeluaran.tipe', '!=', 'petty')
                    ->where('cash_registers.status', 'close')
                    ->where('tbl_detail_pengeluaran.dtm_crt', '<', $open_time)
                    ->first();
        $sisa_saldo['total_cash']=(($total_asset->total_payment_cash != null ? $total_asset->total_payment_cash : 0) - ($pengeluaran_before->pengeluaran_cash != null ? $pengeluaran_before->pengeluaran_cash : 0));
        $sisa_saldo['total_not_cash']=(($total_asset->total_payment_not_cash != null ? $total_asset->total_payment_not_cash : 0) - ($pengeluaran_before->pengeluaran_not_cash != null ? $pengeluaran_before->pengeluaran_not_cash : 0));
        
        // print_r($total_asset);exit();
        return view('cash_register.register_details')
                    ->with(compact('register_details', 'details', 'pengeluaran', 'sisa_saldo'));
    }

    private function getPengeluaran($id){
        $pengeluaran=DB::table('tbl_pengeluaran')
                    ->join('tbl_detail_pengeluaran', 'tbl_detail_pengeluaran.id_pengeluaran', '=', 'tbl_pengeluaran.id')
                    ->select(DB::raw('SUM(IF(tbl_pengeluaran.tipe="pengeluaran", IF(sumber="cash ", tbl_detail_pengeluaran.total, 0), 0)) AS pengeluaran_cash'),
                        DB::raw('SUM(IF(tbl_pengeluaran.tipe="pengeluaran", IF(sumber!="cash ", tbl_detail_pengeluaran.total, 0), 0)) AS pengeluaran_not_cash'),
                        DB::raw('SUM(IF(tbl_pengeluaran.tipe="setoran", IF(sumber="cash ", tbl_detail_pengeluaran.total, 0), 0)) AS setoran_cash'),
                        DB::raw('SUM(IF(tbl_pengeluaran.tipe="setoran", IF(sumber!="cash ", tbl_detail_pengeluaran.total, 0), 0)) AS setoran_not_cash'),
                        DB::raw('SUM(IF(tbl_pengeluaran.tipe="petty", tbl_pengeluaran.total, 0)) AS total_petty'))
                    ->where('cash_register_id', $id)
                    ->first();
        return $pengeluaran;
    }

    /**
     * Shows register details modal.
     *
     * @param  void
     * @return \Illuminate\Http\Response
     */
    public function getRegisterDetails()
    {
        $register_details =  $this->cashRegisterUtil->getRegisterDetails();

        $user_id = auth()->user()->id;
        $open_time = $register_details['open_time'];
        // $close_time = \Carbon::now()->toDateTimeString();
        $close_time = $register_details['closed_at'] != null ? $register_details['closed_at'] : \Carbon::now()->toDateTimeString();
        $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time);
        $pengeluaran=$this->getPengeluaran($register_details->id);
        
        $total_asset=$this->cekProfitAll($user_id, $close_time);

        $pengeluaran_before=DB::table('tbl_pengeluaran')
                    ->join('cash_registers', 'cash_registers.id', '=', 'tbl_pengeluaran.cash_register_id')
                    ->join('tbl_detail_pengeluaran', 'tbl_detail_pengeluaran.id_pengeluaran', '=', 'tbl_pengeluaran.id')
                    ->select(DB::raw('SUM(IF(sumber="cash ", tbl_detail_pengeluaran.total, 0)) AS pengeluaran_cash'),
                        DB::raw('SUM(IF(sumber!="cash ", tbl_detail_pengeluaran.total, 0)) AS pengeluaran_not_cash'))
                    ->where('tbl_pengeluaran.user_id', $user_id)
                    ->where('tbl_pengeluaran.tipe', '!=', 'petty')
                    ->where('cash_registers.status', 'close')
                    ->where('tbl_detail_pengeluaran.dtm_crt', '<', $close_time)
                    ->first();

        $sisa_saldo['total_cash']=(($total_asset->total_payment_cash != null ? $total_asset->total_payment_cash : 0) - ($pengeluaran_before->pengeluaran_cash != null ? $pengeluaran_before->pengeluaran_cash : 0));
        $sisa_saldo['total_not_cash']=(($total_asset->total_payment_not_cash != null ? $total_asset->total_payment_not_cash : 0) - ($pengeluaran_before->pengeluaran_not_cash != null ? $pengeluaran_before->pengeluaran_not_cash : 0));
        
        return view('cash_register.register_details')
                ->with(compact('register_details', 'details', 'pengeluaran', 'sisa_saldo'));
    }

    
    /**
     * Shows close register form.
     *
     * @param  void
     * @return \Illuminate\Http\Response
     */
    public function getCloseRegister()
    {
        $register_details =  $this->cashRegisterUtil->getRegisterDetails();

        $user_id = auth()->user()->id;
        $open_time = $register_details['open_time'];
        $close_time = \Carbon::now()->toDateTimeString();
        $details = $this->cashRegisterUtil->getRegisterTransactionDetails($user_id, $open_time, $close_time);
        $pengeluaran=$this->getPengeluaran($register_details->id);
        $total_asset=$this->cekProfitAll($user_id, $close_time);

        $pengeluaran_before=DB::table('tbl_pengeluaran')
                    ->join('cash_registers', 'cash_registers.id', '=', 'tbl_pengeluaran.cash_register_id')
                    ->join('tbl_detail_pengeluaran', 'tbl_detail_pengeluaran.id_pengeluaran', '=', 'tbl_pengeluaran.id')
                    ->select(DB::raw('SUM(IF(sumber="cash ", tbl_detail_pengeluaran.total, 0)) AS pengeluaran_cash'),
                        DB::raw('SUM(IF(sumber!="cash ", tbl_detail_pengeluaran.total, 0)) AS pengeluaran_not_cash'))
                    ->where('tbl_pengeluaran.user_id', $user_id)
                    ->where('tbl_pengeluaran.tipe', '!=', 'petty')
                    ->where('cash_registers.status', 'close')
                    ->where('tbl_detail_pengeluaran.dtm_crt', '<', $close_time)
                    ->first();

        $sisa_saldo['total_cash']=(($total_asset->total_payment_cash != null ? $total_asset->total_payment_cash : 0) - ($pengeluaran_before->pengeluaran_cash != null ? $pengeluaran_before->pengeluaran_cash : 0));
        $sisa_saldo['total_not_cash']=(($total_asset->total_payment_not_cash != null ? $total_asset->total_payment_not_cash : 0) - ($pengeluaran_before->pengeluaran_not_cash != null ? $pengeluaran_before->pengeluaran_not_cash : 0));

        return view('cash_register.close_register_modal')
                    ->with(compact('register_details', 'details', 'pengeluaran', 'sisa_saldo'));
    }

    /**
     * Closes currently opened register.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postCloseRegister(Request $request)
    {
        try {
            //Disable in demo
            if (config('app.env') == 'demo') {
                $output = ['success' => 0,
                                'msg' => 'Feature disabled in demo!!'
                            ];
                return redirect()->action('HomeController@index')->with('status', $output);
            }
            
            $input = $request->only(['closing_amount', 'total_card_slips', 'total_cheques',
                                    'closing_note']);
            $input['closing_amount'] = $this->cashRegisterUtil->num_uf($input['closing_amount']);
            $user_id = $request->session()->get('user.id');
            $input['closed_at'] = \Carbon::now()->format('Y-m-d H:i:s');
            $input['status'] = 'close';

            CashRegister::where('user_id', $user_id)
                                ->where('status', 'open')
                                ->update($input);
            $output = ['success' => 1,
                            'msg' => __('cash_register.close_success')
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            $output = ['success' => 0,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return redirect()->action('HomeController@index')->with('status', $output);
    }
}
