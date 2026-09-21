<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ItemReturnSlip;
use App\Models\ItemReturnSlipDetails;
use App\Models\Item;
use App\Models\LPTChemical;
use App\Models\MaterialMpt;
use App\Models\EquipmentMPT;
use App\Models\EquipmentUT;
use App\Models\ProbeUT;
use App\Models\ItemIssueSlip;
use App\Models\ItemIssueSlipDetails;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DataTables;
use Date;

class ItemReturnSlipController extends Controller
{
    public function manage()
    {
        return view('manage.manage-item_return_slip');
    }

    public function index(ItemReturnSlip $ItemReturnSlip, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $irs_data = ItemReturnSlipDetails::select([
            'item_return_slip.irs_id',
            'item_return_slip.irs_sequence',
            'item_return_slip.irs_number',
            'item_return_slip.irs_date',
            'item_return_slip.irs_type_id',
            'item_return_slip.irs_special_note',
            'item_return_slip.created_on',
            'item_return_slip.created_by',
            'item_return_slip.last_by',
            'item_return_slip.last_on',
            'admin.person_name as user_name',
            'item.item_name',
            'item.item_type',
            'reason.reason_name',
            'unit.unit',
            'item_return_slip_details.irsd_sr_no_or_batch_no',
            'item_return_slip_details.irsd_return_qty',
            'item_return_slip_details.irsd_non_rerurnable_qty',
        ])
        ->leftJoin('item_return_slip','item_return_slip.irs_id','=','item_return_slip_details.irsd_irs_id')
        ->leftJoin('item','item.id','=','item_return_slip_details.irsd_item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('admin','admin.id','=','item_return_slip.irs_employee_id')
        ->leftJoin('reason','reason.id','=','item_return_slip_details.irsd_reason_id')
        ->where('item_return_slip.year_id', $year_data->id);
        $dataTable = DataTables::of($irs_data)
        ->editColumn('irs_date', function($irs_data){
            if ($irs_data->irs_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $irs_data->irs_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('item_return_slip.irs_date', function ($q, $k) {
            applyDate($q, $k, 'item_return_slip.irs_date');
        })

        ->addColumn('options',function($irs_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("item_return_slip", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_item_return_slip"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("item_return_slip", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'item_return_slip');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function getLatestItemReturnSlipNumber()
    {
        $modal  =  ItemReturnSlip::class;
        $sequence = 'irs_sequence';
        $prefix = 'RET';
        $sup_num_format = getLatestSequence($modal,$sequence,$prefix);   

        return response()->json([
            'response_code' => 1,
            'latest_no'     => $sup_num_format['format'],
            'number'        => $sup_num_format['isFound'],
        ]);
    }

    public function getBatchNoAndSrNoReturnSlipData(Request $request)
    {
        $irsd_batch_no_id = "";
        $irsd_sr_no_id = "";

        if($request->item_type_id == "lpt_chemical") {
            $irsd_batch_no_id = LPTChemical::where('lpt_chemical_id', $request->item_id)->select('lpt_id as irsd_batch_no_id','lpt_batch_no as sr_batch_name')->orderBy('lpt_batch_no', 'asc')->get();
        } else if($request->item_type_id == "mpt_chemical") {
            $irsd_batch_no_id = MaterialMpt::where('mm_material_id', $request->item_id)->select('mm_id as irsd_batch_no_id','mm_batch_no as sr_batch_name')->orderBy('mm_batch_no', 'asc')->get();
        } else if($request->item_type_id == "mpt_equipment") {
            $irsd_sr_no_id = EquipmentMPT::where('em_mpt_equipment_id', $request->item_id)->select('em_id as irsd_sr_no_id','em_serial_no as sr_no_name')->orderBy('em_serial_no', 'asc')->get();
        } else if($request->item_type_id == "ut_equipment") {
            $irsd_sr_no_id = EquipmentUT::where('eu_ut_equipment_id', $request->item_id)->select('eu_id as irsd_sr_no_id','eu_serial_no as sr_no_name')->orderBy('eu_serial_no', 'asc')->get();
        } else if($request->item_type_id == "ut_probe") {
            $irsd_sr_no_id = ProbeUT::where('pu_ut_probe_id', $request->item_id)->select('pu_id as irsd_sr_no_id','pu_serial_number as sr_no_name')->orderBy('pu_serial_number', 'asc')->get();
        }

        return response()->json([
            'irsd_batch_no_id' => $irsd_batch_no_id,
            'irsd_sr_no_id' => $irsd_sr_no_id,
            'response_code' => '1'
        ]);
    }

    public function getPendingSupplierForIRS(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();
        if(isset($request->id))
        {
            // $get_issue_return_employee =  User::select('id','name')->orderBy('name','asc')->get();
            $get_issue_return_employee =  Admin::select('id','person_name as user_name')->where('status','=',1)->orderBy('person_name','asc')->get();
            return response()->json([
                'response_code' => 1,
                'get_issue_return_employee'  => $get_issue_return_employee,
            ]);
        }

        if($request->irs_type_id == "From Issue")
        {
            // $get_issue_return_employee =  User::select('id','name')->orderBy('name','asc')->get();
            $get_issue_return_employee =  Admin::select('id','person_name as user_name')->where('status','=',1)->orderBy('person_name','asc')->get();
            // $get_issue_return_employee = PurchaseOrderDetails::select('suppliers.name','suppliers.id',
            //     //DB::raw("(SELECT purchase_order_details.pod_po_qty - (SELECT IFNULL(SUM(gid.grnd_qty),0) FROM grn_details AS gid WHERE gid.grnd_pod_id = purchase_order_details.pod_id)) as pend_po_qty"),
            //     DB::raw("(SELECT purchase_order_details.pod_po_qty -  (SELECT IFNULL(SUM(psid.po_sc_qty),0) FROM po_short_close AS psid  WHERE psid.po_sc_pod_id = purchase_order_details.pod_id) - (SELECT IFNULL(SUM(gid.grnd_qty),0) FROM grn_details AS gid WHERE gid.grnd_pod_id = purchase_order_details.pod_id)) as pend_po_qty"),
            // )
            // ->leftJoin('purchase_order','purchase_order.po_id', 'purchase_order_details.pod_po_id')
            // ->leftJoin('suppliers','suppliers.id', 'purchase_order.po_supplier_id')
            // ->whereIn('purchase_order.year_id',$yearIds)
            // ->having('pend_po_qty','>',0)
            // ->get();
            // $get_issue_return_employee = $get_issue_return_employee->unique(['id']);
            // $get_issue_return_employee = $get_issue_return_employee->values()->all();
        }
        else
        {
            // $get_issue_return_employee =  User::select('id','name')->orderBy('name','asc')->get();
            $get_issue_return_employee =  Admin::select('id','person_name as user_name')->where('status','=',1)->orderBy('person_name','asc')->get();
        }

        return response()->json([
            'response_code' => 1,
            'get_issue_return_employee'  => $get_issue_return_employee,
        ]);
    }

    public function store(Request $request)
    {
        $year_data = getCurrentYearData();
        $existNumber = ItemReturnSlip::where([['irs_sequence',  $request->irs_sequence],['irs_number',$request->irs_number],['year_id',$year_data->id]])->first();

        if($existNumber)
        {
            $latestNo = $this->getLatestItemReturnSlipNumber($request);
            $tmp =  $latestNo->getContent();
            $area = json_decode($tmp, true);
            $irs_number =   $area['latest_no'];
            $irs_sequence = $area['number'];
        }
        else
        {
            $irs_number   = $request->irs_number;
            $irs_sequence = $request->irs_sequence;
        }

        DB::beginTransaction();
        try
        {
            $irs_data =  ItemReturnSlip::create([
                'irs_number' => $irs_number ?? null,
                'irs_sequence' => $irs_sequence ?? null,
                'irs_date' => isset($request->irs_date) ? Date::createFromFormat('d/m/Y', $request->irs_date)->format('Y-m-d') : null,
                'irs_type_id' => $request->irs_type_id ?? null,
                'irs_employee_id' => $request->irs_employee_id ?? null,
                'irs_special_note' => $request->irs_special_note ?? null,
                'year_id' => $year_data->id,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id,
            ]);

            $irs_details_data = $request->irs_details_data = json_decode($request->irs_details_data, true);
            if(!empty($irs_details_data))
            {
                foreach($irs_details_data as $ctKey => $ctVal)
                {
                    if($ctVal != null)
                    {
                        $irs_details_data = ItemReturnSlipDetails::create([
                            'irsd_irs_id' => $irs_data->irs_id,
                            'irsd_iisd_id' => !empty($ctVal['irsd_iisd_id']) ? $ctVal['irsd_iisd_id'] : null,
                            'irsd_item_id' => !empty($ctVal['irsd_item_id']) ? $ctVal['irsd_item_id'] : null,
                            'irsd_sr_no_id' => !empty($ctVal['irsd_sr_no_id']) ? $ctVal['irsd_sr_no_id'] : null,
                            'irsd_batch_no_id' => !empty($ctVal['irsd_batch_no_id']) ? $ctVal['irsd_batch_no_id'] : null,
                            'irsd_sr_no_or_batch_no' => !empty($ctVal['irsd_sr_no_or_batch_no']) ? $ctVal['irsd_sr_no_or_batch_no'] : null,
                            'irsd_return_qty' => !empty($ctVal['irsd_return_qty']) ? $ctVal['irsd_return_qty'] : null,
                            'irsd_non_rerurnable_qty' => !empty($ctVal['irsd_non_rerurnable_qty']) ? $ctVal['irsd_non_rerurnable_qty'] : null,
                            'irsd_reason_id' => !empty($ctVal['irsd_reason_id']) ? $ctVal['irsd_reason_id'] : null,
                            'irsd_remark' => !empty($ctVal['irsd_remark']) ? $ctVal['irsd_remark'] : null,
                            'status' => 'Y',
                        ]);
                    }
                }
            }

            if($irs_data->save())
            {
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
        $irs_data =  DB::select('CALL item_return_slip_master(?)', [$request->id]);
        if(!empty($irs_data))
        {
            $irs_data = $irs_data[0];
            $irs_data->irs_date = $irs_data->irs_date != "" ? Date::createFromFormat('Y-m-d',  $irs_data->irs_date)->format('d/m/Y') : "";
        }

        $irs_details_data = DB::select('CALL item_return_slip_details(?)', [$request->id]);

        if($irs_details_data){
            foreach($irs_details_data as $dKey => $dVal)
            {
               $dVal->irsd_pending_qty = $dVal->irsd_pending_qty + $dVal->irsd_return_qty;
            }
            
        }

        if($irs_data)
        {
            return response()->json([
                'irs_data'     => $irs_data,
                'irs_details_data'     => $irs_details_data,
                'response_code'    => '1',
                'response_message' => '',
            ]);
        }
        else
        {
            return response()->json([
                'response_code'    => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try
        {
            $year_data = getCurrentYearData();
            $validated = $request->validate(['irs_sequence' => ['required', 'max:155', Rule::unique('item_return_slip')->where(function ($query) use ($request, $year_data) { $query->where('year_id', '=', $year_data->id);})->ignore($request->id, 'irs_id')],
                'irs_number' => ['required','max:155',Rule::unique('item_return_slip')->where(function ($query) use ($request, $year_data) { $query->where('year_id', '=', $year_data->id); })->ignore($request->id, 'irs_id')],
            ], [
                'irs_sequence.unique' => 'Item Return No. Already Exists',
                'irs_sequence.required' => 'Please Enter Item Return No.',
            ]);

            $irs_data = ItemReturnSlip::where('irs_id', $request->id)->update([
                'irs_sequence' => $request->irs_sequence ?? null,
                'irs_number'   => $request->irs_number ?? null,
                'irs_date' => isset($request->irs_date) ? Date::createFromFormat('d/m/Y', $request->irs_date)->format('Y-m-d') : null,
                'irs_type_id' => $request->irs_type_id ?? null,
                'irs_employee_id' => $request->irs_employee_id ?? null,
                'irs_special_note' => $request->irs_special_note ?? null,
                'last_on' => Carbon::now('Asia/Kolkata'),
                'last_by' => Auth::id(),
            ]);

            if(!$irs_data)
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }

            $update_details =  ItemReturnSlipDetails::where('irsd_irs_id',$request->id)->update(['status' => 'D']);
            $irs_details_data = json_decode($request->irs_details_data, true);
            foreach($irs_details_data as $ctVal)
            {
                if($ctVal['irsd_id'] == 0)
                {
                    ItemReturnSlipDetails::create([
                        'irsd_irs_id' => $request->id,
                        'irsd_iisd_id' => !empty($ctVal['irsd_iisd_id']) ? $ctVal['irsd_iisd_id'] : null,
                        'irsd_item_id' => !empty($ctVal['irsd_item_id']) ? $ctVal['irsd_item_id'] : null,
                        'irsd_sr_no_id' => !empty($ctVal['irsd_sr_no_id']) ? $ctVal['irsd_sr_no_id'] : null,
                        'irsd_batch_no_id' => !empty($ctVal['irsd_batch_no_id']) ? $ctVal['irsd_batch_no_id'] : null,
                        'irsd_sr_no_or_batch_no' => !empty($ctVal['irsd_sr_no_or_batch_no']) ? $ctVal['irsd_sr_no_or_batch_no'] : null,
                        'irsd_return_qty' => !empty($ctVal['irsd_return_qty']) ? $ctVal['irsd_return_qty'] : null,
                        'irsd_non_rerurnable_qty' => !empty($ctVal['irsd_non_rerurnable_qty']) ? $ctVal['irsd_non_rerurnable_qty'] : null,
                        'irsd_reason_id' => !empty($ctVal['irsd_reason_id']) ? $ctVal['irsd_reason_id'] : null,
                        'irsd_remark' => !empty($ctVal['irsd_remark']) ? $ctVal['irsd_remark'] : null,
                        'status' => 'Y',
                    ]);
                }
                else
                {
                    ItemReturnSlipDetails::where('irsd_id', $ctVal['irsd_id'])->update([
                        'irsd_iisd_id' => !empty($ctVal['irsd_iisd_id']) ? $ctVal['irsd_iisd_id'] : null,
                        'irsd_item_id' => !empty($ctVal['irsd_item_id']) ? $ctVal['irsd_item_id'] : null,
                        'irsd_sr_no_id' => !empty($ctVal['irsd_sr_no_id']) ? $ctVal['irsd_sr_no_id'] : null,
                        'irsd_batch_no_id' => !empty($ctVal['irsd_batch_no_id']) ? $ctVal['irsd_batch_no_id'] : null,
                        'irsd_sr_no_or_batch_no' => !empty($ctVal['irsd_sr_no_or_batch_no']) ? $ctVal['irsd_sr_no_or_batch_no'] : null,
                        'irsd_return_qty' => !empty($ctVal['irsd_return_qty']) ? $ctVal['irsd_return_qty'] : null,
                        'irsd_non_rerurnable_qty' => !empty($ctVal['irsd_non_rerurnable_qty']) ? $ctVal['irsd_non_rerurnable_qty'] : null,
                        'irsd_reason_id' => !empty($ctVal['irsd_reason_id']) ? $ctVal['irsd_reason_id'] : null,
                        'irsd_remark' => !empty($ctVal['irsd_remark']) ? $ctVal['irsd_remark'] : null,
                        'status' => 'Y',
                    ]);
                }
            }

            ItemReturnSlipDetails::where('irsd_irs_id',$request->id)->where('status','D')->delete();

            if($irs_data)
            {
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
            ItemReturnSlip::where('irs_id',$request->id)->delete();
            ItemReturnSlipDetails::where('irsd_irs_id',$request->id)->delete();
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

    public function getPendingIssueSlipListForIRS(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();
        $issue_slip_data = ItemIssueSlipDetails::select(
            'item_issue_slip.iis_id',
            'item_issue_slip_details.iisd_id',
            'item_issue_slip_details.iisd_iis_id',
            'item_issue_slip_details.iisd_item_id as irsd_item_id',
            'item_issue_slip_details.iisd_batch_no_id',
            'item_issue_slip_details.iisd_sr_no_id',
            'item_issue_slip_details.iisd_sr_no_or_batch_no',
            'item_issue_slip.iis_sequence',
            'item_issue_slip.iis_number',
            'item_issue_slip.iis_date',
            'item_issue_slip.iis_receiver_id',
            'admin.person_name as user_name',
            'item.item_name',
            'item.item_type',
            'item_issue_slip_details.iisd_issue_qty',
            // 'item_issue_slip_details.iisd_issue_qty as pending_iis_qty',
            'unit.unit',
            // DB::raw("(item_issue_slip_details.iisd_issue_qty  - (SELECT IFNULL(SUM(item_return_slip_details.irsd_return_qty),0) FROM item_return_slip_details WHERE iisd_id  = item_issue_slip_details.iisd_id )) as irsd_pending_qty"),
            DB::raw("((SELECT IFNULL(SUM(iisd.iisd_issue_qty),0) FROM item_issue_slip_details AS iisd WHERE iisd.iisd_id = item_issue_slip_details.iisd_id) - (SELECT IFNULL(SUM(irsd.irsd_return_qty),0)
                FROM item_return_slip_details AS irsd WHERE irsd.irsd_iisd_id = item_issue_slip_details.iisd_id)) as irsd_pending_qty")
        )
        ->leftJoin('item_issue_slip','item_issue_slip.iis_id','=','item_issue_slip_details.iisd_iis_id')
        ->leftJoin('item','item.id','=','item_issue_slip_details.iisd_item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('admin','admin.id','=','item_issue_slip.iis_receiver_id')
        ->where('item_issue_slip_details.iisd_issue_type','=',"Returnable")
        ->whereIn('item_issue_slip.year_id',$yearIds)
        ->having('irsd_pending_qty','>',0)
        ->get();
        
        $itemTypes = [
            'film'          => 'Film',
            'general'       => 'General',
            'lpt_chemical'  => 'LPT Chemical',
            'mpt_chemical'  => 'MPT Chemical',
            'mpt_equipment' => 'MPT Equipment',
            'rt_camera'     => 'RT Camera',
            'ut_equipment'  => 'UT Equipment',
            'ut_probe'      => 'UT Probe',
        ];

        foreach($issue_slip_data as $row)
        {
            if(!empty($row->iis_date))
            {
                $row->iis_date = \Carbon\Carbon::parse($row->iis_date)->format('d/m/Y');
            }

            if(isset($itemTypes[$row->item_type]))
            {
                $row->item_type = $itemTypes[$row->item_type];
            }
        }

        if($issue_slip_data != null)
        {
            return response()->json([
                'response_code' => '1',
                'issue_slip_data' => $issue_slip_data,
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '1',
                'issue_slip_data' => []
            ]);
        }
    }

    public function getIssueSlipPartDataForIRS(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();
        $request->iisd_ids = explode(',', $request->iisd_ids);
        $issue_slip_data = ItemIssueSlipDetails::select(
            'item_issue_slip.iis_id',
            'item_issue_slip_details.iisd_id as irsd_iisd_id',
            'item_issue_slip_details.iisd_iis_id',
            'item_issue_slip_details.iisd_item_id as irsd_item_id',
            'item_issue_slip_details.iisd_batch_no_id as irsd_batch_no_id',
            'item_issue_slip_details.iisd_sr_no_id as irsd_sr_no_id',
            'item_issue_slip_details.iisd_sr_no_or_batch_no as irsd_sr_no_or_batch_no',
            'item_issue_slip.iis_sequence',
            'item_issue_slip.iis_number',
            'item_issue_slip.iis_date',
            'item_issue_slip.iis_receiver_id',
            'admin.person_name as user_name',
            'item.item_name',
            'item.item_type as item_type_id',
            'item_issue_slip_details.iisd_issue_qty as irsd_issue_qty',
            // 'item_issue_slip_details.iisd_issue_qty as irsd_pending_qty',
            'unit.unit as issue_unit',
            'item_issue_slip_details.iisd_remark as irsd_remark',
            // DB::raw("((SELECT IFNULL(SUM(iisd.iisd_issue_qty),0) FROM item_issue_slip_details AS iisd WHERE iisd.iisd_id = item_issue_slip_details.iisd_id) - (SELECT IFNULL(SUM(irsd.irsd_return_qty),0) FROM item_return_slip_details AS irsd WHERE irsd.irsd_iisd_id = item_issue_slip_details.iisd_id)) as irsd_pending_qty")
            DB::raw("((SELECT IFNULL(SUM(iisd.iisd_issue_qty),0) FROM item_issue_slip_details AS iisd WHERE iisd.iisd_id = item_issue_slip_details.iisd_id) - (SELECT IFNULL(SUM(irsd.irsd_return_qty),0) FROM item_return_slip_details AS irsd WHERE irsd.irsd_iisd_id = item_issue_slip_details.iisd_id)) as irsd_pending_qty")
        )
        ->leftJoin('item_issue_slip','item_issue_slip.iis_id','=','item_issue_slip_details.iisd_iis_id')
        ->leftJoin('item','item.id','=','item_issue_slip_details.iisd_item_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('admin','admin.id','=','item_issue_slip.iis_receiver_id')
        ->where('item_issue_slip_details.iisd_issue_type','=',"Returnable")
        ->whereIn('item_issue_slip_details.iisd_id', $request->iisd_ids)
        ->whereIn('item_issue_slip.year_id',$yearIds)
        ->having('irsd_pending_qty','>',0)
        // ->first();
        ->get();

        $itemTypes = [
            'film'          => 'Film',
            'general'       => 'General',
            'lpt_chemical'  => 'LPT Chemical',
            'mpt_chemical'  => 'MPT Chemical',
            'mpt_equipment' => 'MPT Equipment',
            'rt_camera'     => 'RT Camera',
            'ut_equipment'  => 'UT Equipment',
            'ut_probe'      => 'UT Probe',
        ];

        foreach($issue_slip_data as $row)
        {
            if(!empty($row->iis_date))
            {
                $row->iis_date = \Carbon\Carbon::parse($row->iis_date)->format('d/m/Y');
            }

            if(isset($itemTypes[$row->item_type]))
            {
                $row->item_type = $itemTypes[$row->item_type];
            }
        }

        if($issue_slip_data != null)
        {
            return response()->json([
                'response_code' => '1',
                'issue_slip_data' => $issue_slip_data,
            ]);
        }
        else
        {
            return response()->json([
                'response_code' => '1',
                'issue_slip_data' => []
            ]);
        }
    }
}