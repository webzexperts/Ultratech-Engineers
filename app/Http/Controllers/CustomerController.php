<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\City;
use App\Models\CustomerContacts;
use App\Models\Inquiry;
use App\Models\Admin;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DataTables;
use Date;
use App\Imports\ImportCustomer;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    public function getCustomerData()
    {
        // $customers = Customer::all();
        $customers = Customer::orderBy('customer', 'ASC')->get();
        if($customers)
        {
            return response()->json([
                'customers' => $customers,
                'response_code' => '1'
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'No Data Avilable',
            ]);
        }  
    }

    public function manage()
    {
        return view('manage.manage-customer');
    }

    public function index(Customer $Customer,Request $request,DataTables $dataTables)
    {
        $customer_data = Customer::select([
            'customers.customer',
            'customers.customer_code',
            'cities.city',
            'customers.id',
            'countries.country_name',
            'states.state',
            'customers.phone_no',
            'customers.email',
            'customers.web_address',
            'customers.gstin',
            'customers.credit_days',
            'customers.created_on',
            'customers.created_by',
            'customers.last_by',
            'customers.last_on'
        ])
        ->leftJoin('cities','cities.id','=','customers.city_id')
        ->leftJoin('states','states.id','=','cities.state_id')
        ->leftJoin('countries','countries.id','=','states.country_id');
        $dataTable =DataTables::of($customer_data)
        ->editColumn('customer', function($customer_data) { 
            return Str::limit($customer_data->customer, 50);
        })
        ->editColumn('city', function($customer_data) { 
            return Str::limit($customer_data->city, 50);
        })
        ->editColumn('country_name', function($customer_data) { 
            return Str::limit($customer_data->country_name, 50);
        })
        ->editColumn('state', function($customer_data) { 
            return Str::limit($customer_data->state, 50);
        })
        ->addColumn('options',function($customer_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("customer", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_customer"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("customer", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'customers');
        return $dataTable
        ->rawColumns(['options','customer','city','country','state','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer'=>'required|max:255|unique:customers',
            //'customer_code'=>'required|max:255',
            'contact_person.*'=>'required|max:255',
            'address' => 'max:255',
            'city_id' => 'required'
        ],
        [
            'customer.required' => 'Please Enter Customer',
            'customer.max' => 'Maximum 255 Characters Allowed',
            'contact_person.*.required' => 'Please Enter Contact Person',
            'contact_person.*.max' => 'Maximum 255 Characters Allowed',
            //'customer_code.required' => 'Please Enter Customer Code',
            //'customer_code.max' => 'Maximum 255 Characters Allowed',
            'city_id.required' => 'Please Select City',
            'address.max' => 'Maximum 255 Charactes Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $existNumber = Customer::where('customer_code', $request->customer_code)->first();
            if($existNumber)
            {
                $customer_code = Customer::max('customer_code');
                if($customer_code != null)
                {
                    $customer_code++;
                }
                else
                {
                    $customer_code = 1;
                }
            }
            else
            {
                $customer_code = $request->customer_code;
            }

            $customer_data = Customer::create([
                //'customer_code' => $customer_code,
                'customer'    => $request->customer,
                'city_id'     => $request->city_id,
                'pin_code'    => $request->pincode,
                'phone_no'    => $request->mobile_no,
                'email'       => $request->email,
                'web_address' => $request->web_address,
                'gstin'       => $request->gstin,
                'pan'         => $request->pan,
                'tan'         => $request->tan,
                'msme_reg_no' => $request->msme_reg_no,
                'credit_days' => $request->credit_days,
                //'payment_terms' => $request->payment_terms,
                'address'     => $request->address,
                'company_id'  => Auth::user()->company_id,
                'created_on'  => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'  => Auth::user()->id
            ]);

            if($customer_data->save())
            {
                if(isset($request->contacts) && !empty($request->contacts))
                {
                    $convertJson = json_decode($request->contacts, true);
                    foreach($convertJson as $ctKey => $ctVal)
                    {
                        if($ctVal != null)
                        {
                            $contact_data=  CustomerContacts::create([
                                'customer_id' => $customer_data->id,
                                'contact_person' => isset($ctVal['contact_person']) ? $ctVal['contact_person'] : "",
                                'contact_designation' => isset($ctVal['contact_designation']) ? $ctVal['contact_designation'] : "",
                                'contact_phone_no' => isset($ctVal['contact_phone_no']) ? $ctVal['contact_phone_no'] : "",
                                //'contact_mobile_no' => isset($ctVal['contact_mobile_no']) ? $ctVal['contact_mobile_no'] : "",
                                'contact_email' => isset($ctVal['contact_email']) ? $ctVal['contact_email'] : "",
                                'send_sms_for' => isset($ctVal['send_sms_for']) ?$ctVal['send_sms_for']: "",
                                'send_email_for' => isset($ctVal['send_email_for']) ? (is_array($ctVal['send_email_for']) ? implode(',', $ctVal['send_email_for']) : $ctVal['send_email_for']) : "",
                            ]);
                        }
                    }
                }

                DB::commit();
                return response()->json([
                    'response_code' => '1',
                    'response_message' => getResponseMessage('store_success'),
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('store_error'),
                ]);
            }
        }
        catch(\Exception $e)
        {
            report($e);
            DB::rollBack();
            return response()->json([
                'response_code' => '0',
                'response_message' => getResponseMessage('store_error'),
                'original_error' => $e->getMessage()
            ]);
        }
    }

    public function edit(Request $request)
    {
        $customer_data = Customer::select([
            'customers.*',
            'countries.country_name',
            'states.state_code',
            'states.state'
        ])
        ->leftJoin('cities','cities.id','=','customers.city_id')
        ->leftJoin('states','states.id','=','cities.state_id')
        ->leftJoin('countries','countries.id','=','states.country_id')
        ->where('customers.id','=',$request->id)->first();

        // $contact_data = CustomerContacts::where('customer_id','=',$request->id)->get();

        // $contact_data = CustomerContacts::where('customer_id','=',$request->id)->get();
        $isUsed = false;
        // dd($request->id);
        $contact_data = CustomerContacts::where('customer_id', $request->id)->get();
        foreach ($contact_data as $contact)
        {
            $isUsedinq = Inquiry::where('inq_kind_attn_id', $contact->id)->exists();
            if($isUsedinq == true)
            {
                $contact->in_use = $isUsedinq;
                
            }
            else{
                $contact->in_use = $isUsed;

            }
        }

        // if($contact_data != null)
        // {
        //     foreach($contact_data as $cnKey => $cnVal)
        //     {
        //         $cnVal->send_email_for = explode(',',$cnVal->send_email_for);
        //     }
        // }

        if($customer_data)
        {
            return response()->json([
                'customer' => $customer_data,
                'contact' => $contact_data,
                'response_code' => '1',
                'response_message' => '',
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'customer'=>['required','max:255',Rule::unique('customers')->ignore($request->id, 'id')],   
            //'customer_code'=>['required','max:255',Rule::unique('customers')->ignore($request->id, 'id')],
            'contact_person.*'=>['required','max:255'],
            'address' => 'max:255',
            'city_id' => 'required'
        ],
        [
            'customer.required' => 'Please Enter Customer',
            'customer.max' => 'Maximum 255 Characters Allowed',
            'contact_person.*.required' => 'Please Enter Contact Person',
            'contact_person.*.max' => 'Maximum 255 Characters Allowed',
            //'customer_code.required' => 'Please Enter Customer Code',
            //'customer_code.max' => 'Maximum 255 Characters Allowed',
            'city_id.required' => 'Please Select City',
            'address.max' => 'Maximum 255 Charactes Allowed',
        ]);

        DB::beginTransaction();

        try
        {
            $customer_data = Customer::where('id','=',$request->id)->update([
                //'customer_code' => $request->customer_code,
                'customer'    => $request->customer,
                'address'     => $request->address,
                'city_id'     => $request->city_id,
                'pin_code'    => $request->pincode,
                'phone_no'    => $request->mobile_no,
                'email'       => $request->email,
                'web_address' => $request->web_address,
                'gstin'       => $request->gstin,
                'pan'         => $request->pan,
                'tan'         => $request->tan,
                'msme_reg_no' => $request->msme_reg_no,
                'credit_days' => $request->credit_days,
                //'payment_terms' => $request->payment_terms,
                'last_on'     => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by'     => Auth::user()->id
            ]);

            if($customer_data)
            {
                $oldContacts = CustomerContacts::where('customer_id','=',$request->id)->get();
                $getCustomerId = Customer::where('id', $request->id)->pluck('id')->first();

                $oldContactsData = [];
                if($oldContacts != null)
                {
                    $oldContactsData = $oldContacts->toArray();
                }

                $contactDetails = $request->only('contacts');
                $contactDetails['contacts'] = json_decode($contactDetails['contacts'],true);
                if(isset($oldContactsData) && !empty($oldContactsData))
                {
                    foreach($oldContactsData as $oldCtKey => $oldCtVal)
                    {
                        if(isset($contactDetails['contacts'][$oldCtKey]) && $contactDetails['contacts'][$oldCtKey] != null)
                        {
                            $contact_data_updated =  CustomerContacts::where('customer_id','=',$request->id)->where('id','=',$oldCtVal['id'])->update([
                                'contact_person' => isset($contactDetails['contacts'][$oldCtKey]['contact_person']) ? $contactDetails['contacts'][$oldCtKey]['contact_person'] : "",
                                'contact_designation' => isset($contactDetails['contacts'][$oldCtKey]['contact_designation']) ? $contactDetails['contacts'][$oldCtKey]['contact_designation']: "",
                                'contact_phone_no' => isset($contactDetails['contacts'][$oldCtKey]['contact_phone_no']) ? $contactDetails['contacts'][$oldCtKey]['contact_phone_no'] : "",
                                //'contact_mobile_no' => isset($contactDetails['contacts'][$oldCtKey]['contact_mobile_no']) ? $contactDetails['contacts'][$oldCtKey]['contact_mobile_no'] : "",
                                'contact_email' => isset($contactDetails['contacts'][$oldCtKey]['contact_email']) ? $contactDetails['contacts'][$oldCtKey]['contact_email'] : "",
                                'send_email_for' => isset($contactDetails['contacts'][$oldCtKey]['send_email_for']) ? $contactDetails['contacts'][$oldCtKey]['send_email_for'] : "",
                            ]);
                            unset($oldContactsData[$oldCtKey]);
                            unset($contactDetails['contacts'][$oldCtKey]);
                        }
                    }
    
                    if(isset($oldContactsData) && !empty($oldContactsData))
                    {
                        foreach($oldContactsData as $oldCtKey => $oldCtVal)
                        {
                            CustomerContacts::where('id','=',$oldCtVal['id'])->delete();
                        }
                    }
                }

                if(isset($contactDetails['contacts']) && !empty($contactDetails['contacts']))
                {
                    foreach($contactDetails['contacts'] as $ctKey => $ctVal)
                    {
                        if($ctVal != null)
                        {
                            $contact_data=  CustomerContacts::create([
                                'customer_id' =>  $getCustomerId,
                                'contact_person' => isset($ctVal['contact_person']) ? $ctVal['contact_person'] : "",
                                'contact_designation' => isset($ctVal['contact_designation']) ? $ctVal['contact_designation'] : "",
                                'contact_phone_no' => isset($ctVal['contact_phone_no']) ? $ctVal['contact_phone_no'] : "",
                                //'contact_mobile_no' => isset($ctVal['contact_mobile_no']) ? $ctVal['contact_mobile_no'] : "",
                                'contact_email' => isset($ctVal['contact_email']) ? $ctVal['contact_email'] : "",
                                'send_sms_for' => isset($ctVal['send_sms_for']) ? $ctVal['send_sms_for'] : "0",
                                'send_email_for' => isset($ctVal['send_email_for']) ? (is_array($ctVal['send_email_for']) ? implode(',', $ctVal['send_email_for']) : $ctVal['send_email_for']) : "",
                            ]);
                        }
                    }
                }

                DB::commit();
                return response()->json([
                    'response_code' => '1',
                    'response_message' => getResponseMessage('update_success'),
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }
        }
        catch(\Exception $e)
        {
            report($e);
            DB::rollBack();
            return response()->json([
                'response_code' => '0',
                'response_message' => getResponseMessage('update_error'),
                'original_error' => $e->getMessage()
            ]);
        }
    }

    public function destroy(Request $request)
    {
        try
        {
            // $inqiry_customer_id = Inquiry::where('inq_customer_id',$request->id)->get();
            // if($inqiry_customer_id->isNotEmpty())
            // {
            //     return response()->json([
            //         'response_code' => '0',
            //         'response_message' => "You Can't Delete, Customer Is Used In Inquiry.",
            //     ]);
            // }

            $usage = DB::select('CALL customer_master_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                $message = "You Can't Delete, Customer Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            Customer::destroy($request->id);
            CustomerContacts::where('customer_id','=',$request->id)->delete();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        }
        catch(\Exception $e)
        {
            report($e);
            if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451)
            {
                $error_msg = "This Is Used Somewhere, You Can't Delete";
            }
            else
            {
                $error_msg = getResponseMessage('delete_error');
            }

            return response()->json([
                'response_code' => '0',
                'response_message' => $error_msg,
            ]);
        }
    }

    public function getRelationValues(Request $request)
    {
        $relData = City::select([
            'states.country_id',
            'countries.country_name',
            'states.state',
            'states.state_code'
        ])
        ->leftJoin('states','states.id','=','cities.state_id')
        ->leftJoin('countries','countries.id','=','states.country_id')
        ->where('cities.id','=',$request->city_id)
        ->first();

        if($relData != null)
        {
            return response()->json([
                'response_code' => '1',
                'relation_data' =>  $relData
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'relation_data' =>  '',
                'response_message' => 'No Relation Data Available!'
            ]);
        }
    }

    public function existsCustomer(Request $request)
    {
        if($request->term != "")
        {
            $fdCustomer = Customer::select('customer')->where('customer', 'LIKE', '%'.$request->term.'%')->groupBy('customer')->get();
            if($fdCustomer != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdCustomer as $dsKey)
                {
                    $output .= '<li parent-id="customer" list-id="customer_list" class="list-group-item" tabindex="0">'.$dsKey->customer.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'customerList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Customer available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'customerList' => '',
                'response_code' => 1,
            ]);
        }
    }

    public function existsPaymentTerms(Request $request)
    {
        if($request->term != "")
        {
            $fdPaymentTerms = Customer::select('payment_terms')->where('payment_terms', 'LIKE', '%'.$request->term.'%')->groupBy('payment_terms')->get();
            if($fdPaymentTerms != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdPaymentTerms as $pmKey)
                {
                    $output .= '<li parent-id="payment_terms" list-id="payment_terms_list" class="list-group-item" tabindex="0">'.$pmKey->payment_terms.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'paymentTermsList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Payment terms available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'paymentTermsList' => '',
                'response_code' => 1,
            ]);
        }
    }

    public function existsDesignation(Request $request)
    {
        if($request->term != "")
        {
            $fdDesignation = CustomerContacts::select('contact_designation')->where('contact_designation', 'LIKE', '%'.$request->term.'%')->groupBy('contact_designation')->get();
            if($fdDesignation != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($fdDesignation as $dsKey)
                {
                    $output .= '<li parent-id="contact_designation" list-id="contact_designation_list" class="list-group-item" tabindex="0">'.$dsKey->contact_designation.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'designationList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Designation available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'designationList' => '',
                'response_code' => 1,
            ]);
        }  
    }

    public function importviewCustomer()
    {
        return view('import_customer');
    }

    public function importCustomer()
    {        
        Excel::import(new ImportCustomer,request()->file('file'));
        return redirect()->back();
    }

    public function getCustomerCode()
    {
        $new_code = Customer::max('customer_code');
        if ($new_code != null)
        {
            $new_code++;
        }
        else
        {
            $new_code = 1;
        }

        return response()->json([
            'customer_code' => $new_code,
            'response_code' => 1,
        ]);
    }
}