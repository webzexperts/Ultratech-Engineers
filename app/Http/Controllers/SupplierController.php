<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierDetails;
use App\Models\Admin;
use App\Models\PurchaseOrder;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DataTables;
use Date;
use Maatwebsite\Excel\Facades\Excel;

class SupplierController extends Controller
{
    public function getSupplierData()
    {
        $suppliers = Supplier::orderBy('supplier_name', 'ASC')->get();
        if($suppliers)
        {
            return response()->json([
                'suppliers' => $suppliers,
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
        return view('manage.manage-supplier');
    }

    public function index(Supplier $supplier,Request $request,DataTables $dataTables)
    {
        $supplier_data = Supplier::select([
            'suppliers.id',
            'suppliers.supplier_name',
            'suppliers.address',
            'suppliers.pincode',
            'suppliers.msme_reg_no',
            'suppliers.TAN',
            'suppliers.phone_no',
            'suppliers.email_id',
            'suppliers.web_address',
            'suppliers.GSTIN',
            'suppliers.PAN',
            'suppliers.payment_terms',
            'countries.country_name',
            'states.state',
            'states.state_code',
            'cities.city',
            'suppliers.created_on',
            'suppliers.created_by',
            'suppliers.last_by',
            'suppliers.last_on'
        ])
        ->leftJoin('cities','cities.id','=','suppliers.city_id')
        ->leftJoin('states','states.id','=','cities.state_id')
        ->leftJoin('countries','countries.id','=','states.country_id');
        $dataTable =DataTables::of($supplier_data)
        ->editColumn('supplier_name', function($supplier_data){
            return ucfirst($supplier_data->supplier_name);
        })
        ->editColumn('city_name', function($supplier_data){
            return ucfirst($supplier_data->city);
        })
        ->editColumn('country_name', function($supplier_data){
            return ucfirst($supplier_data->country_name);
        })
        ->editColumn('state_name', function($supplier_data){
            return ucfirst($supplier_data->state_name);
        })
        ->addColumn('options',function($supplier_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("supplier", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_supplier"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("supplier", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'suppliers');
        return $dataTable
        ->rawColumns(['options','supplier_name','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'supplier_name' =>'required|max:255|unique:suppliers',
            'address'       => 'max:255',
            'city_id'       => 'required',
        ],
        [
            'supplier_name.required'     => 'Enter Supplier',
            'supplier_name.unique'       => 'The Supplier Has Already Been Taken',
            'city_id.required'           => 'Please Select City',
            'address.max'                => 'Maximum 255 charactes Allowed',
        ]);

        DB::beginTransaction();
        try
        {
            $supplier_data = Supplier::create([
                'supplier_name'          => $request->supplier_name,
                'address'                => $request->address,
                'city_id'                => $request->city_id,
                'pincode'                => $request->pincode,
                'phone_no'               => $request->phone_no,
                'email_id'               => $request->email_id,
                'web_address'            => $request->web_address,
                'GSTIN'                  => $request->GSTIN,
                'PAN'                    => $request->PAN,
                'TAN'                    => $request->TAN,
                'msme_reg_no'            => $request->msme_reg_no,
                'payment_terms'          => $request->payment_terms,
                'company_id'             => Auth::user()->company_id,
                'created_on'             => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'             => Auth::user()->id
            ]);

            if($supplier_data->save())
            {

                if(isset($request->contact_data) && !empty($request->contact_data))
                {
                    $convertJson = json_decode($request->contact_data, true);
                    foreach($convertJson as $ctKey => $ctVal)
                    {
                        if($ctVal != null)
                        {
                            $contact_data=  SupplierDetails::create([
                                'sup_id' => $supplier_data->id,
                                'contact_person' => isset($ctVal['contact_person']) ? $ctVal['contact_person'] : "",
                                'phone_no' => isset($ctVal['phone_no']) ? $ctVal['phone_no'] : "",
                                'email_id' => isset($ctVal['email_id']) ? $ctVal['email_id'] : "",
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
        $supplier_data = Supplier::select([
            'suppliers.*',
            'countries.country_name',
            'states.state_code',
            'states.state'
        ])
        ->leftJoin('cities','cities.id','=','suppliers.city_id')
        ->leftJoin('states','states.id','=','cities.state_id')
        ->leftJoin('countries','countries.id','=','states.country_id')
        ->where('suppliers.id','=',$request->id)->first();
        
        $contact_data = SupplierDetails::where('sup_id', $request->id)->get();
        // dd($contact_data);
        foreach($contact_data as $key=>$val) {
            $usage = DB::select('CALL kind_attention_used_list(?)', [$val->supd_details_id]);
            if(!empty($usage)) {
                $val->in_use = true;
            } else {
                $val->in_use = false;
            }
        }
        // dd($contact_data);
        if($supplier_data)
        {
            return response()->json([
                'supplier' => $supplier_data,
                'contact_data' => $contact_data,
                'response_code' => '1',
                'response_message' => '',
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists.',
            ]);
        }
    }

    public function update(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'supplier_name' =>['required','max:255',Rule::unique('suppliers')->ignore($request->id, 'id')],
            'address' => 'max:255',
            'city_id' => 'required'
        ],
        [
            'supplier_name.required'     => 'Enter Supplier',
            'supplier_name.unique'       => 'The Supplier Has Already Been Taken',
            'city_id.required'           => 'Please Select City',
            'address.max'                => 'Maximum 255 charactes Allowed',
        ]);

        DB::beginTransaction();

        try
        {
            $supplier = Supplier::where('id','=',$request->id)->update([
                'supplier_name'          => $request->supplier_name,
                'address'                => $request->address,
                'city_id'                => $request->city_id,
                'pincode'                => $request->pincode,
                'phone_no'               => $request->phone_no,
                'email_id'               => $request->email_id,
                'web_address'            => $request->web_address,
                'GSTIN'                  => $request->GSTIN,
                'PAN'                    => $request->PAN,
                'TAN'                    => $request->TAN,
                'msme_reg_no'            => $request->msme_reg_no,
                'payment_terms'          => $request->payment_terms,
                'last_on'                => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by'                => Auth::user()->id
            ]);

            if($supplier)
            {
        // dd($request->all());

                $oldContacts = SupplierDetails::where('sup_id','=',$request->id)->get();
                $getCustomerId = Supplier::where('id', $request->id)->pluck('id')->first();

                $oldContactsData = [];
                if($oldContacts != null)
                {
                    $oldContactsData = $oldContacts->toArray();
                }

                $contactDetails = $request->only('contact_data');
                $contactDetails['contact_data'] = json_decode($contactDetails['contact_data'],true);
                if(isset($oldContactsData) && !empty($oldContactsData))
                {
                    foreach($oldContactsData as $oldCtKey => $oldCtVal)
                    {
                        if(isset($contactDetails['contact_data'][$oldCtKey]) && $contactDetails['contact_data'][$oldCtKey] != null)
                        {
                            $contact_data_updated =  SupplierDetails::where('sup_id','=',$request->id)->where('supd_details_id','=',$oldCtVal['supd_details_id'])->update([
                                'contact_person' => isset($contactDetails['contact_data'][$oldCtKey]['contact_person']) ? $contactDetails['contact_data'][$oldCtKey]['contact_person'] : "",
                                'phone_no' => isset($contactDetails['contact_data'][$oldCtKey]['phone_no']) ? $contactDetails['contact_data'][$oldCtKey]['phone_no'] : "",
                                'email_id' => isset($contactDetails['contact_data'][$oldCtKey]['email_id']) ? $contactDetails['contact_data'][$oldCtKey]['email_id'] : "",
                                
                            ]);
                            unset($oldContactsData[$oldCtKey]);
                            unset($contactDetails['contact_data'][$oldCtKey]);
                        }
                    }
    
                    if(isset($oldContactsData) && !empty($oldContactsData))
                    {
                        foreach($oldContactsData as $oldCtKey => $oldCtVal)
                        {
                            SupplierDetails::where('supd_details_id','=',$oldCtVal['supd_details_id'])->delete();
                        }
                    }
                }

                if(isset($contactDetails['contact_data']) && !empty($contactDetails['contact_data']))
                {
                    foreach($contactDetails['contact_data'] as $ctKey => $ctVal)
                    {
                        if($ctVal != null)
                        {
                            $contact_data=  SupplierDetails::create([
                                'sup_id' =>  $getCustomerId,
                                'contact_person' => isset($ctVal['contact_person']) ? $ctVal['contact_person'] : "",
                                'phone_no' => isset($ctVal['phone_no']) ? $ctVal['phone_no'] : "",
                                'email_id' => isset($ctVal['email_id']) ? $ctVal['email_id'] : "",
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
            // $po_data = PurchaseOrder::where('po_supplier_id','=',$request->id)->get(); 
            // if($po_data->isNotEmpty())
            // {
            //     return response()->json([
            //         'response_code' => '0',
            //         'response_message' => "You Can't Delete, Supplier Is Used In Purchase Order.",
            //     ]);
            // }

            $usage = DB::select('CALL supplier_master_used_list(?)', [$request->id]);

            if (!empty($usage)) {
                $message = "You Can't Delete, Supplier Is Used In ". $usage[0]->table_name.".";
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            }

            Supplier::destroy($request->id);
            SupplierDetails::where('sup_id',$request->id)->delete();
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
                $error_msg = "This Is Used Somewhere, You Can't Delete.";
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

    public function existsSupplier(Request $request)
    {
        if($request->term != "")
        {
            $Supplier = Supplier::select('supplier_name')->where('supplier_name', 'LIKE', '%'.$request->term.'%')->groupBy('supplier_name')->get();
            if($Supplier != null)
            {
                $output = '<ul class="list-group" style="display: block; position: relative;" tabindex="-1">';
                foreach($Supplier as $dsKey)
                {
                    $output .= '<li parent-id="supplier_name" list-id="supplier_name_list" class="list-group-item" tabindex="0">'.$dsKey->supplier_name.'</li>';
                }
                $output .= '</ul>';

                return response()->json([
                    'supplierList' => $output,
                    'response_code' => 1,
                ]);
            }
            else
            {
                return response()->json([
                    'response_message' => 'No Supplier available',
                    'response_code' => 0,
                ]);
            }
        }
        else
        {
            return response()->json([
                'supplierList' => '',
                'response_code' => 1,
            ]);
        }
    }

    public function importviewSupplier()
    {
        return view('import_supplier');
    }

    public function importSupplier()
    {
        Excel::import(new ImportSupplier,request()->file('file'));
        return redirect()->back();
    }
}