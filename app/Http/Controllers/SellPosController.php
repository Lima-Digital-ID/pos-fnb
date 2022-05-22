<?php
/* LICENSE: This source file belongs to The Web Fosters. The customer
 * is provided a licence to use it.
 * Permission is hereby granted, to any person obtaining the licence of this
 * software and associated documentation files (the "Software"), to use the
 * Software for personal or business purpose ONLY. The Software cannot be
 * copied, published, distribute, sublicense, and/or sell copies of the
 * Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. THE AUTHOR CAN FIX
 * ISSUES ON INTIMATION. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
 * BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH
 * THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @author     The Web Fosters <thewebfosters@gmail.com>
 * @owner      The Web Fosters <thewebfosters@gmail.com>
 * @copyright  2018 The Web Fosters
 * @license    As attached in zip file.
 */

namespace App\Http\Controllers;

use App\Account;
use App\AccountTransaction;
use App\Brands;
use App\Business;
use App\BusinessLocation;
use App\Category;
use App\Contact;
use App\CustomerGroup;
use App\Media;
use App\Product;
use App\CashRegister;
use App\SellingPriceGroup;
use App\TaxRate;
use App\Variation;
use App\Transaction;
use App\TransactionSellLine;
use App\User;
use App\Promo;

use App\Utils\BusinessUtil;
use App\Utils\CashRegisterUtil;
use App\Utils\ContactUtil;

use App\Utils\ModuleUtil;
use App\Utils\NotificationUtil;
use App\Utils\ProductUtil;
use App\Utils\TransactionUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use Yajra\DataTables\Facades\DataTables;

class SellPosController extends Controller
{
    /**
     * All Utils instance.
     *
     */
    protected $contactUtil;
    protected $productUtil;
    protected $businessUtil;
    protected $transactionUtil;
    protected $cashRegisterUtil;
    protected $moduleUtil;
    protected $notificationUtil;

