<?php

namespace App\Http\Controllers;

use App\Discount;
use App\Promo;
use App\Brands;
use App\Category;
use App\BusinessLocation;
use App\User;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\Utils\Util;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class PromoController extends Controller
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
    public function index()
    {
        $user_id = request()->session()->get('user.id');
        $user = User::where('id', $user_id)->first();
        $location_id = $user->location_id;
        
        if (request()->ajax()) {
            $business_id = request()->session()->get('user.business_id');
            $query=Promo::join('business_locations', 'business_locations.id', '=', 'promos.location_id')
            				->select('promos.*', 'business_locations.name');
            if ($location_id != null) {
                $query->where('promos.location_id', $location_id);
            }
            $promo=$query->get();

            return Datatables::of($promo)
                ->addColumn(
                    'action',
                    '<a href="{{action(\'PromoController@edit\', [$id])}}" class="btn btn-xs btn-primary" ><i class="glyphicon glyphicon-edit"></i> @lang("messages.edit")</a>
                        &nbsp;
                        <button data-href="{{action(\'PromoController@destroy\', [$id])}}" class="btn btn-xs btn-danger delete_promo_button hide"><i class="glyphicon glyphicon-trash"></i> @lang("messages.delete")</button>'
                )
                ->make(true);
        }
        return view('promo.index');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // if (!auth()->user()->can('discount.access')) {
        //     abort(403, 'Unauthorized action.');
        // }
        $user_id = request()->session()->get('user.id');
        $user = User::where('id', $user_id)->first();
        $location_id = $user->location_id;

        $business_id = request()->session()->get('user.business_id');

        $categories = Category::where('business_id', $business_id)
                            ->where('parent_id', 0)
                            ->pluck('name', 'id');

        $brands = Brands::forDropdown($business_id);

        $locations = BusinessLocation::forDropdown($business_id);
        if ($location_id != null) {
            foreach ($locations as $key => $value) {
                if ($key != $location_id) {
                    unset($locations[$key]);
                }
            }    
        }
        
        return view('promo.create')
                ->with(compact('categories', 'brands', 'locations'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // if (!auth()->user()->can('discount.access')) {
        //     abort(403, 'Unauthorized action.');
        // }

        try {
            $business_id = $request->session()->get('user.business_id');
            $input['promo_name'] = $request->input('name');
            $input['location_id'] = $request->input('location_id');
            $input['promo_type_diskon'] = $request->input('discount_type');
            $input['promo_diskon'] = $request->input('discount_amount');
            $input['promo_status'] = 1;
            $input['promo_limit'] = $request->input('limit');
            $input['promo_sk_limit'] = $request->input('limit_sk');
            $input['promo_start'] = $request->has('starts_at') ? date('Y-m-d', strtotime($request->input('starts_at'))) : null;
            $input['promo_end'] = $request->has('ends_at') ? date('Y-m-d', strtotime($request->input('ends_at'))) : null;
            
            $promo=Promo::create($input);

            $output = ['success' => true,
                            'msg' => __("lang_v1.added_success")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
            
            $output = ['success' => false,
                            'msg' => __("messages.something_went_wrong")
                        ];
        }

        return redirect('promo')->with('status', $output);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        // if (!auth()->user()->can('discount.access')) {
        //     abort(403, 'Unauthorized action.');
        // }
        $user_id = request()->session()->get('user.id');
        $user = User::where('id', $user_id)->first();
        $location_id = $user->location_id;

        // if (request()->ajax()) {
        $business_id = request()->session()->get('user.business_id');
        $promo = Promo::where('id', $id)->first();
        // $starts_at = $this->commonUtil->format_date($discount->starts_at->toDateTimeString(), true);
        $starts_at = date('d-m-Y', strtotime($promo->promo_start));
        $ends_at = date('d-m-Y', strtotime($promo->promo_end));
        // print_r($ends_at);exit();
        $categories = Category::where('business_id', $business_id)
                        ->where('parent_id', 0)
                        ->pluck('name', 'id');

        $brands = Brands::forDropdown($business_id);

        $locations = BusinessLocation::forDropdown($business_id);
        if ($location_id != null) {
            foreach ($locations as $key => $value) {
                if ($key != $location_id) {
                    unset($locations[$key]);
                }
            }    
        }
        
        return view('promo.edit')
            ->with(compact('promo', 'ends_at', 'starts_at', 'brands', 'categories', 'locations'));
        // }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $business_id = $request->session()->get('user.business_id');
            $input['promo_name'] = $request->input('name');
            $input['location_id'] = $request->input('location_id');
            $input['promo_type_diskon'] = $request->input('discount_type');
            $input['promo_diskon'] = $request->input('discount_amount');
            $input['promo_status'] = $request->input('status') ? 1 : 0;
            $input['promo_limit'] = $request->input('limit');
            $input['promo_sk_limit'] = $request->input('limit_sk');
            $input['promo_start'] = $request->has('starts_at') ? date('Y-m-d', strtotime($request->input('starts_at'))) : null;
            $input['promo_end'] = $request->has('ends_at') ? date('Y-m-d', strtotime($request->input('ends_at'))) : null;

            Promo::where('id', $id)->update($input);
            $output = ['success' => true,
                        'msg' => __("lang_v1.updated_success")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
        
            $output = ['success' => false,
                        'msg' => __("messages.something_went_wrong")
                    ];
        }

        return redirect('promo')->with('status', $output);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Discount  $discount
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        
        try {
            Promo::where('id', $id)->delete();
            $output = ['success' => true,
                        'msg' => __("lang_v1.deleted_success")
                        ];
        } catch (\Exception $e) {
            \Log::emergency("File:" . $e->getFile(). "Line:" . $e->getLine(). "Message:" . $e->getMessage());
        
            $output = ['success' => false,
                        'msg' => __("messages.something_went_wrong")
                    ];
        }

        return $output;
        
    }
}