    /**
     * Constructor
     *
     * @param ProductUtils $product
     * @return void
     */
    public function __construct(
        ContactUtil $contactUtil,
        ProductUtil $productUtil,
        BusinessUtil $businessUtil,
        TransactionUtil $transactionUtil,
        CashRegisterUtil $cashRegisterUtil,
        ModuleUtil $moduleUtil,
        NotificationUtil $notificationUtil
    ) {
        $this->contactUtil = $contactUtil;
        $this->productUtil = $productUtil;
        $this->businessUtil = $businessUtil;
        $this->transactionUtil = $transactionUtil;
        $this->cashRegisterUtil = $cashRegisterUtil;
        $this->moduleUtil = $moduleUtil;
        $this->notificationUtil = $notificationUtil;

        $this->dummyPaymentLine = ['method' => 'cash', 'amount' => 0, 'note' => '', 'card_transaction_number' => '', 'card_number' => '', 'card_type' => '', 'card_holder_name' => '', 'card_month' => '', 'card_year' => '', 'card_security' => '', 'cheque_number' => '', 'bank_account_number' => '',
        'is_return' => 0, 'transaction_no' => '', 'ref_code' => ''];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        $business_id = request()->session()->get('user.business_id');

        $business_locations = BusinessLocation::forDropdown($business_id, false);
        $customers = Contact::customersDropdown($business_id, false);
        $user_id = request()->session()->get('user.id');
        $user = User::where('id', $user_id)->first();
        $location_id = $user->location_id;
        foreach ($business_locations as $key => $value) {
            if ($location_id != null) {
                if ($location_id != $key) {
                    unset($business_locations[$key]);
                }
            }
        }
        return view('sale_pos.index')->with(compact('business_locations', 'customers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    private function cekSaldo($id, $location_id){
        $results = DB::select( DB::raw('SELECT COALESCE((SELECT SUM(trd.jumlah) FROM tbl_trx_akuntansi_detail trd JOIN 
            tbl_trx_akuntansi tra ON trd.id_trx_akun=tra.id_trx_akun WHERE trd.tipe="DEBIT" '.($location_id != null ? "AND tra.location_id=".$location_id : "").' AND 
            trd.id_akun=tbl_trx_akuntansi_detail.id_akun), 0) AS jumlah_debit, COALESCE((SELECT SUM(trd.jumlah) 
            FROM tbl_trx_akuntansi_detail trd JOIN tbl_trx_akuntansi tra ON trd.id_trx_akun=tra.id_trx_akun WHERE 
            trd.tipe="KREDIT" '.($location_id != null ? "AND tra.location_id=".$location_id : "").' AND trd.id_akun=tbl_trx_akuntansi_detail.id_akun), 0) AS jumlah_kredit FROM 
            `tbl_trx_akuntansi_detail` WHERE id_akun='.$id.' GROUP BY id_akun'));
        return $results;
    }

    // private function cekProfit($location_id, $type, $id){
    //     $query = DB::table('transaction_payments')
    //                     ->join('transactions', 'transactions.id', '=', 'transaction_payments.transaction_id');
    //     if ($type == 'cash') {
    //         $query->select(DB::raw('SUM(transaction_payments.amount) AS jumlah'));
    //         $query->where('method', 'cash');
    //     }else{
    //         $query->select(DB::raw('SUM(transaction_payments.amount) AS jumlah'));
    //         $query->where('method' ,'!=', 'cash');
    //     }

    //     if ($location_id != null) {
    //         $query->where('transactions.location_id', $location_id);
    //     }

    //     $query->where('transactions.created_by' ,$id);
    //     $query->where('transactions.type' , 'sell');
    //     // $query->where('transactions.transaction_date' ,'like', $date.'%');
    //     $results=$query->first();
    //     return $results;
    // }

    private function cekProfit($type, $where){
        $query = DB::table('cash_registers')
                        ->join('cash_register_transactions', 'cash_registers.id', '=', 'cash_register_transactions.cash_register_id')
                        ->join('users', 'cash_registers.user_id', '=', 'users.id');
        if ($type == 'cash') {
            $query->select(DB::raw('SUM(cash_register_transactions.amount) AS jumlah'));
            $query->where('cash_register_transactions.pay_method', 'cash');
        }else{
            $query->select(DB::raw('SUM(cash_register_transactions.amount) AS jumlah'));
            $query->where('cash_register_transactions.pay_method' ,'!=', 'cash');
        }

        $query->where($where[0],$where[1]);
        // $query->where('cash_register_transactions.pay_method' , 'sell');
        $results=$query->first();
        return $results;
    }
    private function cekPengeluaran($type, $type2, $where){
        $query = DB::table('tbl_pengeluaran')
                        ->join('tbl_detail_pengeluaran', 'tbl_pengeluaran.id', '=', 'tbl_detail_pengeluaran.id_pengeluaran')
                        ->join('users', 'tbl_pengeluaran.user_id', '=', 'users.id');
        if ($type == 'cash') {
            $query->select(DB::raw('SUM(tbl_detail_pengeluaran.total) AS jumlah'));
            $query->where('sumber', 'cash');
            // $query->where('tipe', '');
        }else if ($type == 'non tunai') {
            $query->select(DB::raw('SUM(tbl_detail_pengeluaran.total) AS jumlah'));
            $query->where('sumber', 'non tunai');
            // $query->where('tipe', '');
        }else if ($type == 'petty_pc') {
            $query->select(DB::raw('SUM(tbl_detail_pengeluaran.total) AS jumlah'));
            $query->where('sumber', 'petty');
            // $query->where('tipe', '');
        }else if ($type == 'petty') {
            $query->select(DB::raw('SUM(tbl_detail_pengeluaran.total) AS jumlah'));
            $query->where('tbl_detail_pengeluaran.tipe', 'petty');
        }else{
            $query->select(DB::raw('SUM(tbl_detail_pengeluaran.total) AS jumlah'));
            $query->where('tbl_detail_pengeluaran.tipe', 'pengeluaran');
            if ($type2 == 'petty') {
                $query->where('sumber', 'petty');
            }else if($type2 == 'cash') {
                $query->where('sumber', 'cash');
            }

        }

        $query->where($where[0],$where[1]);

        $results=$query->first();
        return $results;
    }
    public function getSaldoAll(request $request){
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        $user = User::where('id', $user_id)->first();
        $wherePengeluaran = $request->get('location_id') ?  ['tbl_pengeluaran.location_id',$request->get('location_id')] : ['tbl_pengeluaran.user_id',$user_id];
        $whereProfit = $request->get('location_id') ?  ['cash_registers.business_id',1] : ['cash_registers.user_id',$user_id]; // harusnya pake location_id
        
        $petty_cash=$this->cekPengeluaran('petty', null, $wherePengeluaran);
        $pengeluaran_pc=$this->cekPengeluaran('pengeluaran', 'petty',  $wherePengeluaran);
        $pengeluaran_cash_pc=$this->cekPengeluaran('pengeluaran', 'cash',  $wherePengeluaran);
        $setoran_pc=$this->cekPengeluaran('petty_pc', null, $wherePengeluaran);
        $saldo_cash=$this->cekProfit('cash', $whereProfit);
        $pengeluaran_cash=$this->cekPengeluaran('cash', null, $wherePengeluaran);
        $saldo_not_cash=$this->cekProfit('ovo', $whereProfit);
        $pengeluaran_non_cash=$this->cekPengeluaran('non tunai', null, $wherePengeluaran);
        
        $total_petty=$petty_cash->jumlah - ($pengeluaran_pc->jumlah + $setoran_pc->jumlah);
        // $total_pengeluaran=($pengeluaran_pc->jumlah + $pengeluaran_cash_pc->jumlah);
        $getPengeluaran=DB::table('tbl_pengeluaran')
                    ->join('cash_registers', 'cash_registers.id', '=', 'tbl_pengeluaran.cash_register_id')
                    ->join('users', 'cash_registers.user_id', '=', 'users.id')
                    ->where($wherePengeluaran[0],$wherePengeluaran)
                    ->where('cash_registers.status', 'open')
                    ->where('tbl_pengeluaran.tipe', 'pengeluaran')
                    ->select(DB::raw('SUM(tbl_pengeluaran.total) AS total'))
                    ->first();
        $total_pengeluaran=$getPengeluaran->total;

        $list_saldo=array(
            'saldo_pengeluaran'   => round($total_pengeluaran, 0),
            'saldo_petty'   => round($total_petty, 0),
            'saldo_cash'    => round(($saldo_cash->jumlah - $pengeluaran_cash->jumlah), 0),
            'saldo_cash_only'    => round(($saldo_cash->jumlah), 0),
            'saldo_pengeluaran_cash'    => round(($pengeluaran_cash->jumlah), 0),
            'saldo_not_cash'=> round($saldo_not_cash->jumlah - $pengeluaran_non_cash->jumlah, 0),
        );
        // echo "<pre>";
        // print_r($list_saldo);
        header('Content-type: application/json');
        echo json_encode($list_saldo);
    }
    private function getAkunPengeluaran(){
        $query=DB::table('tbl_akun')->whereIn('id_akun', [94,95,96,122,92,93,124])->get();
        return $query;
    }
    public function create()
    {
        if (!auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }
        
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        
        $user = User::where('id', $user_id)->first();
        $location_id = $user->location_id;
        $query = DB::table('tbl_pegawai')->select('*');
        if ($location_id != null) {
            $query->where('location_id', $location_id);
        }else{
            $query->where('location_id', 0);
        }
        $pegawai=$query->get();
        
        $business_data = BusinessLocation::all();
        $location_option=array();
        foreach ($business_data as $key => $value) {
            $location_option[$value->id]=$value->name;
        }

        //Check if subscribed or not, then check for users quota
        if (!$this->moduleUtil->isSubscribed($business_id)) {
            return $this->moduleUtil->expiredResponse(action('HomeController@index'));
        } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
            return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action('SellPosController@index'));
        }
        
        //Check if there is a open register, if no then redirect to Create Register screen.
        if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action('CashRegisterController@create');
        }

        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
        
        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);
        $payment_types = $this->productUtil->payment_types();
        $payment_type_setor = $this->productUtil->payment_types();
        unset($payment_type_setor['ovo']);
        unset($payment_types['bank_transfer']);
        unset($payment_type_setor['gopay']);
        unset($payment_type_setor['qris']);
        unset($payment_type_setor['shopee_pay']);
        $payment_lines[] = $this->dummyPaymentLine;
        
        $business_locations = BusinessLocation::forDropdown($business_id, false, true);
        
        $bl_attributes = $business_locations['attributes'];
        $business_locations = $business_locations['locations'];
        $default_location = $location_id;
        if (count($business_locations) == 1) {
            foreach ($business_locations as $id => $name) {
                $default_location = $id;
            }
        }
        //Shortcuts
        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);
        
        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id, false);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id, false);
        }
        

        //If brands, category are enabled then send else false.
        $categories = (request()->session()->get('business.enable_category') == 1) ? Category::catAndSubCategories($business_id) : false;
        $brands = (request()->session()->get('business.enable_brand') == 1) ? Brands::where('business_id', $business_id)
                    ->pluck('name', 'id')
                    ->prepend(__('lang_v1.all_brands'), 'all') : false;
        
        $change_return = $this->dummyPaymentLine;

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);
        foreach ($customer_groups as $key => $value) {
            if ($key != 2 && $key != 0) {
                unset($customer_groups[$key]);
            }
        }
        $pegawai_option=array();
        foreach ($pegawai as $key => $value) {
            $pegawai_option[$value->id_pegawai]=$value->nama_pegawai;
        }
        
        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false);
        }
        //Selling Price Group Dropdown
        $price_groups = SellingPriceGroup::forDropdown($business_id);

        //cari set booking
        $get_set_book=DB::table('tbl_set_antrian')->where('location_id', $location_id)->first();

        $promo=$this->getPromo($location_id);
        
        $promo_option=array();
        $promo_option['']='Pilih Promo';
        foreach ($promo as $key => $value) {
            $promo_option[$value->id]=$value->promo_name;
        }
        
        $json_promo=json_encode($promo);

        $akun_pengeluaran=array();
        foreach ($akun=$this->getAkunPengeluaran() as $key => $value) {
            $akun_pengeluaran[$value->id_akun]=$value->nama_akun;
        }
        $kategoriCustomer = \DB::table('tb_kategori_harga')->get();
        return view('sale_pos.create')
            ->with(compact(
                'kategoriCustomer',
                'business_details',
                'taxes',
                'payment_types',
                'walk_in_customer',
                'payment_lines',
                'business_locations',
                'bl_attributes',
                'default_location',
                'shortcuts',
                'commission_agent',
                'categories',
                'brands',
                'pos_settings',
                'change_return',
                'types',
                'customer_groups',
                'accounts',
                'location_id',
                'pegawai_option',
                'price_groups',
                'location_option',
                'payment_type_setor',
                'user_id',
                'get_set_book',
                'json_promo',
                'promo_option',
                'akun_pengeluaran'
            ));
    }
    public function getPromo($location_id){
        $date=date('Y-m-d');
        $query_promo=Promo::where('promo_status', 1)
                        ->whereDate('promo_start', '<=', $date)
                        ->whereDate('promo_end', '>=' , $date);
        if ($location_id != null) {
            $query_promo->where('location_id', $location_id);
        }else{
            $query_promo->where('location_id', 0);
        }
        $promo=$query_promo->get();

        foreach ($promo as $key => $value) {
            if ($value->promo_sk_limit != 'no') {
                $count=$this->countPromo($value->id, $value->promo_sk_limit);
                if ($count >= $value->promo_limit) {
                    unset($promo[$key]);
                }
            }
        }
        return $promo;
    }
    private function countPromo($id, $type){
        $date=date('Y-m-d');
        $query=Transaction::where('promo_id', $id)->select(DB::raw('COUNT(id) AS total'));
        if ($type == 'day') {
            $query->where('transaction_date', 'like', $date.'%');
        }
        $count=$query->first();
        return $count->total;
    }
    private function journalTransaction($data){
        $data_trx=array(
            'deskripsi'     => 'Pendapatan Transaksi '.$data['invoice_no'],
            'invoice_no'    => $data['invoice_no'],
            'transaction_id'    => $data['trx_id'],
            'location_id'   => $data['location_id'],
            'tanggal'       => date('Y-m-d'),
        );
        DB::table('tbl_trx_akuntansi')->insert($data_trx);
        $id_last=DB::table('tbl_trx_akuntansi')->max('id_trx_akun');
        $data1=array(
                    'id_trx_akun'   => $id_last,
                    'id_akun'       => 20,
                    'jumlah'        => $data['total_kas'],
                    'tipe'          => 'DEBIT',
                    'keterangan'    => 'akun',
                );
        DB::table('tbl_trx_akuntansi_detail')->insert($data1);
        if ($data['hpp'] != 0) {
            $data1=array(
                    'id_trx_akun'   => $id_last,
                    'id_akun'       => 91,
                    'jumlah'        => $data['hpp'],
                    'tipe'          => 'KREDIT',
                    'keterangan'    => 'akun',
                );
            DB::table('tbl_trx_akuntansi_detail')->insert($data1);  
            $data2=array(
                        'id_trx_akun'   => $id_last,
                        'id_akun'       => 65,
                        'jumlah'        => $data['hpp'],
                        'tipe'          => 'DEBIT',
                        'keterangan'    => 'lawan',
                    );
            DB::table('tbl_trx_akuntansi_detail')->insert($data2);
        }
        if ($data['komisi_pegawai'] != 0) {
            $data2=array(
                        'id_trx_akun'   => $id_last,
                        'id_akun'       => 87,
                        'jumlah'        => $data['komisi_pegawai'],
                        'tipe'          => 'DEBIT',
                        'keterangan'    => 'lawan',
                    );
            DB::table('tbl_trx_akuntansi_detail')->insert($data2);
        }
        if ($data['total_jasa'] != 0) {
            $data2=array(
                        'id_trx_akun'   => $id_last,
                        'id_akun'       => 39,
                        'jumlah'        => $data['total_jasa'],
                        'tipe'          => 'KREDIT',
                        'keterangan'    => 'lawan',
                    );
            DB::table('tbl_trx_akuntansi_detail')->insert($data2);
        }
        if ($data['total_diskon'] != 0) {
            $data3=array(
                        'id_trx_akun'   => $id_last,
                        'id_akun'       => 90,
                        'jumlah'        => $data['total_diskon'],
                        'tipe'          => 'DEBIT',
                        'keterangan'    => 'lawan',
                    );
            DB::table('tbl_trx_akuntansi_detail')->insert($data3);
        }
        if ($data['total_pajak'] != 0) {
            $data4=array(
                        'id_trx_akun'   => $id_last,
                        'id_akun'       => 89,
                        'jumlah'        => $data['total_pajak'],
                        'tipe'          => 'KREDIT',
                        'keterangan'    => 'lawan',
                    );
            DB::table('tbl_trx_akuntansi_detail')->insert($data4);
        }
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('sell.create') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        $is_direct_sale = false;
        if (!empty($request->input('is_direct_sale'))) {
            $is_direct_sale = true;
        }

        //Check if there is a open register, if no then redirect to Create Register screen.
        if (!$is_direct_sale && $this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action('CashRegisterController@create');
        }

        try {
            $input = $request->except('_token');
            //Check Customer credit limit

            $is_credit_limit_exeeded = $this->transactionUtil->isCustomerCreditLimitExeeded($input);

            if ($is_credit_limit_exeeded !== false) {
                $credit_limit_amount = $this->transactionUtil->num_f($is_credit_limit_exeeded, true);
                $output = ['success' => 0,
                            'msg' => __('lang_v1.cutomer_credit_limit_exeeded', ['credit_limit' => $credit_limit_amount])
                        ];
                if (!$is_direct_sale) {
                    return $output;
                } else {
                    return redirect()
                        ->action('SellController@index')
                        ->with('status', $output);
                }
            }

            $input['is_quotation'] = 0;
            //status is send as quotation from Add sales screen.
            if ($input['status'] == 'quotation') {
                $input['status'] = 'draft';
                $input['is_quotation'] = 1;
            }
            
            //mengecek item paket atau tidak
             //jika berupa item paket, detail stok item yang ada dalam paket akan berkurang
            foreach ($input['products'] as $key => $value) {
                $product_temp=$value;

                $getBahanProduk = DB::table('tb_bahan_product')->where('product_id',$product_temp['product_id'])->get();

                foreach ($getBahanProduk as $i => $v) {
                    $decrase_stok = $v->kebutuhan * $product_temp['quantity'];
                    DB::statement("update tb_stok_bahan set stok = stok - $decrase_stok where id_bahan = '".$v->id_bahan."' and location_id = '".$request->input('location_id')."' ");
                }


                $cekProduct=Product::find($product_temp['product_id']);
                if ($cekProduct->is_paket == 1) {
                    $paket_detail=DB::table('product_pakets')
                                    ->select('variations.id AS variation_id', 'products.*', 'product_pakets.*', 'variation_location_details.qty_available')
                                    ->join('products', 'products.id', '=', 'product_pakets.product_id')
                                    ->join('variations', 'products.id', '=', 'variations.product_id')
                                    ->join('variation_location_details', 'products.id', '=', 'variation_location_details.product_id')
                                    ->where('product_item', $product_temp['product_id'])
                                    ->where('variation_location_details.location_id', $request->input('location_id'))
                                    ->get();
                    foreach ($paket_detail as $key => $value) {
                        if ($value->enable_stock != 0) {
                            if ($product_temp['quantity'] > $value->qty_available) {
                                $output = ['success' => 0,
                                            'msg' => 'Stok '.$value->name.' melebihi permintaan'
                                        ];
                                if (!$is_direct_sale) {
                                    return $output;
                                } else {
                                    return redirect()
                                        ->action('SellController@index')
                                        ->with('status', $output);
                                }
                            }
                        }
                        $input_detail=array(
                            'unit_price' => 0,
                            'line_discount_type' => $product_temp['line_discount_type'],
                            'line_discount_amount' => 0,
                            'item_tax' => 0,
                            'tax_id' => null,
                            'sell_line_note' => null,
                            'product_id' => $value->product_id,
                            'variation_id' => $value->variation_id,
                            'enable_stock' => $value->enable_stock,
                            'quantity' => $product_temp['quantity'],
                            'product_unit_id' => $value->unit_id,
                            'is_item_paket' => 1,
                            'base_unit_multiplier' => $product_temp['base_unit_multiplier'],
                            'unit_price_inc_tax' => 0,
                            'pegawai' => $product_temp['pegawai']
                        );
                        array_push($input['products'], $input_detail);
                    }
                }
            }
            if (!empty($input['products'])) {
                $business_id = $request->session()->get('user.business_id');

                //Check if subscribed or not, then check for users quota
                if (!$this->moduleUtil->isSubscribed($business_id)) {
                    return $this->moduleUtil->expiredResponse();
                } elseif (!$this->moduleUtil->isQuotaAvailable('invoices', $business_id)) {
                    return $this->moduleUtil->quotaExpiredResponse('invoices', $business_id, action('SellPosController@index'));
                }

                $user_id = $request->session()->get('user.id');
                $commsn_agnt_setting = $request->session()->get('business.sales_cmsn_agnt');

                $discount = ['discount_type' => $input['discount_type'],
                                'discount_amount' => $input['discount_amount']
                            ];
                $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'], $discount);

                DB::beginTransaction();

                if (empty($request->input('transaction_date'))) {
                    $input['transaction_date'] =  \Carbon::now();
                } else {
                    $input['transaction_date'] = $this->productUtil->uf_date($request->input('transaction_date'), true);
                }
                if ($is_direct_sale) {
                    $input['is_direct_sale'] = 1;
                }

                $input['commission_agent'] = !empty($request->input('commission_agent')) ? $request->input('commission_agent') : null;
                if ($commsn_agnt_setting == 'logged_in_user') {
                // print_r($input);
                // exit();
                    $input['commission_agent'] = $user_id;
                }

                if (isset($input['exchange_rate']) && $this->transactionUtil->num_uf($input['exchange_rate']) == 0) {
                    $input['exchange_rate'] = 1;
                }

                //Customer group details
                $contact_id = $request->get('contact_id', null);
                $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
                $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;

                //set selling price group id
                if ($request->has('price_group')) {
                    $input['selling_price_group_id'] = $request->input('price_group');
                }

                $input['is_suspend'] = isset($input['is_suspend']) && 1 == $input['is_suspend']  ? 1 : 0;
                if ($input['is_suspend']) {
                    $input['sale_note'] = !empty($input['additional_notes']) ? $input['additional_notes'] : null;
                }

                //Generate reference number
                if (!empty($input['is_recurring'])) {
                    //Update reference count
                    $ref_count = $this->transactionUtil->setAndGetReferenceCount('subscription');
                    $input['subscription_no'] = $this->transactionUtil->generateReferenceNumber('subscription', $ref_count);
                }
                // print_r($input['payment']);exit();
                $transaction = $this->transactionUtil->createSellTransaction($business_id, $input, $invoice_total, $user_id);
                $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id']);
                $total_discount=$transaction->discount_amount;
                if ($transaction->discount_type == 'percentage') {
                    $total_discount=$transaction->total_before_tax * ($transaction->discount_amount / 100);
                }

                $hpp=$sum_komisi=0;
                $kasir=DB::table('tbl_setting_gaji')
                            ->join('tbl_pegawai', 'tbl_pegawai.id_pegawai', '=', 'tbl_setting_gaji.id_pegawai')
                            ->join('users', 'tbl_pegawai.id_pegawai', '=', 'users.id_pegawai')
                            ->where('users.id', $transaction->created_by)
                            ->first();
                                
                foreach ($input['products'] as $key => $value) {
                    $komisi=Product::where('id', $value['product_id'])->first();
                    if ($komisi->is_paket != 1) {//jika produk bukan paket, komisi kasir tidak bertambah
                        
                        if ($value['is_item_paket'] == 0) {//jika produk bukan item dalam paket, komisi kasir tidak bertambah
                            // $sum_komisi+=(($kasir != null ? $kasir->kom_trx : 0) * $value['quantity']);
                            if ($komisi->enable_stock == 1) {
                                $price=Variation::where('product_id', $value['product_id'])->first();
                                // $hpp+=($price->default_purchase_price * $value['quantity']);
                                // $sum_komisi+=(($komisi != null ? ($komisi->commission != null ? $komisi->commission : 0) : 0) * $value['quantity']);
                            }
                            // else{
                            //     $detail_gaji=DB::table('tbl_setting_gaji')->where('id_pegawai', $value['pegawai'])->first();
                            //     if ($detail_gaji != null) {
                            //         if ($detail_gaji->tipe_gaji == 2) {
                            //             $sum_komisi+=(($komisi != null ? ($komisi->commission != null ? $komisi->commission : 0) : 0) * $value['quantity']);
                            //         }else if ($detail_gaji->tipe_gaji == 3) {
                            //             if(strpos($komisi->name, 'Potong') !== false){
                            //                 $sum_komisi+=($detail_gaji->kom_min * $value['quantity']);
                            //             }else{
                            //                 $sum_komisi+=(($komisi != null ? ($komisi->commission != null ? $komisi->commission : 0) : 0) * $value['quantity']);
                            //             }
                            //         }
                            //     }
                            // }
                        }else{
                            if ($komisi->enable_stock == 1) {
                                $price=Variation::where('product_id', $value['product_id'])->first();
                                // $hpp+=($price->default_purchase_price * $value['quantity']);
                                // $sum_komisi+=(($komisi != null ? ($komisi->commission != null ? $komisi->commission : 0) : 0) * $value['quantity']);
                            }
                        }
                    }else{
                        //jika produk paket tambah komisi untuk kasir yang menghitung jumlah dari tiap item dalam paket itu
                        $total_item_paket=DB::table('products')->join('product_pakets', 'products.id', '=', 'product_pakets.product_item')->where('products.id', $komisi->id)->count();
                        // $sum_komisi+=(($kasir != null ? $kasir->kom_trx : 0) * ($value['quantity'] * $total_item_paket));
                        // $sum_komisi+=(($komisi != null ? ($komisi->commission != null ? $komisi->commission : 0) : 0) * $value['quantity']);
                    }

                    $bahanProduk = DB::table('tb_bahan_product as bp')->select('bp.*','b.harga_bahan')->join('tb_bahan as b','bp.id_bahan','b.id_bahan')->where('bp.product_id',$value['product_id'])->get();
                    foreach ($bahanProduk as $index => $v) {

                        $hpp += ($v->kebutuhan * $v->harga_bahan);

                        $kartuStok = [
                            'id_bahan' => $v->id_bahan,
                            'jml_stok' => $v->kebutuhan,
                            'tipe' => 'pos',
                            'no_transaksi' => $transaction->invoice_no,
                            'tanggal' => date('Y-m-d'),
                        ];
                        \DB::table('tb_kartu_stok')->insert($kartuStok);
                    }
        
                }
                
                $data_jurnal=array(
                    'location_id'        => $request->input('location_id'),
                    'invoice_no'        => $transaction->invoice_no,
                    'trx_id'        => $transaction->id,
                    'total_kas'         => $transaction->final_total - $sum_komisi,
                    'total_jasa'        => $transaction->total_before_tax,
                    'total_pajak'       => $transaction->tax_amount,
                    'total_kirim'       => $transaction->shipping_charges,
                    'total_diskon'      => $total_discount,
                    'hpp'               => $hpp,
                    'komisi_pegawai'    => $sum_komisi
                );
                
                $this->journalTransaction($data_jurnal);
                
                if (!$is_direct_sale) {
                    //Add change return
                    $change_return = $this->dummyPaymentLine;
                    $change_return['amount'] = $input['change_return'];
                    $change_return['is_return'] = 1;
                    $input['payment'][] = $change_return;
                }
                
                if (!$transaction->is_suspend && !empty($input['payment'])) {
                    $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);
                }

                $update_transaction = false;
                if ($this->transactionUtil->isModuleEnabled('tables')) {
                    $transaction->res_table_id = request()->get('res_table_id');
                    $update_transaction = true;
                }
                if ($this->transactionUtil->isModuleEnabled('service_staff')) {
                    $transaction->res_waiter_id = request()->get('res_waiter_id');
                    $update_transaction = true;
                }
                if ($update_transaction) {
                    $transaction->save();
                }
                
                //Check for final and do some processing.
                if ($input['status'] == 'final') {
                    //update product stock
                    foreach ($input['products'] as $product) {
                        if ($product['enable_stock']) {
                            $decrease_qty = $this->productUtil->num_uf($product['quantity']);
                            if (!empty($product['base_unit_multiplier'])) {
                                $decrease_qty = $decrease_qty * $product['base_unit_multiplier'];
                            }
                            
                            $this->productUtil->decreaseProductQuantity(
                                $product['product_id'],
                                $product['variation_id'],
                                $input['location_id'],
                                $decrease_qty
                            );
                        }
                    }

                    //Add payments to Cash Register
                    if (!$is_direct_sale && !$transaction->is_suspend && !empty($input['payment'])) {
                        $this->cashRegisterUtil->addSellPayments($transaction, $input['payment']);
                    }
                    
                    //Update payment status
                    $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

                    //Allocate the quantity from purchase and add mapping of
                    //purchase & sell lines in
                    //transaction_sell_lines_purchase_lines table
                    $business_details = $this->businessUtil->getDetails($business_id);
                    $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

                    $business = ['id' => $business_id,
                                    'accounting_method' => $request->session()->get('business.accounting_method'),
                                    'location_id' => $input['location_id'],
                                    'pos_settings' => $pos_settings
                                ];
                    // $this->transactionUtil->mapPurchaseSell($business, $transaction->sell_lines, 'purchase');

                    //Auto send notification
                    $this->notificationUtil->autoSendNotification($business_id, 'new_sale', $transaction, $transaction->contact);
                }

                //Set Module fields
                if (!empty($input['has_module_data'])) {
                    $this->moduleUtil->getModuleData('after_sale_saved', ['transaction' => $transaction, 'input' => $input]);
                }
                Media::uploadMedia($business_id, $transaction, $request, 'documents');
                
                DB::commit();
                
                $msg = '';
                $receipt = '';
                if ($input['status'] == 'draft' && $input['is_quotation'] == 0) {
                    $msg = trans("sale.draft_added");
                } elseif ($input['status'] == 'draft' && $input['is_quotation'] == 1) {
                    $msg = trans("lang_v1.quotation_added");
                    if (!$is_direct_sale) {
                        $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id);
                    } else {
                        $receipt = '';
                    }
                } elseif ($input['status'] == 'final') {
                    if (empty($input['sub_type'])) {
                        $msg = trans("sale.pos_sale_added");
                        if (!$is_direct_sale && !$transaction->is_suspend) {
                            $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id);
                        } else {
                            $receipt = '';
                        }
                    } else {
                        $msg = trans("sale.pos_sale_added");
                        $receipt = '';
                    }
                }
                                
                $output = ['success' => 1, 'msg' => $msg, 'receipt' => $receipt ];
            } else {
                $output = ['success' => 0,
                            'msg' => trans("messages.something_went_wrong")
                        ];
            }
        } catch (\Exception $e) {
            DB::rollBack();

            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            $msg = trans("messages.something_went_wrong");
            
            if (get_class($e) == \App\Exceptions\PurchaseSellMismatch::class) {
                $msg = $e->getMessage();
            }

            $output = ['success' => 0,
                            'msg' => $msg
                        ];
        }

        if (!$is_direct_sale) {
            return $output;
        } else {
            if ($input['status'] == 'draft') {
                if (isset($input['is_quotation']) && $input['is_quotation'] == 1) {
                    return redirect()
                        ->action('SellController@getQuotations')
                        ->with('status', $output);
                } else {
                    return redirect()
                        ->action('SellController@getDrafts')
                        ->with('status', $output);
                }
            } else {
                if (!empty($input['sub_type']) && $input['sub_type'] == 'repair') {
                    $redirect_url = $input['print_label'] == 1 ? action('\Modules\Repair\Http\Controllers\RepairController@printLabel', [$transaction->id]) : action('\Modules\Repair\Http\Controllers\RepairController@index');
                    return redirect($redirect_url)
                        ->with('status', $output);
                }
                return redirect()
                    ->action('SellController@index')
                    ->with('status', $output);
            }
        }
    }

    /**
     * Returns the content for the receipt
     *
     * @param  int  $business_id
     * @param  int  $location_id
     * @param  int  $transaction_id
     * @param string $printer_type = null
     *
     * @return array
     */
    private function receiptContent(
        $business_id,
        $location_id,
        $transaction_id,
        $printer_type = null,
        $is_package_slip = false,
        $from_pos_screen = true
    ) {
        $output = ['is_enabled' => false,
                    'print_type' => 'browser',
                    'html_content' => null,
                    'printer_config' => [],
                    'data' => []
                ];


        $business_details = $this->businessUtil->getDetails($business_id);
        $location_details = BusinessLocation::find($location_id);
        if ($from_pos_screen && $location_details->print_receipt_on_invoice != 1) {
            return $output;
        }
        //Check if printing of invoice is enabled or not.
        //If enabled, get print type.
        $output['is_enabled'] = true;

        $invoice_layout = $this->businessUtil->invoiceLayout($business_id, $location_id, $location_details->invoice_layout_id);
        
        //Check if printer setting is provided.
        $receipt_printer_type = is_null($printer_type) ? $location_details->receipt_printer_type : $printer_type;

        $receipt_details = $this->transactionUtil->getReceiptDetails($transaction_id, $location_id, $invoice_layout, $business_details, $location_details, $receipt_printer_type);        
        // print_r($receipt_details);exit();
        $currency_details = [
            'symbol' => $business_details->currency_symbol,
            'thousand_separator' => $business_details->thousand_separator,
            'decimal_separator' => $business_details->decimal_separator,
        ];
        $receipt_details->currency = $currency_details;
        
        if ($is_package_slip) {
            $output['html_content'] = view('sale_pos.receipts.packing_slip', compact('receipt_details'))->render();
            return $output;
        }
        //If print type browser - return the content, printer - return printer config data, and invoice format config
        if ($receipt_printer_type == 'printer') {
            $output['print_type'] = 'printer';
            $output['printer_config'] = $this->businessUtil->printerConfig($business_id, $location_details->printer_id);
            $output['data'] = $receipt_details;
        } else {
            $layout = !empty($receipt_details->design) ? 'sale_pos.receipts.' . $receipt_details->design : 'sale_pos.receipts.classic';

            $output['html_content'] = view($layout, compact('receipt_details'))->render();
        }
        
        return $output;
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
        if (!auth()->user()->can('sell.update')) {
            abort(403, 'Unauthorized action.');
        }

        //Check if the transaction can be edited or not.
        $edit_days = request()->session()->get('business.transaction_edit_days');
        if (!$this->transactionUtil->canBeEdited($id, $edit_days)) {
            return back()
                ->with('status', ['success' => 0,
                    'msg' => __('messages.transaction_edit_not_allowed', ['days' => $edit_days])]);
        }

        //Check if there is a open register, if no then redirect to Create Register screen.
        if ($this->cashRegisterUtil->countOpenedRegister() == 0) {
            return redirect()->action('CashRegisterController@create');
        }
        
        //Check if return exist then not allowed
        if ($this->transactionUtil->isReturnExist($id)) {
            return back()->with('status', ['success' => 0,
                    'msg' => __('lang_v1.return_exist')]);
        }

        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        $user = User::where('id', $user_id)->first();
        $location_id = $user->location_id;
        $query = DB::table('tbl_pegawai')->select('*')->where('id_jabatan', 1);
        if ($location_id != null) {
            $query->where('location_id', $location_id);
        }
        $pegawai=$query->get();
        $pegawai_option=array();
        foreach ($pegawai as $key => $value) {
            $pegawai_option[$value->id_pegawai]=$value->nama_pegawai;
        }
        $walk_in_customer = $this->contactUtil->getWalkInCustomer($business_id);
        
        $business_details = $this->businessUtil->getDetails($business_id);
        $taxes = TaxRate::forBusinessDropdown($business_id, true, true);
        $payment_types = $this->productUtil->payment_types();

        $transaction = Transaction::where('business_id', $business_id)
                            ->where('type', 'sell')
                            ->findorfail($id);

        $location_id = $transaction->location_id;
        $location_printer_type = BusinessLocation::find($location_id)->receipt_printer_type;

        $sell_details = TransactionSellLine::
                        join(
                            'products AS p',
                            'transaction_sell_lines.product_id',
                            '=',
                            'p.id'
                        )
                        ->join(
                            'variations AS variations',
                            'transaction_sell_lines.variation_id',
                            '=',
                            'variations.id'
                        )
                        ->join(
                            'product_variations AS pv',
                            'variations.product_variation_id',
                            '=',
                            'pv.id'
                        )
                        ->leftjoin('variation_location_details AS vld', function ($join) use ($location_id) {
                            $join->on('variations.id', '=', 'vld.variation_id')
                                ->where('vld.location_id', '=', $location_id);
                        })
                        ->leftjoin('units', 'units.id', '=', 'p.unit_id')
                        ->where('transaction_sell_lines.transaction_id', $id)
                        ->select(
                            DB::raw("IF(pv.is_dummy = 0, CONCAT(p.name, ' (', pv.name, ':',variations.name, ')'), p.name) AS product_name"),
                            'p.id as product_id',
                            'p.enable_stock',
                            'p.name as product_actual_name',
                            'pv.name as product_variation_name',
                            'pv.is_dummy as is_dummy',
                            'variations.name as variation_name',
                            'variations.sub_sku',
                            'p.barcode_type',
                            'p.enable_sr_no',
                            'variations.id as variation_id',
                            'units.short_name as unit',
                            'units.allow_decimal as unit_allow_decimal',
                            'transaction_sell_lines.tax_id as tax_id',
                            'transaction_sell_lines.item_tax as item_tax',
                            'transaction_sell_lines.unit_price as default_sell_price',
                            'transaction_sell_lines.unit_price_before_discount as unit_price_before_discount',
                            'transaction_sell_lines.unit_price_inc_tax as sell_price_inc_tax',
                            'transaction_sell_lines.id as transaction_sell_lines_id',
                            'transaction_sell_lines.quantity as quantity_ordered',
                            'transaction_sell_lines.sell_line_note as sell_line_note',
                            'transaction_sell_lines.parent_sell_line_id',
                            'transaction_sell_lines.lot_no_line_id',
                            'transaction_sell_lines.line_discount_type',
                            'transaction_sell_lines.line_discount_amount',
                            'transaction_sell_lines.res_service_staff_id',
                            'transaction_sell_lines.id_pegawai',
                            'units.id as unit_id',
                            'transaction_sell_lines.sub_unit_id',
                            DB::raw('vld.qty_available + transaction_sell_lines.quantity AS qty_available')
                        )
                        ->get();
        if (!empty($sell_details)) {
            foreach ($sell_details as $key => $value) {
                //If modifier sell line then unset
                if (!empty($sell_details[$key]->parent_sell_line_id)) {
                    unset($sell_details[$key]);
                } else {
                    if ($transaction->status != 'final') {
                        $actual_qty_avlbl = $value->qty_available - $value->quantity_ordered;
                        $sell_details[$key]->qty_available = $actual_qty_avlbl;
                        $value->qty_available = $actual_qty_avlbl;
                    }

                    $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available, false, null, true);

                    //Add available lot numbers for dropdown to sell lines
                    $lot_numbers = [];
                    if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                        $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($value->variation_id, $business_id, $location_id);
                        foreach ($lot_number_obj as $lot_number) {
                            //If lot number is selected added ordered quantity to lot quantity available
                            if ($value->lot_no_line_id == $lot_number->purchase_line_id) {
                                $lot_number->qty_available += $value->quantity_ordered;
                            }

                            $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                            $lot_numbers[] = $lot_number;
                        }
                    }
                    $sell_details[$key]->lot_numbers = $lot_numbers;
                    
                    if (!empty($value->sub_unit_id)) {
                        $value = $this->productUtil->changeSellLineUnit($business_id, $value);
                        $sell_details[$key] = $value;
                    }

                    $sell_details[$key]->formatted_qty_available = $this->productUtil->num_f($value->qty_available, false, null, true);

                    if ($this->transactionUtil->isModuleEnabled('modifiers')) {
                        //Add modifier details to sel line details
                        $sell_line_modifiers = TransactionSellLine::where('parent_sell_line_id', $sell_details[$key]->transaction_sell_lines_id)->get();
                        $modifiers_ids = [];
                        if (count($sell_line_modifiers) > 0) {
                            $sell_details[$key]->modifiers = $sell_line_modifiers;
                            foreach ($sell_line_modifiers as $sell_line_modifier) {
                                $modifiers_ids[] = $sell_line_modifier->variation_id;
                            }
                        }
                        $sell_details[$key]->modifiers_ids = $modifiers_ids;

                        //add product modifier sets for edit
                        $this_product = Product::find($sell_details[$key]->product_id);
                        if (count($this_product->modifier_sets) > 0) {
                            $sell_details[$key]->product_ms = $this_product->modifier_sets;
                        }
                    }
                }
            }
        }

        $payment_lines = $this->transactionUtil->getPaymentDetails($id);
        //If no payment lines found then add dummy payment line.
        if (empty($payment_lines)) {
            $payment_lines[] = $this->dummyPaymentLine;
        }

        $shortcuts = json_decode($business_details->keyboard_shortcuts, true);
        $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

        $commsn_agnt_setting = $business_details->sales_cmsn_agnt;
        $commission_agent = [];
        if ($commsn_agnt_setting == 'user') {
            $commission_agent = User::forDropdown($business_id, false);
        } elseif ($commsn_agnt_setting == 'cmsn_agnt') {
            $commission_agent = User::saleCommissionAgentsDropdown($business_id, false);
        }

        //If brands, category are enabled then send else false.
        $categories = (request()->session()->get('business.enable_category') == 1) ? Category::catAndSubCategories($business_id) : false;
        $brands = (request()->session()->get('business.enable_brand') == 1) ? Brands::where('business_id', $business_id)
                    ->pluck('name', 'id')
                    ->prepend(__('lang_v1.all_brands'), 'all') : false;

        $change_return = $this->dummyPaymentLine;

        $types = [];
        if (auth()->user()->can('supplier.create')) {
            $types['supplier'] = __('report.supplier');
        }
        if (auth()->user()->can('customer.create')) {
            $types['customer'] = __('report.customer');
        }
        if (auth()->user()->can('supplier.create') && auth()->user()->can('customer.create')) {
            $types['both'] = __('lang_v1.both_supplier_customer');
        }
        $customer_groups = CustomerGroup::forDropdown($business_id);

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false);
        }
        //Selling Price Group Dropdown
        $price_groups = SellingPriceGroup::forDropdown($business_id);

        $waiters = null;
        if ($this->productUtil->isModuleEnabled('service_staff') && !empty($pos_settings['inline_service_staff'])) {
            $waiters_enabled = true;
            $waiters = $this->productUtil->serviceStaffDropdown($business_id);
        }
        
        return view('sale_pos.edit')
            ->with(compact('business_details', 'taxes', 'payment_types', 'walk_in_customer', 'sell_details', 'transaction', 'payment_lines', 'location_printer_type', 'shortcuts', 'commission_agent', 'categories', 'pos_settings', 'change_return', 'types', 'customer_groups', 'brands', 'accounts', 'price_groups', 'waiters', 'pegawai_option'));
    }

    /**
     * Update the specified resource in storage.
     * TODO: Add edit log.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!auth()->user()->can('sell.update') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }
        
        try {
            $input = $request->except('_token');
            // print_r($input);exit();
            //status is send as quotation from edit sales screen.
            $input['is_quotation'] = 0;
            if ($input['status'] == 'quotation') {
                $input['status'] = 'draft';
                $input['is_quotation'] = 1;
            }

            $is_direct_sale = false;
            if (!empty($input['products'])) {
                //Get transaction value before updating.
                $transaction_before = Transaction::find($id);
                $status_before =  $transaction_before->status;

                if ($transaction_before->is_direct_sale == 1) {
                    $is_direct_sale = true;
                }

                //Check Customer credit limit
                $is_credit_limit_exeeded = $this->transactionUtil->isCustomerCreditLimitExeeded($input, $id);

                if ($is_credit_limit_exeeded !== false) {
                    $credit_limit_amount = $this->transactionUtil->num_f($is_credit_limit_exeeded, true);
                    $output = ['success' => 0,
                                'msg' => __('lang_v1.cutomer_credit_limit_exeeded', ['credit_limit' => $credit_limit_amount])
                            ];
                    if (!$is_direct_sale) {
                        return $output;
                    } else {
                        return redirect()
                            ->action('SellController@index')
                            ->with('status', $output);
                    }
                }

                //Check if there is a open register, if no then redirect to Create Register screen.
                if (!$is_direct_sale && $this->cashRegisterUtil->countOpenedRegister() == 0) {
                    return redirect()->action('CashRegisterController@create');
                }

                $business_id = $request->session()->get('user.business_id');
                $user_id = $request->session()->get('user.id');
                $commsn_agnt_setting = $request->session()->get('business.sales_cmsn_agnt');

                $discount = ['discount_type' => $input['discount_type'],
                                'discount_amount' => $input['discount_amount']
                            ];
                $invoice_total = $this->productUtil->calculateInvoiceTotal($input['products'], $input['tax_rate_id'], $discount);

                if (!empty($request->input('transaction_date'))) {
                    $input['transaction_date'] = $this->productUtil->uf_date($request->input('transaction_date'), true);
                }

                $input['commission_agent'] = !empty($request->input('commission_agent')) ? $request->input('commission_agent') : null;
                if ($commsn_agnt_setting == 'logged_in_user') {
                    $input['commission_agent'] = $user_id;
                }

                if (isset($input['exchange_rate']) && $this->transactionUtil->num_uf($input['exchange_rate']) == 0) {
                    $input['exchange_rate'] = 1;
                }

                //Customer group details
                $contact_id = $request->get('contact_id', null);
                $cg = $this->contactUtil->getCustomerGroup($business_id, $contact_id);
                $input['customer_group_id'] = (empty($cg) || empty($cg->id)) ? null : $cg->id;
                
                //set selling price group id
                if ($request->has('price_group')) {
                    $input['selling_price_group_id'] = $request->input('price_group');
                }

                $input['is_suspend'] = isset($input['is_suspend']) && 1 == $input['is_suspend']  ? 1 : 0;
                if ($input['is_suspend']) {
                    $input['sale_note'] = !empty($input['additional_notes']) ? $input['additional_notes'] : null;
                }

                //Begin transaction
                DB::beginTransaction();

                $transaction = $this->transactionUtil->updateSellTransaction($id, $business_id, $input, $invoice_total, $user_id);

                //Update Sell lines
                $deleted_lines = $this->transactionUtil->createOrUpdateSellLines($transaction, $input['products'], $input['location_id'], true, $status_before);

                //Update update lines
                if (!$is_direct_sale && !$transaction->is_suspend) {
                    //Add change return
                    $change_return = $this->dummyPaymentLine;
                    $change_return['amount'] = $input['change_return'];
                    $change_return['is_return'] = 1;
                    if (!empty($input['change_return_id'])) {
                        $change_return['id'] = $input['change_return_id'];
                    }
                    $input['payment'][] = $change_return;

                    $this->transactionUtil->createOrUpdatePaymentLines($transaction, $input['payment']);

                    //Update cash register
                    $this->cashRegisterUtil->updateSellPayments($status_before, $transaction, $input['payment']);
                }

                //Update payment status
                $this->transactionUtil->updatePaymentStatus($transaction->id, $transaction->final_total);

                //Update product stock
                $this->productUtil->adjustProductStockForInvoice($status_before, $transaction, $input);

                //Allocate the quantity from purchase and add mapping of
                //purchase & sell lines in
                //transaction_sell_lines_purchase_lines table
                $business = ['id' => $business_id,
                                'accounting_method' => $request->session()->get('business.accounting_method'),
                                'location_id' => $input['location_id']
                            ];
                $this->transactionUtil->adjustMappingPurchaseSell($status_before, $transaction, $business, $deleted_lines);
                
                if ($this->transactionUtil->isModuleEnabled('tables')) {
                    $transaction->res_table_id = request()->get('res_table_id');
                    $transaction->save();
                }
                if ($this->transactionUtil->isModuleEnabled('service_staff')) {
                    $transaction->res_waiter_id = request()->get('res_waiter_id');
                    $transaction->save();
                }
                $log_properties = [];
                if (isset($input['repair_completed_on'])) {
                    $completed_on = !empty($input['repair_completed_on']) ? $this->transactionUtil->uf_date($input['repair_completed_on'], true) : null;
                    if ($transaction->repair_completed_on != $completed_on) {
                        $log_properties['completed_on_from'] = $transaction->repair_completed_on;
                        $log_properties['completed_on_to'] = $completed_on;
                    }
                }

                //Set Module fields
                if (!empty($input['has_module_data'])) {
                    $this->moduleUtil->getModuleData('after_sale_saved', ['transaction' => $transaction, 'input' => $input]);
                }

                if (!empty($input['update_note'])) {
                    $log_properties['update_note'] = $input['update_note'];
                }

                Media::uploadMedia($business_id, $transaction, $request, 'documents');

                activity()
                ->performedOn($transaction)
                ->withProperties($log_properties)
                ->log('edited');

                DB::commit();
                    
                $msg = '';
                $receipt = '';

                if ($input['status'] == 'draft' && $input['is_quotation'] == 0) {
                    $msg = trans("sale.draft_added");
                } elseif ($input['status'] == 'draft' && $input['is_quotation'] == 1) {
                    $msg = trans("lang_v1.quotation_updated");
                    if (!$is_direct_sale) {
                        $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id);
                    } else {
                        $receipt = '';
                    }
                } elseif ($input['status'] == 'final') {
                    $msg = trans("sale.pos_sale_updated");
                    if (!$is_direct_sale && !$transaction->is_suspend) {
                        $receipt = $this->receiptContent($business_id, $input['location_id'], $transaction->id);
                    } else {
                        $receipt = '';
                    }
                }

                $output = ['success' => 1, 'msg' => $msg, 'receipt' => $receipt ];
            } else {
                $output = ['success' => 0,
                            'msg' => trans("messages.something_went_wrong")
                        ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            $output = ['success' => 0,
                            'msg' => __('messages.something_went_wrong')
                        ];
        }

        if (!$is_direct_sale) {
            return $output;
        } else {
            if ($input['status'] == 'draft') {
                if (isset($input['is_quotation']) && $input['is_quotation'] == 1) {
                    return redirect()
                        ->action('SellController@getQuotations')
                        ->with('status', $output);
                } else {
                    return redirect()
                        ->action('SellController@getDrafts')
                        ->with('status', $output);
                }
            } else {
                if (!empty($transaction->sub_type) && $transaction->sub_type == 'repair') {
                    return redirect()
                        ->action('\Modules\Repair\Http\Controllers\RepairController@index')
                        ->with('status', $output);
                }

                return redirect()
                    ->action('SellController@index')
                    ->with('status', $output);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!auth()->user()->can('sell.delete')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            try {
                //Check if return exist then not allowed
                if ($this->transactionUtil->isReturnExist($id)) {
                    $output = [
                        'success' => false,
                        'msg' => __('lang_v1.return_exist')
                    ];
                    return $output;
                }

                $business_id = request()->session()->get('user.business_id');
                $transaction = Transaction::where('id', $id)
                            ->where('business_id', $business_id)
                            ->where('type', 'sell')
                            ->with(['sell_lines'])
                            ->first();

                //Begin transaction
                DB::beginTransaction();

                if (!empty($transaction)) {
                    //If status is draft direct delete transaction
                    if ($transaction->status == 'draft') {
                        $transaction->delete();
                    } else {
                        $deleted_sell_lines = $transaction->sell_lines;
                        $deleted_sell_lines_ids = $deleted_sell_lines->pluck('id')->toArray();
                        $this->transactionUtil->deleteSellLines(
                            $deleted_sell_lines_ids,
                            $transaction->location_id
                        );

                        $transaction->status = 'draft';
                        $business = ['id' => $business_id,
                                'accounting_method' => request()->session()->get('business.accounting_method'),
                                'location_id' => $transaction->location_id
                            ];

                        $this->transactionUtil->adjustMappingPurchaseSell('final', $transaction, $business, $deleted_sell_lines_ids);

                        //Delete Cash register transactions
                        $transaction->cash_register_payments()->delete();

                        $transaction->delete();
                    }
                }

                //Delete account transactions
                AccountTransaction::where('transaction_id', $transaction->id)->delete();
                DB::table('tbl_trx_akuntansi')->where('transaction_id', $id)->delete();
                DB::commit();
                $output = [
                    'success' => true,
                    'msg' => __('lang_v1.sale_delete_success')
                ];
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

                $output['success'] = false;
                $output['msg'] = trans("messages.something_went_wrong");
            }

            return $output;
        }
    }

    /**
     * Returns the HTML row for a product in POS
     *
     * @param  int  $variation_id
     * @param  int  $location_id
     * @return \Illuminate\Http\Response
     */
    public function getProductRow($variation_id, $location_id)
    {
        $output = [];

        // try {
            $kategori_customer = request()->get('kategori_customer');
            $row_count = request()->get('product_row');
            $row_count = $row_count + 1;
            $is_direct_sell = false;
            if (request()->get('is_direct_sell') == 'true') {
                $is_direct_sell = true;
            }
            $month=date('m');
            $day=date('m-d');
            $contact=DB::table('contacts')->where('id', request()->get('customer_id', null))->first();

            $birthday=explode('-', $contact->birthday);
            
            if ($contact->customer_group_id != null) {
                if ($contact->exp_member_date > date('Y-m-d')) {
                    DB::table('contacts')
                                ->where('id', request()->get('customer_id', null))
                                ->update(['customer_group_id' => null]);
                }else{

                    $customer_group_id=2;
                    if($contact->birthday != null){
                        $month_birth=$birthday[1];
                        $day_birth=$birthday[1].'-'.$birthday[2];
                        if ($month_birth == $month) {
                            $customer_group_id=3;
                            if ($day == $day_birth) {
                                $customer_group_id=4;
                            }
                        }
                    }
                    DB::table('contacts')
                                ->where('id', request()->get('customer_id', null))
                                ->update(['customer_group_id' => $customer_group_id]);
                }
            }
            
            $business_id = request()->session()->get('user.business_id');

            $product = $this->productUtil->getDetailsFromVariation($variation_id, $business_id, $location_id);
            if(count((array)$product)==0){
                $product = $this->productUtil->getDetailsFromVariation($variation_id, $business_id,null,true,true);
            }

            $getHarga = DB::table('tb_harga_produk')->select('harga','harga_inc_tax')
            ->where('product_id',$product->product_id)
            ->where('id_kategori',$kategori_customer)->first();

            $product->default_sell_price = $getHarga->harga;
            $product->sell_price_inc_tax = $getHarga->harga_inc_tax;

            $category=Category::where('id', $product->category_id)->first();
            
            $product->formatted_qty_available = $this->productUtil->num_f($product->qty_available, false, null, true);

            $sub_units = $this->productUtil->getSubUnits($business_id, $product->unit_id);
            //Get customer group and change the price accordingly
            $customer_id = request()->get('customer_id', null);
            $cg = $this->contactUtil->getCustomerGroup($business_id, $customer_id);
            if(strpos(strtolower($product->product_name), 'pak gun') !== false || strpos(strtolower($category->name), 'promo') !== false){
                $percent = 0;
            // }else{
            //     $percent = 1;
            // }
            // if(strpos(strtolower($category->name), 'promo') !== false){
            //     // print_r('promo');
            //     $percent = 0;
            }else{
                $percent = (empty($cg) || empty($cg->amount)) ? 0 : $cg->amount;
            }
            $product->default_sell_price = $product->default_sell_price + ($percent * $product->default_sell_price / 100);
            $product->sell_price_inc_tax = $product->sell_price_inc_tax + ($percent * $product->sell_price_inc_tax / 100);

            $tax_dropdown = TaxRate::forBusinessDropdown($business_id, true, true);

            $enabled_modules = $this->transactionUtil->allModulesEnabled();

            //Get lot number dropdown if enabled
            $lot_numbers = [];
            if (request()->session()->get('business.enable_lot_number') == 1 || request()->session()->get('business.enable_product_expiry') == 1) {
                $lot_number_obj = $this->transactionUtil->getLotNumbersFromVariation($variation_id, $business_id, $location_id, true);
                foreach ($lot_number_obj as $lot_number) {
                    $lot_number->qty_formated = $this->productUtil->num_f($lot_number->qty_available);
                    $lot_numbers[] = $lot_number;
                }
            }
            $product->lot_numbers = $lot_numbers;

            $price_group = request()->input('price_group');
            if (!empty($price_group)) {
                $variation_group_prices = $this->productUtil->getVariationGroupPrice($variation_id, $price_group, $product->tax_id);
                
                if (!empty($variation_group_prices['price_inc_tax'])) {
                    $product->sell_price_inc_tax = $variation_group_prices['price_inc_tax'];
                    $product->default_sell_price = $variation_group_prices['price_exc_tax'];
                }
            }
            $business_details = $this->businessUtil->getDetails($business_id);
            $pos_settings = empty($business_details->pos_settings) ? $this->businessUtil->defaultPosSettings() : json_decode($business_details->pos_settings, true);

            $query = DB::table('tbl_pegawai')->select('*');
            // ->where('id_jabatan', 1);
            if ($location_id != null) {
                $query->where('location_id', $location_id);
            }
            $pegawai=$query->get();
            $pegawai_option=array();
            foreach ($pegawai as $key => $value) {
                $pegawai_option[$value->id_pegawai]=$value->nama_pegawai;
            }
            $output['success'] = true;

            $waiters = null;
            if ($this->productUtil->isModuleEnabled('service_staff') && !empty($pos_settings['inline_service_staff'])) {
                $waiters_enabled = true;
                $waiters = $this->productUtil->serviceStaffDropdown($business_id, $location_id);
            }

            if (request()->get('type') == 'sell-return') {
                $output['html_content'] =  view('sell_return.partials.product_row')
                            ->with(compact('product', 'row_count', 'tax_dropdown', 'enabled_modules', 'sub_units', 'pegawai_option'))
                            ->render();
            } else {
                $is_cg = !empty($cg->id) ? true : false;
                $is_pg = !empty($price_group) ? true : false;
                $discount = $this->productUtil->getProductDiscount($product, $business_id, $location_id, $is_cg, $is_pg);
                // $discount=null;
                
                $output['html_content'] =  view('sale_pos.product_row')
                            ->with(compact('product', 'row_count', 'tax_dropdown', 'enabled_modules', 'pos_settings', 'sub_units', 'discount', 'waiters', 'pegawai_option', 'is_cg'))
                            ->render();
            }

            $output['enable_sr_no'] = $product->enable_sr_no;

            if ($this->transactionUtil->isModuleEnabled('modifiers')  && !$is_direct_sell) {
                $this_product = Product::where('business_id', $business_id)
                                        ->find($product->product_id);
                if (count($this_product->modifier_sets) > 0) {
                    $product_ms = $this_product->modifier_sets;
                    $output['html_modifier'] =  view('restaurant.product_modifier_set.modifier_for_product')
                    ->with(compact('product_ms', 'row_count'))->render();
                }
            }

            // print_r($output);exit();
        // } catch (\Exception $e) {
        //     \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());

        //     $output['success'] = false;
        //     $output['msg'] = __('lang_v1.item_out_of_stock');
        // }

        return $output;
    }

    /**
     * Returns the HTML row for a payment in POS
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getPaymentRow(Request $request)
    {
        $business_id = request()->session()->get('user.business_id');
        
        $row_index = $request->input('row_index');
        $removable = true;
        $payment_types = $this->productUtil->payment_types();

        $payment_line = $this->dummyPaymentLine;

        //Accounts
        $accounts = [];
        if ($this->moduleUtil->isModuleEnabled('account')) {
            $accounts = Account::forDropdown($business_id, true, false);
        }

        return view('sale_pos.partials.payment_row')
            ->with(compact('payment_types', 'row_index', 'removable', 'payment_line', 'accounts'));
    }

    /**
     * Returns recent transactions
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getRecentTransactions(Request $request)
    {
        $business_id = $request->session()->get('user.business_id');
        $user_id = $request->session()->get('user.id');
        $transaction_status = $request->get('status');

        $register = $this->cashRegisterUtil->getCurrentCashRegister($user_id);

        $query = Transaction::where('business_id', $business_id)
                        ->where('transactions.created_by', $user_id)
                        ->where('transactions.type', 'sell')
                        ->where('is_direct_sale', 0);

        if ($transaction_status == 'final') {
            if (!empty($register->id)) {
                $query->leftjoin('cash_register_transactions as crt', 'transactions.id', '=', 'crt.transaction_id')
                ->where('crt.cash_register_id', $register->id);
            }
        }

        if ($transaction_status == 'quotation') {
            $query->where('transactions.status', 'draft')
                ->where('is_quotation', 1);
        } elseif ($transaction_status == 'draft') {
            $query->where('transactions.status', 'draft')
                ->where('is_quotation', 0);
        } else {
            $query->where('transactions.status', $transaction_status);
        }

        $transactions = $query->orderBy('transactions.created_at', 'desc')
                            ->groupBy('transactions.id')
                            ->select('transactions.*')
                            ->with(['contact'])
                            ->limit(10)
                            ->get();

        return view('sale_pos.partials.recent_transactions')
            ->with(compact('transactions'));
    }

    /**
     * Prints invoice for sell
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function printInvoice(Request $request, $transaction_id)
    {
        if (request()->ajax()) {
            try {
                $output = ['success' => 0,
                        'msg' => trans("messages.something_went_wrong")
                        ];

                $business_id = $request->session()->get('user.business_id');
            
                $transaction = Transaction::where('business_id', $business_id)
                                ->where('id', $transaction_id)
                                ->with(['location'])
                                ->first();

                if (empty($transaction)) {
                    return $output;
                }

                $printer_type = 'browser';
                if (!empty(request()->input('check_location')) && request()->input('check_location') == true) {
                    $printer_type = $transaction->location->receipt_printer_type;
                }

                $is_package_slip = !empty($request->input('package_slip')) ? true : false;

                $receipt = $this->receiptContent($business_id, $transaction->location_id, $transaction_id, $printer_type, $is_package_slip, false);
                
                if (!empty($receipt)) {
                    $output = ['success' => 1, 'receipt' => $receipt];
                }
            } catch (\Exception $e) {
                \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
                
                $output = ['success' => 0,
                        'msg' => trans("messages.something_went_wrong")
                        ];
            }

            return $output;
        }
    }

    /**
     * Gives suggetion for product based on category
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function getProductSuggestion(Request $request)
    {
        if ($request->ajax()) {
            $category_id = $request->get('category_id');
            $brand_id = $request->get('brand_id');
            $location_id = $request->get('location_id');
            $term = $request->get('term');

            $check_qty = false;
            $business_id = $request->session()->get('user.business_id');

            $products = Product::join(
                'variations',
                'products.id',
                '=',
                'variations.product_id'
            )
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
                                    });
                                    ;
                                }
                            }
                        )
                ->active()
                ->where('products.business_id', $business_id)
                ->where('products.type', '!=', 'modifier');
            if ($location_id != null) {
                $products->whereIn('products.location_id', [$location_id, 0]);
                // $products->where('products.location_id', 0);
            }
            //Include search
            if (!empty($term)) {
                $products->where(function ($query) use ($term) {
                    $query->where('products.name', 'like', '%' . $term .'%');
                    $query->orWhere('sku', 'like', '%' . $term .'%');
                    $query->orWhere('sub_sku', 'like', '%' . $term .'%');
                });
            }

            //Include check for quantity
            if ($check_qty) {
                $products->where('VLD.qty_available', '>', 0);
            }
            
            if ($category_id != 'all') {
                $products->where(function ($query) use ($category_id) {
                    $query->where('products.category_id', $category_id);
                    $query->orWhere('products.sub_category_id', $category_id);
                });
            }
            if ($brand_id != 'all') {
                $products->where('products.brand_id', $brand_id);
            }

            $products = $products->select(
                'products.id as product_id',
                'products.name',
                'products.type',
                'products.enable_stock',
                'variations.id as variation_id',
                'variations.name as variation',
                'VLD.qty_available',
                'variations.default_sell_price as selling_price',
                'variations.sub_sku',
                'products.image'
            )
            ->orderBy('products.name', 'asc')
            ->groupBy('variations.id')
            ->paginate(20);

            return view('sale_pos.partials.product_list')
                    ->with(compact('products'));
        }
    }

    /**
     * Shows invoice url.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function showInvoiceUrl($id)
    {
        if (!auth()->user()->can('sell.update')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $transaction = Transaction::where('business_id', $business_id)
                                   ->findorfail($id);
            $url = $this->transactionUtil->getInvoiceUrl($id, $business_id);

            return view('sale_pos.partials.invoice_url_modal')
                    ->with(compact('transaction', 'url'));
        }
    }

    /**
     * Shows invoice to guest user.
     *
     * @param  string  $token
     * @return \Illuminate\Http\Response
     */
    public function showInvoice($token)
    {
        $transaction = Transaction::where('invoice_token', $token)->with(['business'])->first();

        if (!empty($transaction)) {
            $receipt = $this->receiptContent($transaction->business_id, $transaction->location_id, $transaction->id, 'browser');

            $title = $transaction->business->name . ' | ' . $transaction->invoice_no;
            return view('sale_pos.partials.show_invoice')
                    ->with(compact('receipt', 'title'));
        } else {
            die(__("messages.something_went_wrong"));
        }
    }

    /**
     * Display a listing of the recurring invoices.
     *
     * @return \Illuminate\Http\Response
     */
    public function listSubscriptions()
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');

            $sells = Transaction::leftJoin('contacts', 'transactions.contact_id', '=', 'contacts.id')
                ->leftJoin('transaction_payments as tp', 'transactions.id', '=', 'tp.transaction_id')
                ->join(
                    'business_locations AS bl',
                    'transactions.location_id',
                    '=',
                    'bl.id'
                )
                ->where('transactions.business_id', $business_id)
                ->where('transactions.type', 'sell')
                ->where('transactions.status', 'final')
                ->where('transactions.is_recurring', 1)
                ->select(
                    'transactions.id',
                    'transactions.transaction_date',
                    'transactions.is_direct_sale',
                    'transactions.invoice_no',
                    'contacts.name',
                    'transactions.subscription_no',
                    'bl.name as business_location',
                    'transactions.recur_parent_id',
                    'transactions.recur_stopped_on',
                    'transactions.is_recurring',
                    'transactions.recur_interval',
                    'transactions.recur_interval_type',
                    'transactions.recur_repetitions'
                )->with(['subscription_invoices']);



            $permitted_locations = auth()->user()->permitted_locations();
            if ($permitted_locations != 'all') {
                $sells->whereIn('transactions.location_id', $permitted_locations);
            }

            if (!empty(request()->start_date) && !empty(request()->end_date)) {
                $start = request()->start_date;
                $end =  request()->end_date;
                $sells->whereDate('transactions.transaction_date', '>=', $start)
                            ->whereDate('transactions.transaction_date', '<=', $end);
            }
            $datatable = Datatables::of($sells)
                ->addColumn(
                    'action',
                    function ($row) {
                        $html = '' ;

                        if ($row->is_recurring == 1 && auth()->user()->can("sell.update")) {
                            $link_text = !empty($row->recur_stopped_on) ? __('lang_v1.start_subscription') : __('lang_v1.stop_subscription');
                            $link_class = !empty($row->recur_stopped_on) ? 'btn-success' : 'btn-danger';

                            $html .= '<a href="' . action('SellPosController@toggleRecurringInvoices', [$row->id]) . '" class="toggle_recurring_invoice btn btn-xs ' . $link_class . '"><i class="fa fa-power-off"></i> ' . $link_text . '</a>';

                            if ($row->is_direct_sale == 0) {
                                $html .= '<a target="_blank" class="btn btn-xs btn-primary" href="' . action('SellPosController@edit', [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __("messages.edit") . '</a>';
                            } else {
                                $html .= '<a target="_blank" class="btn btn-xs btn-primary" href="' . action('SellController@edit', [$row->id]) . '"><i class="glyphicon glyphicon-edit"></i> ' . __("messages.edit") . '</a>';
                            }
                        }

                        return $html;
                    }
                )
                ->removeColumn('id')
                ->editColumn('transaction_date', '{{@format_date($transaction_date)}}')
                ->editColumn('recur_interval', function ($row) {
                    $type = $row->recur_interval == 1 ? str_singular(__('lang_v1.' . $row->recur_interval_type)) : __('lang_v1.' . $row->recur_interval_type);
                    return $row->recur_interval . $type;
                })
                ->addColumn('subscription_invoices', function ($row) {
                    $invoices = [];
                    if (!empty($row->subscription_invoices)) {
                        $invoices = $row->subscription_invoices->pluck('invoice_no')->toArray();
                    }

                    $html = '';
                    $count = 0;
                    if (!empty($invoices)) {
                        $imploded_invoices = '<span class="label bg-info">' . implode('</span>, <span class="label bg-info">', $invoices) . '</span>';
                        $count = count($invoices);
                        $html .= '<small>' . $imploded_invoices . '</small>';
                    }
                    if ($count > 0) {
                        $html .= '<br><small class="text-muted">' .
                    __('sale.total') . ': ' . $count . '</small>';
                    }

                    return $html;
                })
                ->addColumn('last_generated', function ($row) {
                    if (!empty($row->subscription_invoices)) {
                        $last_generated_date = $row->subscription_invoices->max('created_at');
                    }
                    return !empty($last_generated_date) ? $last_generated_date->diffForHumans() : '';
                })
                ->addColumn('upcoming_invoice', function ($row) {
                    if (empty($row->recur_stopped_on)) {
                        $last_generated = !empty($row->subscription_invoices) ? \Carbon::parse($row->subscription_invoices->max('transaction_date')) : \Carbon::parse($row->transaction_date);
                        if ($row->recur_interval_type == 'days') {
                            $upcoming_invoice = $last_generated->addDays($row->recur_interval);
                        } elseif ($row->recur_interval_type == 'months') {
                            $upcoming_invoice = $last_generated->addMonths($row->recur_interval);
                        } elseif ($row->recur_interval_type == 'years') {
                            $upcoming_invoice = $last_generated->addYears($row->recur_interval);
                        }
                    }
                    return !empty($upcoming_invoice) ? $this->transactionUtil->format_date($upcoming_invoice) : '';
                })
                ->rawColumns(['action', 'subscription_invoices'])
                ->make(true);
                
            return $datatable;
        }
        return view('sale_pos.subscriptions');
    }

    /**
     * Starts or stops a recurring invoice.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function toggleRecurringInvoices($id)
    {
        if (!auth()->user()->can('sell.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $business_id = request()->session()->get('user.business_id');
            $transaction = Transaction::where('business_id', $business_id)
                            ->where('type', 'sell')
                            ->where('is_recurring', 1)
                            ->findorfail($id);

            if (empty($transaction->recur_stopped_on)) {
                $transaction->recur_stopped_on = \Carbon::now();
            } else {
                $transaction->recur_stopped_on = null;
            }
            $transaction->save();

            $output = ['success' => 1,
                    'msg' => trans("lang_v1.updated_success")
                ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => 0,
                            'msg' => trans("messages.something_went_wrong")
                        ];
        }

        return $output;
    }
    public function savePengeluaran(Request $request)
    {
        $user_id = $request->session()->get('user.id');
        $business_id = $request->session()->get('user.business_id');
        $id_akun=$request->input('id_akun');
        $akun=DB::table('tbl_akun')->where('id_akun', $id_akun)->first();
        $register = CashRegister::where([
                    'business_id' => $business_id,
                    'user_id' => $user_id,
                    'status' => 'open'
                ])->first();

        //pengeluaran
        $data=array(
            'cash_register_id'       => $register->id,
            'user_id'       => $request->input('user_id'),
            'total'       => $request->input('jml_pengeluaran'),
            'deskripsi_pengeluaran'       => $request->input('desc_pengeluaran'),
            'tipe'       => 'pengeluaran',
            'notes'      => $akun->nama_akun,
            'tanggal'       => date('Y-m-d'),
        );
        DB::table('tbl_pengeluaran')->insert($data);
        $id_last=DB::table('tbl_pengeluaran')->max('id');
        if ($request->input('jml_tunai_pengeluaran')) {
            $data=array(
                'id_pengeluaran'       => $id_last,
                'total'       => $request->input('jml_tunai_pengeluaran'),
                'sumber'      => 'cash',
                'tipe'      => 'pengeluaran'
            );
            DB::table('tbl_detail_pengeluaran')->insert($data);
        }
        if ($request->input('jml_petty_pengeluaran')) {
            $data=array(
                'id_pengeluaran'       => $id_last,
                'total'       => $request->input('jml_petty_pengeluaran'),
                'sumber'      => 'petty',
                'tipe'      => 'pengeluaran'
            );
            DB::table('tbl_detail_pengeluaran')->insert($data);
        }

        $data_trx=array(
            'deskripsi'     => $request->input('desc_pengeluaran'),
            'location_id'     => $request->input('id_lokasi'),
            'id_pengeluaran'     => $id_last,
            'tanggal'       => date('Y-m-d'),
        );
        DB::table('tbl_trx_akuntansi')->insert($data_trx);
        
        $id_last_akun=DB::table('tbl_trx_akuntansi')->max('id_trx_akun');
        if ($request->input('jml_tunai_pengeluaran')) {
            $data1=array(
                        'id_trx_akun'   => $id_last_akun,
                        'id_akun'       => 20,
                        'jumlah'        => $request->input('jml_tunai_pengeluaran'),
                        'tipe'          => 'KREDIT',
                        'keterangan'    => 'akun',
                    );
            DB::table('tbl_trx_akuntansi_detail')->insert($data1);
        }
        if ($request->input('jml_petty_pengeluaran')) {
            $data1=array(
                        'id_trx_akun'   => $id_last_akun,
                        'id_akun'       => 35,
                        'jumlah'        => $request->input('jml_petty_pengeluaran'),
                        'tipe'          => 'KREDIT',
                        'keterangan'    => 'akun',
                    );
            DB::table('tbl_trx_akuntansi_detail')->insert($data1);
        }
        $data1=array(
                'id_trx_akun'   => $id_last_akun,
                'id_akun'       => $request->input('id_akun'),
                'jumlah'        => $request->input('jml_pengeluaran'),
                'tipe'          => 'DEBIT',
                'keterangan'    => 'lawan',
            );
        DB::table('tbl_trx_akuntansi_detail')->insert($data1); 

        $output = ['success' => 1,
                            'msg' => 'Pengeluaran berhasil diinput'
                        ];
        return redirect()
                        ->action('SellPosController@create')
                        ->with('status', $output);
    }
    public function inputSetoran(Request $request)
    {
        $user_id = $request->session()->get('user.id');
        $business_id = $request->session()->get('user.business_id');

        $register = CashRegister::where([
                    'business_id' => $business_id,
                    'user_id' => $user_id,
                    'status' => 'open'
                ])->first();

        $name='';
        if ($request->file('bukti_setor') != null) {
            $file = $request->file('bukti_setor');
            $tujuan_upload = 'uploads/bukti_setor';

                // upload file
            $name=time().'.'.$file->getClientOriginalExtension();
            $file->move($tujuan_upload, $name);
        }

        $data=array(
            'cash_register_id'       => $register->id,
            'location_id'       => $request->input('location_id'),
            'user_id'       => $request->input('user_id'),
            'total'       => $request->input('jml_setoran'),
            'deskripsi_pengeluaran'       => $request->input('desc_setoran'),
            'tipe'       => 'setoran',
            'method'       => $request->input('method_payment'),
            'ref_code'       => $request->input('ref_code'),
            'atas_nama'       => $request->input('to'),
            'tanggal'       => date('Y-m-d'),
            'bukti_setor'   => $name,
            'bank_account_number'       => $request->input('bank_account_number'),
        );
        DB::table('tbl_pengeluaran')->insert($data);
        $id_last=DB::table('tbl_pengeluaran')->max('id');
        if ($request->input('jml_tunai') != 0) {
            $data=array(
                'id_pengeluaran'       => $id_last,
                'total'       => $request->input('jml_tunai'),
                'sumber'      => 'cash'
            );
            DB::table('tbl_detail_pengeluaran')->insert($data);
        }
        if ($request->input('jml_non_tunai') != 0) {
            $data=array(
                'id_pengeluaran'       => $id_last,
                'total'       => $request->input('jml_non_tunai'),
                'sumber'      => 'non tunai'
            );
            DB::table('tbl_detail_pengeluaran')->insert($data);
        }
        if ($request->input('jml_petty') != 0) {
            $data=array(
                'id_pengeluaran'       => $id_last,
                'total'       => $request->input('jml_petty'),
                'sumber'      => 'petty'
            );
            DB::table('tbl_detail_pengeluaran')->insert($data);
        }
        $output = ['success' => 1,
                            'msg' => 'Setoran berhasil diinput'
                        ];
        return redirect()
                        ->action('SellPosController@create')
                        ->with('status', $output);
    }
    public function inputPetty(Request $request)
    {
        $user_id = $request->session()->get('user.id');
        $business_id = $request->session()->get('user.business_id');

        $register = CashRegister::where([
                    'business_id' => $business_id,
                    'user_id' => $user_id,
                    'status' => 'open'
                ])->first();

        $data_trx=array(
            'deskripsi'     => $request->input('desc_pengeluaran_petty'),
            'location_id'     => $request->input('id_lokasi'),
            'tanggal'       => date('Y-m-d'),
        );
        DB::table('tbl_trx_akuntansi')->insert($data_trx);
        
        $id_last=DB::table('tbl_trx_akuntansi')->max('id_trx_akun');

        $data1=array(
                    'id_trx_akun'   => $id_last,
                    'id_akun'       => 20,
                    'jumlah'        => $request->input('jml_pengeluaran_petty'),
                    'tipe'          => 'KREDIT',
                    'keterangan'    => 'akun',
                );
        DB::table('tbl_trx_akuntansi_detail')->insert($data1);
        $data1=array(
                'id_trx_akun'   => $id_last,
                'id_akun'       => 35,
                'jumlah'        => $request->input('jml_pengeluaran_petty'),
                'tipe'          => 'DEBIT',
                'keterangan'    => 'lawan',
            );
        DB::table('tbl_trx_akuntansi_detail')->insert($data1);  

        $data=array(
            'cash_register_id'       => $register->id,
            'user_id'       => $request->input('user_id'),
            'total'       => $request->input('jml_pengeluaran_petty'),
            'tipe'       => 'petty',
            'tanggal'       => date('Y-m-d'),
            'deskripsi_pengeluaran'       => $request->input('desc_pengeluaran_petty'),
        );
        DB::table('tbl_pengeluaran')->insert($data);
        $id_last=DB::table('tbl_pengeluaran')->max('id');
        if ($request->input('jml_tunai_petty') != 0) {
            $data=array(
                'id_pengeluaran'       => $id_last,
                'total'       => $request->input('jml_tunai_petty'),
                'sumber'      => 'cash',
                'tipe'      => 'petty'
            );
            DB::table('tbl_detail_pengeluaran')->insert($data);
        }
        if ($request->input('jml_non_tunai_petty') != 0) {
            $data=array(
                'id_pengeluaran'       => $id_last,
                'total'       => $request->input('jml_non_tunai_petty'),
                'sumber'      => 'non tunai',
                'tipe'      => 'petty'
            );
            DB::table('tbl_detail_pengeluaran')->insert($data);
        }
        $output = ['success' => 1,
                            'msg' => 'Setoran berhasil diinput'
                        ];
        return redirect()
                        ->action('SellPosController@create')
                        ->with('status', $output);
    }
    public function inputKasbon(Request $request)
    {
        $user_id = $request->session()->get('user.id');
        $business_id = $request->session()->get('user.business_id');

        $register = CashRegister::where([
                    'business_id' => $business_id,
                    'user_id' => $user_id,
                    'status' => 'open'
                ])->first();

        $data=array(
            'cash_register_id'       => $register->id,
            'user_id'       => $request->input('user_id'),
            'id_pegawai'       => $request->input('pegawai_id'),
            'total'       => $request->input('jml_pengeluaran_kasbon'),
            'tipe'       => 'kasbon',
            'tanggal'       => date('Y-m-d'),
            'deskripsi_pengeluaran'       => $request->input('desc_pengeluaran_kasbon'),
        );
        DB::table('tbl_pengeluaran')->insert($data);
        $id_last=DB::table('tbl_pengeluaran')->max('id');
        if ($request->input('jml_tunai_kasbon') != 0) {
            $data=array(
                'id_pengeluaran'       => $id_last,
                'total'       => $request->input('jml_tunai_kasbon'),
                'sumber'      => 'cash',
                'tipe'      => 'kasbon'
            );
            DB::table('tbl_detail_pengeluaran')->insert($data);
        }
        if ($request->input('jml_non_tunai_kasbon') != 0) {
            $data=array(
                'id_pengeluaran'       => $id_last,
                'total'       => $request->input('jml_non_tunai_kasbon'),
                'sumber'      => 'non tunai',
                'tipe'      => 'kasbon'
            );
            DB::table('tbl_detail_pengeluaran')->insert($data);
        }
        $output = ['success' => 1,
                            'msg' => 'Kasbon berhasil diinput'
                        ];
        return redirect()
                        ->action('SellPosController@create')
                        ->with('status', $output);
    }
    public function listSetoran(Request $request)
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        $user=User::find($user_id);
        $location_id = $user->location_id;
        $business_location = BusinessLocation::where('business_id', $business_id)->get();
        $business_locations=array();
        $business_locations['']='Pilih Cabang';
        foreach ($business_location as $key => $value) {
            $business_locations[$value->id]=$value->name;
        }
        foreach ($business_locations as $key => $value) {
            if ($location_id != null) {
                if ($location_id != $key) {
                    unset($business_locations[$key]);
                }
            }
        }
        
        // if (request()->ajax()) {

        //     $pengeluaran = DB::table('tbl_pengeluaran')
        //                 ->where('tbl_pengeluaran.tipe', 'setoran')
        //                 ->join('users', 'tbl_pengeluaran.user_id','=', 'users.id')
        //                 ->join('business_locations', 'business_locations.id','=', 'users.location_id')
        //                 ->select('tbl_pengeluaran.*', 'users.first_name', 'users.last_name', 'business_locations.name AS cabang');
        //     if ($user->location_id != null) {
        //         if ($user->role == 2) {
        //             $pengeluaran->where('user_id', $user_id);
        //         }else{
        //             $get_user=User::where('location_id', $user->location_id)->pluck('id');
        //             $pengeluaran->whereIn('user_id', $get_user);
        //         }
        //     }

        //     $pengeluaran->get();
        //     $datatable = Datatables::of($pengeluaran)
        //         ->addColumn(
        //             'action',
        //             function ($row) {
        //                 $html = '' ;            
        //                 $html .= '<a href="' . action('SellPosController@deletePengeluaran', $row->id) . '" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>';
        //                 return $html;
        //             }
        //         )->filterColumn('first_name', function($query, $keyword) {
        //             $query->where('users.first_name', 'like', '%'.$keyword.'%');
        //             $query->orWhere('users.last_name', 'like', '%'.$keyword.'%');
        //         })->filterColumn('cabang', function($query, $keyword) {
        //             $query->where('business_locations.name', 'like', '%'.$keyword.'%');
        //         })       
        //         ->make(true);
                
        //     return $datatable;
        // }
        return view('sale_pos.list_setoran')->with(compact('location_id', 'business_locations'));
    }
    public function jsonSetoran(Request $request){
        $user_id = request()->session()->get('user.id');
        $user=User::find($user_id);
        $pengeluaran = DB::table('tbl_pengeluaran')
                    ->where('tbl_pengeluaran.tipe', 'setoran')
                    ->join('users', 'tbl_pengeluaran.user_id','=', 'users.id')
                    ->join('business_locations', 'business_locations.id','=', 'users.location_id')
                    ->select('tbl_pengeluaran.*', 'users.first_name', 'users.last_name', 'business_locations.name AS cabang');
        if ($request->input('location_id')) {
            if ($user->role == 2) {
                $pengeluaran->where('user_id', $user_id);
            }else{
                $get_user=User::where('location_id', $request->input('location_id'))->pluck('id');
                $pengeluaran->whereIn('user_id', $get_user);
            }
        }else if ($user->location_id != null) {
            if ($user->role == 2) {
                $pengeluaran->where('user_id', $user_id);
            }else{
                $get_user=User::where('location_id', $user->location_id)->pluck('id');
                $pengeluaran->whereIn('user_id', $get_user);
            }
        }

        if ($request->input('date')) {
            $date_range = $request->input('date');
            $date_range_array = explode('~', $date_range);
            $start_date = date('Y-m-d', strtotime($date_range_array[0]));
            $end_date = date('Y-m-d', strtotime($date_range_array[1]));
            $pengeluaran->where('tbl_pengeluaran.tanggal', '>=', $start_date);
            $pengeluaran->where('tbl_pengeluaran.tanggal', '<=', $end_date);
        }

        $pengeluaran->get();
        $datatable = Datatables::of($pengeluaran)
            ->addColumn(
                'action',
                function ($row) {
                    $html = '' ;            
                    $html .= '<a href="' . action('SellPosController@deletePengeluaran', $row->id) . '" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>';
                    return $html;
                }
            )->filterColumn('first_name', function($query, $keyword) {
                $query->where('users.first_name', 'like', '%'.$keyword.'%');
                $query->orWhere('users.last_name', 'like', '%'.$keyword.'%');
            })->filterColumn('cabang', function($query, $keyword) {
                $query->where('business_locations.name', 'like', '%'.$keyword.'%');
            })       
            ->make(true);
            
        return $datatable;
    }
    public function listPengeluaran()
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        $user=User::find($user_id);
        $location_id = $user->location_id;
        $business_location = BusinessLocation::where('business_id', $business_id)->get();
        $business_locations=array();
        $business_locations['']='Pilih Cabang';
        foreach ($business_location as $key => $value) {
            $business_locations[$value->id]=$value->name;
        }
        foreach ($business_locations as $key => $value) {
            if ($location_id != null) {
                if ($location_id != $key) {
                    unset($business_locations[$key]);
                }
            }
        }
        // if (request()->ajax()) {
            
        // }
        return view('sale_pos.list_pengeluaran')->with(compact('location_id', 'business_locations'));
    }

    public function jsonPengeluaran(Request $request){
        $user_id = request()->session()->get('user.id');
        $user=User::find($user_id);
        $pengeluaran = DB::table('tbl_pengeluaran')
                    ->where('tbl_pengeluaran.tipe', 'pengeluaran')
                    ->join('users', 'tbl_pengeluaran.user_id','=', 'users.id')
                    ->join('business_locations', 'business_locations.id','=', 'users.location_id')
                    ->select('tbl_pengeluaran.tanggal', 'tbl_pengeluaran.total', 'tbl_pengeluaran.deskripsi_pengeluaran', 'tbl_pengeluaran.id', 'users.first_name', 'users.last_name', 'business_locations.name AS cabang');
        if ($request->input('location_id')) {
            if ($user->role == 2) {
                $pengeluaran->where('user_id', $user_id);
            }else{
                $get_user=User::where('location_id', $request->input('location_id'))->pluck('id');
                $pengeluaran->whereIn('user_id', $get_user);
            }
        }else if ($user->location_id != null) {
            if ($user->role == 2) {
                $pengeluaran->where('user_id', $user_id);
            }else{
                $get_user=User::where('location_id', $user->location_id)->pluck('id');
                $pengeluaran->whereIn('user_id', $get_user);
            }
        }
        if ($request->input('date')) {
            $date_range = $request->input('date');
            $date_range_array = explode('~', $date_range);
            $start_date = date('Y-m-d', strtotime($date_range_array[0]));
            $end_date = date('Y-m-d', strtotime($date_range_array[1]));
            $pengeluaran->where('tbl_pengeluaran.tanggal', '>=', $start_date);
            $pengeluaran->where('tbl_pengeluaran.tanggal', '<=', $end_date);
        }
        $pengeluaran->get();
        $datatable = Datatables::of($pengeluaran)
            ->addColumn(
                'action',
                function ($row) {
                    $html = '' ;            
                    if (auth()->user()->can("spend.delete")) {    
                        $html .= '<a href="' . action('SellPosController@deletePengeluaran', $row->id) . '" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>';
                    }
                    return $html;
                }
            )->filterColumn('first_name', function($query, $keyword) {
                $query->where('users.first_name', 'like', '%'.$keyword.'%');
                $query->orWhere('users.last_name', 'like', '%'.$keyword.'%');
            })->filterColumn('cabang', function($query, $keyword) {
                $query->where('business_locations.name', 'like', '%'.$keyword.'%');
            }) 
            ->make(true);
            
        return $datatable;
    }

    public function listKasbon(Request $request)
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        $user=User::find($user_id);
        $location_id = $user->location_id;
        $business_location = BusinessLocation::where('business_id', $business_id)->get();
        $business_locations=array();
        $business_locations['']='Pilih Cabang';
        foreach ($business_location as $key => $value) {
            $business_locations[$value->id]=$value->name;
        }
        foreach ($business_locations as $key => $value) {
            if ($location_id != null) {
                if ($location_id != $key) {
                    unset($business_locations[$key]);
                }
            }
        }
        
        // if (request()->ajax()) {
        //     $pengeluaran = DB::table('tbl_pengeluaran')
        //                 ->where('tbl_pengeluaran.tipe', 'kasbon')
        //                 ->join('users', 'tbl_pengeluaran.user_id','=', 'users.id')
        //                 ->join('business_locations', 'business_locations.id','=', 'users.location_id')
        //                 ->join('tbl_pegawai', 'tbl_pengeluaran.id_pegawai','=', 'tbl_pegawai.id_pegawai')
        //                 ->select('tbl_pengeluaran.tanggal', 'tbl_pengeluaran.total', 'tbl_pengeluaran.deskripsi_pengeluaran', 'tbl_pengeluaran.id', 'users.first_name', 'users.last_name', 'tbl_pegawai.nama_pegawai', 'business_locations.name AS cabang');
        //     if ($user->location_id != null) {
        //         if ($user->role == 2) {
        //             $pengeluaran->where('user_id', $user_id);
        //         }else{
        //             $get_user=User::where('location_id', $user->location_id)->pluck('id');
        //             $pengeluaran->whereIn('user_id', $get_user);
        //         }
        //     }
            
        //     $pengeluaran->get();
        //     $datatable = Datatables::of($pengeluaran)
        //         ->addColumn(
        //             'action',
        //             function ($row) {
        //                 $html = '' ;            
        //                 $html .= '<a href="' . action('SellPosController@deletePengeluaran', $row->id) . '" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>';
        //                 return $html;
        //             }
        //         )->filterColumn('nama_pegawai', function($query, $keyword) {
        //             $query->where('tbl_pegawai.nama_pegawai', 'like', '%'.$keyword.'%');
        //         })->filterColumn('first_name', function($query, $keyword) {
        //             $query->where('users.first_name', 'like', '%'.$keyword.'%');
        //             $query->orWhere('users.last_name', 'like', '%'.$keyword.'%');
        //         })->filterColumn('cabang', function($query, $keyword) {
        //             $query->where('business_locations.name', 'like', '%'.$keyword.'%');
        //         }) 
        //         ->make(true);
                
        //     return $datatable;
        // }
        return view('sale_pos.list_kasbon')->with(compact('location_id', 'business_locations'));
    }
    public function jsonKasbon(Request $request){
        $user_id = request()->session()->get('user.id');
        $user=User::find($user_id);

        $pengeluaran = DB::table('tbl_pengeluaran')
                    ->where('tbl_pengeluaran.tipe', 'kasbon')
                    ->join('users', 'tbl_pengeluaran.user_id','=', 'users.id')
                    ->join('tbl_pegawai', 'tbl_pengeluaran.id_pegawai','=', 'tbl_pegawai.id_pegawai')
                    ->join('business_locations', 'business_locations.id','=', 'tbl_pegawai.location_id')
                    ->select('tbl_pengeluaran.tanggal', 'tbl_pengeluaran.total', 'tbl_pengeluaran.deskripsi_pengeluaran', 'tbl_pengeluaran.id', 'users.first_name', 'users.last_name', 'tbl_pegawai.nama_pegawai', 'business_locations.name AS cabang');
        if ($request->input('location_id')) {
            if ($user->role == 2) {
                $pengeluaran->where('user_id', $user_id);
            }else{
                $get_user=User::where('location_id', $request->input('location_id'))->pluck('id');
                $pengeluaran->whereIn('user_id', $get_user);
            }
        }else if ($user->location_id != null) {
            if ($user->role == 2) {
                $pengeluaran->where('user_id', $user_id);
            }else{
                $get_user=User::where('location_id', $user->location_id)->pluck('id');
                $pengeluaran->whereIn('user_id', $get_user);
            }
        }
        if ($request->input('date')) {
            $date_range = $request->input('date');
            $date_range_array = explode('~', $date_range);
            $start_date = date('Y-m-d', strtotime($date_range_array[0]));
            $end_date = date('Y-m-d', strtotime($date_range_array[1]));
            $pengeluaran->where('tbl_pengeluaran.tanggal', '>=', $start_date);
            $pengeluaran->where('tbl_pengeluaran.tanggal', '<=', $end_date);
        }
        $pengeluaran->get();
        $datatable = Datatables::of($pengeluaran)
            ->addColumn(
                'action',
                function ($row) {
                    $html = '' ;            
                    $html .= '<a href="' . action('SellPosController@deletePengeluaran', $row->id) . '" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>';
                    return $html;
                }
            )->filterColumn('nama_pegawai', function($query, $keyword) {
                $query->where('tbl_pegawai.nama_pegawai', 'like', '%'.$keyword.'%');
            })->filterColumn('first_name', function($query, $keyword) {
                $query->where('users.first_name', 'like', '%'.$keyword.'%');
                $query->orWhere('users.last_name', 'like', '%'.$keyword.'%');
            })->filterColumn('cabang', function($query, $keyword) {
                $query->where('business_locations.name', 'like', '%'.$keyword.'%');
            }) 
            ->make(true);
            
        return $datatable;
    }
    public function detailPengeluaran($id){
        $details=DB::table('tbl_detail_pengeluaran')->join('tbl_pengeluaran', 'tbl_pengeluaran.id', '=', 'tbl_detail_pengeluaran.id_pengeluaran')->where('id_pengeluaran', $id)->get();
        return view('sale_pos.detail_pengeluaran')->with(compact('details'));
    }
    public function deletePengeluaran($id){
        $data=DB::table('tbl_pengeluaran')->where('id', $id)->first();
        $data1=$data;
        if ($data != null) {
            $data=DB::table('tbl_pengeluaran')->where('id', $id)->delete();
            DB::table('tbl_trx_akuntansi')->where('id_pengeluaran', $id)->delete();
            $output = ['success' => 1,
                            'msg' => 'Data berhasil dihapus'
                        ];
        }else{
            $output = ['success' => 0,
                            'msg' => 'Data tidak ditemukan'
                        ];
        }
        if ($data1->tipe == 'setoran') {
            return redirect()
                            ->action('SellPosController@listSetoran')
                            ->with('status', $output);
        }else if ($data1->tipe == 'pengeluaran') {
            return redirect()
                            ->action('SellPosController@listPengeluaran')
                            ->with('status', $output);
        }else{
            return redirect()
                            ->action('SellPosController@listKasbon')
                            ->with('status', $output);
        }
    }
    public function listAntrian(){
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }

        $user_id = request()->session()->get('user.id');
        $user = User::where('id', $user_id)->first();
        $location_id = $user->location_id;
        $business_id = request()->session()->get('user.business_id');
        if (request()->ajax()) {
            $get_set_book=DB::table('tbl_set_antrian')
                                ->select('tbl_set_antrian.id', 'tbl_set_antrian.open_book', 'tbl_set_antrian.close_book', 'tbl_set_antrian.total_book_hours', 'tbl_set_antrian.location_id');

            if ($location_id != null) {
                $get_set_book->where('tbl_set_antrian.location_id', $location_id);
            }
            $get_set_book->get();

            $datatable = Datatables::of($get_set_book)
                ->addColumn(
                    'name',
                    function ($row) {
                        $location=$this->getLocation($row->location_id);
                        return $location;
                    }
                )         
                ->make(true);
                
            return $datatable;
        }
        $business_data = BusinessLocation::all();
        $location_option=array();
        foreach ($business_data as $key => $value) {
            if ($location_id == null) {
                $location_option[$value->id]=$value->name;
            }else{
                if ($location_id == $value->id) {
                    $location_option[$value->id]=$value->name;
                }
            }
        }
        return view('sale_pos.set_booking')->with(compact('location_option'));
    }
    private function getLocation($id){
        $business_locations=DB::table('business_locations')->where('id', $id)->first();
        return $business_locations->name;
    }
    public function getListAntrian(Request $request){
        $user_id = request()->session()->get('user.id');
        $user = User::where('id', $user_id)->first();
        if ($request->input('location_id')) {
            $location_id = $request->input('location_id');
        }else{
            $location_id = $user->location_id;
        }
        $get_set_book=DB::table('tbl_set_antrian')->where('location_id', $location_id)->first();
        $date=date('Y-m-d');
        $time_now = date('H:i');
        $get_antrian=DB::table('tbl_antrian')
                                ->join('contacts', 'contacts.id', '=', 'tbl_antrian.contact_id')
                                ->select('tbl_antrian.id', 'tbl_antrian.time', 'tbl_antrian.is_done', 'tbl_antrian.total_book', 'contacts.name', 'contacts.mobile')
                                ->where('business_location_id', $location_id)
                                ->where('time', '>=', $time_now)
                                // ->where('is_done', 0)
                                ->where('date', $date)
                                ->orderBy('time', 'created_at')
                                ->get();
        
        foreach ($get_antrian as $key => $value) {
            $after = date('H:i:s' , strtotime($value->time. ' + 1 hours'));
            $value->time_until=$after;
        }
        if ($get_set_book != null) {
            if ($get_set_book->is_active == 0) {
                $get_antrian=array();
            }
        }
        $datatable = Datatables::of($get_antrian)
                ->make(true);
                
        return $datatable;
    }
    public function cekListAntrian($id){
        $query=DB::table('tbl_antrian')->where('id', $id)->update(['is_done' => 1]);
        return $query;
    }
    public function getPegawaiByLocation($id){
        $query = DB::table('tbl_pegawai')->select('*')->where('id_jabatan', 1);
        $query->where('location_id', $id);
        
        $pegawai['data']=$query->get();

        return $pegawai;
    }
    public function setBooking(Request $request){
        $location_id=$request->input('id_lokasi');
        $open_book=$request->input('open_book');
        $close_book=$request->input('close_book');
        $total_booking=$request->input('total_booking');
        $activeBook=$request->input('activeBook') ? 1 : 0;
        $data=array(
            'location_id'   => $location_id,
            'open_book'  => $open_book,
            'close_book'  => $close_book,
            'total_book_hours'  => $total_booking,
            'is_active'     => $activeBook
        );

        $get_set_book=DB::table('tbl_set_antrian')->where('location_id', $location_id)->first();
        if ($get_set_book == null) {
            DB::table('tbl_set_antrian')->insert($data);
            $get_set_book=DB::table('tbl_set_antrian')->where('location_id', $location_id)->first();
        }else{
            DB::table('tbl_set_antrian')->where('id', $get_set_book->id)->update($data);
        }
        $datas['data']=$get_set_book;
        // return $datas;
        return view('report.trx_member')->with(compact('datas'));
    }
    public function listPengeluaranOther(Request $request)
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        $user=User::find($user_id);
        $location_id = $user->location_id;
        $business_location = BusinessLocation::where('business_id', $business_id)->get();
        $business_locations=array();
        $business_locations['']='Pilih Cabang';
        foreach ($business_location as $key => $value) {
            $business_locations[$value->id]=$value->name;
        }
        foreach ($business_locations as $key => $value) {
            if ($location_id != null) {
                if ($location_id != $key) {
                    unset($business_locations[$key]);
                }
            }
        }
        $akun_pengeluaran=array();
        $query=DB::table('tbl_akun')->whereIn('id_akun', [126])->get();
        foreach ($query as $key => $value) {
            $akun_pengeluaran[$value->id_akun]=$value->nama_akun;
        }
        return view('sale_pos.list_pengeluaran_other')->with(compact('business_locations', 'location_id', 'akun_pengeluaran'));
    }
    public function listDeposit(Request $request)
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        $user=User::find($user_id);
        $location_id = $user->location_id;
        $business_location = BusinessLocation::where('business_id', $business_id)->get();
        $business_locations=array();
        $business_locations['']='Pilih Cabang';
        foreach ($business_location as $key => $value) {
            $business_locations[$value->id]=$value->name;
        }
        foreach ($business_locations as $key => $value) {
            if ($location_id != null) {
                if ($location_id != $key) {
                    unset($business_locations[$key]);
                }
            }
        }
        $pegawai=DB::table('tbl_pegawai')->get();
        if ($location_id != null) {
            $pegawai=$pegawai->where('location_id', $location_id);
        }
        $option_pegawai=array();
        foreach ($pegawai as $key => $value) {
            $option_pegawai[$value->id_pegawai]=$value->nama_pegawai;
        }
        $akun_pengeluaran=array();
        $akun_pengeluaran2=array();
        $query=DB::table('tbl_akun')->whereIn('id_akun', [127, 128, 129])->get();
        foreach ($query as $key => $value) {
            $akun_pengeluaran2[$value->id_akun]=$value->nama_akun;
            if ($value->id_akun != 129) {
                $akun_pengeluaran[$value->id_akun]=$value->nama_akun;
            }
        }
        return view('sale_pos.list_deposit')->with(compact('business_locations', 'location_id', 'akun_pengeluaran', 'akun_pengeluaran2', 'option_pegawai'));
    }
    public function listSewa(Request $request)
    {
        if (!auth()->user()->can('sell.view') && !auth()->user()->can('direct_sell.access')) {
            abort(403, 'Unauthorized action.');
        }
        $business_id = request()->session()->get('user.business_id');
        $user_id = request()->session()->get('user.id');
        $user=User::find($user_id);
        $location_id = $user->location_id;
        $business_location = BusinessLocation::where('business_id', $business_id)->get();
        $business_locations=array();
        $business_locations['']='Pilih Cabang';
        foreach ($business_location as $key => $value) {
            $business_locations[$value->id]=$value->name;
        }
        foreach ($business_locations as $key => $value) {
            if ($location_id != null) {
                if ($location_id != $key) {
                    unset($business_locations[$key]);
                }
            }
        }
        $akun_pengeluaran=array();
        $query=DB::table('tbl_akun')->whereIn('id_akun', [125])->get();
        foreach ($query as $key => $value) {
            $akun_pengeluaran[$value->id_akun]=$value->nama_akun;
        }
        return view('sale_pos.list_sewa')->with(compact('business_locations', 'location_id', 'akun_pengeluaran'));
    }
    public function savePengeluaranDt(Request $request, $type)
    {
        $user_id = $request->session()->get('user.id');
        $business_id = $request->session()->get('user.business_id');
        $id_akun=$request->input('id_akun');
        $akun=DB::table('tbl_akun')->where('id_akun', $id_akun)->first();
        $id_pegawai=$request->id_pegawai ? $request->id_pegawai : 0;
        //pengeluaran
        $data=array(
            'id_pegawai'    => $id_pegawai,
            'user_id'       => $user_id,
            'total'       => $request->input('jml_pengeluaran'),
            'deskripsi_pengeluaran'       => $request->input('desc_pengeluaran'),
            'tipe'       => 'pengeluaran',
            'notes'      => $akun->nama_akun,
            'location_id'   => $request->id_lokasi,
            'tipe_manajemen'   => $request->tipe_manajemen,
            'is_entry'      => $request->is_entry,
            'tanggal'       => date('Y-m-d'),
        );
        DB::table('tbl_pengeluaran_other')->insert($data);
        $id_last=DB::table('tbl_pengeluaran_other')->max('id');
        if ($request->input('jml_pengeluaran')) {
            $data=array(
                'id_pengeluaran_dt'       => $id_last,
                'total'       => $request->input('jml_pengeluaran'),
            );
            DB::table('tbl_pengeluaran_other_dt')->insert($data);
        }

        $data_trx=array(
            'deskripsi'     => $request->input('desc_pengeluaran'),
            'location_id'     => $request->input('id_lokasi'),
            'id_pengeluaran_dt'     => $id_last,
            'tanggal'       => date('Y-m-d'),
        );
        DB::table('tbl_trx_akuntansi')->insert($data_trx);
        
        $id_last_akun=DB::table('tbl_trx_akuntansi')->max('id_trx_akun');
        $data1=array(
                    'id_trx_akun'   => $id_last_akun,
                    'id_akun'       => 20,
                    'jumlah'        => $request->input('jml_pengeluaran'),
                    'tipe'          => 'KREDIT',
                    'keterangan'    => 'akun',
                );
        DB::table('tbl_trx_akuntansi_detail')->insert($data1);
        $data1=array(
                'id_trx_akun'   => $id_last_akun,
                'id_akun'       => $request->input('id_akun'),
                'jumlah'        => $request->input('jml_pengeluaran'),
                'tipe'          => 'DEBIT',
                'keterangan'    => 'lawan',
            );
        DB::table('tbl_trx_akuntansi_detail')->insert($data1); 

        $output = ['success' => 1,
                            'msg' => 'Pengeluaran berhasil diinput'
                        ];
        if ($type == 1) {
            return redirect()
                        ->action('SellPosController@listPengeluaranOther')
                        ->with('status', $output);
        }else if ($type == 2){
            return redirect()
                        ->action('SellPosController@listSewa')
                        ->with('status', $output);
        }else{
            return redirect()
                        ->action('SellPosController@listDeposit')
                        ->with('status', $output);
        }
    }
    public function jsonPengeluaranDt(Request $request, $type){
        $user_id = request()->session()->get('user.id');
        $user=User::find($user_id);
        $pengeluaran = DB::table('tbl_pengeluaran_other')
                    ->join('users', 'tbl_pengeluaran_other.user_id','=', 'users.id')
                    ->leftJoin('business_locations', 'business_locations.id','=', 'tbl_pengeluaran_other.location_id')
                    ->leftJoin('tbl_pegawai', 'tbl_pegawai.id_pegawai','=', 'tbl_pengeluaran_other.id_pegawai')
                    ->select('tbl_pengeluaran_other.tanggal', 'tbl_pengeluaran_other.tipe_manajemen', 'tbl_pengeluaran_other.total', 'tbl_pengeluaran_other.notes', 'tbl_pengeluaran_other.deskripsi_pengeluaran', 'tbl_pengeluaran_other.id', 'tbl_pengeluaran_other.is_entry', 'users.first_name', 'users.last_name', 'business_locations.name AS cabang', 'tbl_pegawai.nama_pegawai');
        
        if ($request->input('location_id')) {
            $pengeluaran->where('tbl_pengeluaran_other.location_id', $request->location_id);   
        }else if ($user->location_id != null) {
            $pengeluaran->where('tbl_pengeluaran_other.location_id', $user->location_id);
        }
        if ($type == 1) {
            $pengeluaran->where('notes', 'Pengeluaran Manajemen');
        }else if ($type == 2) {
            $pengeluaran->where('notes', 'Pengeluaran Sewa');
        }else{
            $pengeluaran->whereNotIn('notes', ['Pengeluaran Sewa', 'Pengeluaran Manajemen']);
        }

        if ($request->input('tipe_manajemen')) {
            $pengeluaran->where('tbl_pengeluaran_other.tipe_manajemen', $request->tipe_manajemen);   
        }

        if ($request->input('date')) {
            $date_range = $request->input('date');
            $date_range_array = explode('~', $date_range);
            $start_date = date('Y-m-d', strtotime($date_range_array[0]));
            $end_date = date('Y-m-d', strtotime($date_range_array[1]));
            $pengeluaran->where('tbl_pengeluaran_other.tanggal', '>=', $start_date);
            $pengeluaran->where('tbl_pengeluaran_other.tanggal', '<=', $end_date);
        }
        $pengeluaran->get();
        $datatable = Datatables::of($pengeluaran)
            ->addColumn(
                'amount',
                function ($row) {
                    if ($row->is_entry == 1) {
                        return $row->total;
                    }else{
                        return (0 - $row->total);
                    }
                }
            )->addColumn(
                'action',
                function ($row) {
                    $html = '' ;     
                    if (auth()->user()->can("spend.delete")) {
                        if ($row->notes != 'Deposit Pegawai') {
                                # code...
                        $html .= '<a href="' . action('SellPosController@deletePengeluaranDt', $row->id) . '" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></a>';
                        }       
                    }
                    return $html;
                }
            )->filterColumn('first_name', function($query, $keyword) {
                $query->where('users.first_name', 'like', '%'.$keyword.'%');
                $query->orWhere('users.last_name', 'like', '%'.$keyword.'%');
            })->filterColumn('cabang', function($query, $keyword) {
                $query->where('business_locations.name', 'like', '%'.$keyword.'%');
            }) 
            ->make(true);
            
        return $datatable;
    }
    public function deletePengeluaranDt($id){
        $data=DB::table('tbl_pengeluaran_other')->where('id', $id)->first();
        $data1=$data;
        if ($data != null) {
            $data=DB::table('tbl_pengeluaran_other')->where('id', $id)->delete();
            DB::table('tbl_trx_akuntansi')->where('id_pengeluaran_dt', $id)->delete();
            $output = ['success' => 1,
                            'msg' => 'Data berhasil dihapus'
                        ];
            if ($data1->notes == 'Pengeluaran Manajemen') {
                return redirect()
                            ->action('SellPosController@listPengeluaranOther')
                            ->with('status', $output);
            }else if ($data1->notes == 'Pengeluaran Sewa'){
                return redirect()
                            ->action('SellPosController@listSewa')
                            ->with('status', $output);
            }else{
                return redirect()
                            ->action('SellPosController@listDeposit')
                            ->with('status', $output);
            }
        }else{
            $output = ['success' => 0,
                            'msg' => 'Data tidak ditemukan'
                        ];
            Request::server('HTTP_REFERER');
        }
    }
    public function test()
    {
        $stok = 20;
        $limit = 6;
        $stokLimit = $stok-$limit;
        $kebutuhan = 3;
        $stokProduct = floor($stokLimit/$kebutuhan);
        echo $stokProduct;
    }
    public function cekAvabilityStok()
    {
        $selectedProduct = request()->input('selected_product');
        $arrProduct = explode(',',$selectedProduct); //variation_id

        $getQtyProduk = request()->input('qty');
        $qtyProduk = explode(',',$getQtyProduk);

        $getProdukId = \DB::table('variations')->select('product_id')->where('id',request()->input('variation_id'))->first();

        $productId = $getProdukId->product_id;
        $locationId = request()->input('location_id');

        $bahanProduk = DB::table('tb_bahan_product as bp')
        ->select('bp.id_bahan','bp.kebutuhan','sb.stok','b.limit_pemakaian')
        ->join('tb_bahan as b','bp.id_bahan','b.id_bahan')
        ->join('tb_stok_bahan as sb','b.id_bahan','sb.id_bahan')
        ->where('bp.product_id',$productId)
        ->where('sb.location_id',$locationId)
        ->get();    
        $bahan = [];
        foreach ($bahanProduk as $key => $value) {
            $bahan[$value->id_bahan] = [
                'kebutuhan' => $value->kebutuhan,
                'stok' => $value->stok,
                'stok_temp' => $value->stok,
                'limit_pemakaian' => $value->limit_pemakaian,
            ];
        }

        foreach ($arrProduct as $key => $value) {
            $getBahanProduk = DB::table('variations as v')->select('bp.*')->join('tb_bahan_product as bp','v.product_id','bp.product_id')->where('v.id',$value)->get();

            foreach ($getBahanProduk as $i => $v) {
                if(isset($bahan[$v->id_bahan])){
                    $bahan[$v->id_bahan]['stok_temp']-=($v->kebutuhan*$qtyProduk[$key]);
                }
            }
        }

        $isAdd = true;
        foreach ($bahan as $id_bahan => $value) {
            $cekStok = $value['stok_temp'] - $value['kebutuhan'];
            if($value['stok_temp']<$value['limit_pemakaian']){
                $isAdd = false;
                break;
            }
        }
        echo $isAdd;
    }
}
