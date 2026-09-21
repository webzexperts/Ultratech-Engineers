<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Item;
use App\Models\LPTChemical;
use App\Models\MaterialMpt;
use App\Models\EquipmentMPT;
use App\Models\EquipmentUT;
use App\Models\ProbeUT;
use App\Models\ItemIssueSlip;
use App\Models\ItemIssueSlipDetails;
use App\Models\ItemReturnSlipDetails;
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

class IssueSlipController extends Controller
{
    public function manage()
    {
        return view('manage.manage-issue_slip');
    }

    public function index(ItemIssueSlip $ItemIssueSlip, Request $request, DataTables $datatables)
    {
        $year_data = getCurrentYearData();
        $issue_slip_data = ItemIssueSlipDetails::select([
            'item_issue_slip.iis_id',
            'item_issue_slip.iis_sequence',
            'item_issue_slip.iis_number',
            'item_issue_slip.iis_date',
            'item_issue_slip.iis_issuer_id',
            'item_issue_slip.iis_receiver_id',
            'item_issue_slip.iis_checked_by_issuer',
            'item_issue_slip.iis_checked_by_receiver',
            'item_issue_slip.iis_special_note',
            'item_issue_slip.created_on',
            'item_issue_slip.created_by',
            'item_issue_slip.last_by',
            'item_issue_slip.last_on',
            'item.item_name',
            'admin.person_name as user_name',
            'item_issue_slip_details.iisd_id',
            'item_issue_slip_details.iisd_iis_id',
            'item_issue_slip_details.iisd_item_id',
            'item_issue_slip_details.iisd_batch_no_id',
            'item_issue_slip_details.iisd_sr_no_id',
            'item_issue_slip_details.iisd_sr_no_or_batch_no',
            'item_issue_slip_details.iisd_issue_qty',
            'item_issue_slip_details.iisd_issue_type',
            'item_issue_slip_details.iisd_remark'
        ])
        ->leftJoin('item_issue_slip','item_issue_slip.iis_id','=','item_issue_slip_details.iisd_iis_id')
        ->leftJoin('admin','admin.id','=','item_issue_slip.iis_receiver_id')
        ->leftJoin('item','item.id','=','item_issue_slip_details.iisd_item_id')
        ->where('item_issue_slip.year_id', $year_data->id);
        $dataTable = DataTables::of($issue_slip_data)
        ->editColumn('iis_date', function($issue_slip_data){
            if ($issue_slip_data->iis_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $issue_slip_data->iis_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('item_issue_slip.iis_date', function ($q, $k) {
            applyDate($q, $k, 'item_issue_slip.iis_date');
        })
        ->addColumn('options',function($issue_slip_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if (hasAccess("issue_slip", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit_issue_slip"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("issue_slip", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'item_issue_slip');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }
    
    public function getBatchNoAndSrNoIssueSlipData(Request $request)
    {
        $iisd_batch_no_id = "";
        $iisd_sr_no_id = "";

        if($request->iisd_item_type == "lpt_chemical") {
            $iisd_batch_no_id = LPTChemical::where('lpt_chemical_id', $request->item_id)->select('lpt_id as iisd_batch_no_id','lpt_batch_no as sr_batch_name')->orderBy('lpt_batch_no', 'asc')->get();
        } else if($request->iisd_item_type == "mpt_chemical") {
            $iisd_batch_no_id = MaterialMpt::where('mm_material_id', $request->item_id)->select('mm_id as iisd_batch_no_id','mm_batch_no as sr_batch_name')->orderBy('mm_batch_no', 'asc')->get();
        } else if($request->iisd_item_type == "mpt_equipment") {
            $iisd_sr_no_id = EquipmentMPT::where('em_mpt_equipment_id', $request->item_id)->select('em_id as iisd_sr_no_id','em_serial_no as sr_no_name')->orderBy('em_serial_no', 'asc')->get();
        } else if($request->iisd_item_type == "ut_equipment") {
            $iisd_sr_no_id = EquipmentUT::where('eu_ut_equipment_id', $request->item_id)->select('eu_id as iisd_sr_no_id','eu_serial_no as sr_no_name')->orderBy('eu_serial_no', 'asc')->get();
        } else if($request->iisd_item_type == "ut_probe") {
            $iisd_sr_no_id = ProbeUT::where('pu_ut_probe_id', $request->item_id)->select('pu_id as iisd_sr_no_id','pu_serial_number as sr_no_name')->orderBy('pu_serial_number', 'asc')->get();
        }

        return response()->json([
            'iisd_batch_no_id' => $iisd_batch_no_id,
            'iisd_sr_no_id' => $iisd_sr_no_id,
            'response_code' => '1'
        ]);
    }

    public function getLatestIssueSlipNumber(Request $request)
    {
        $modal  =  ItemIssueSlip::class;
        $sequence = 'iis_sequence';
        $prefix = 'ISSUE';
        $sup_num_format = getLatestSequence($modal,$sequence,$prefix);

        return response()->json([
            'response_code' => 1,
            'latest_no'     => $sup_num_format['format'],
            'number'        => $sup_num_format['isFound'],
        ]);
    }

    public function store(Request $request)
    {
        $year_data = getCurrentYearData();
        $existNumber = ItemIssueSlip::where([['iis_sequence',  $request->iis_sequence],['iis_number',$request->iis_number],['year_id',$year_data->id]])->first();
        
        if($existNumber)
        {
            $latestNo = $this->getLatestIssueSlipNumber($request);
            $tmp =  $latestNo->getContent();
            $area = json_decode($tmp, true);
            $iis_number =   $area['latest_no'];
            $iis_sequence = $area['number'];
        }
        else
        {
            $iis_number = $request->iis_number;
            $iis_sequence = $request->iis_sequence;
        }

        DB::beginTransaction();
        try
        {
            $issue_slip_data =  ItemIssueSlip::create([
                'iis_number' => $iis_number ?? null,
                'iis_sequence'   => $iis_sequence ?? null,
                'iis_date' => isset($request->iis_date) ? Date::createFromFormat('d/m/Y', $request->iis_date)->format('Y-m-d') : null,
                'iis_issuer_id' => $request->iis_issuer_id ?? null,
                'iis_receiver_id' => $request->iis_receiver_id ?? null,
                'iis_special_note' => $request->iis_special_note ?? null,
                'iis_checked_by_issuer' => $request->iis_checked_by_issuer ?? null,
                'iis_checked_by_receiver' => $request->iis_checked_by_receiver ?? null,
                'year_id' => $year_data->id,
                'company_id' => Auth::user()->company_id,
                'created_on' => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by' => Auth::user()->id,
            ]);

            $issue_slip_details_data = $request->issue_slip_details_data = json_decode($request->issue_slip_details_data, true);
            if(!empty($issue_slip_details_data))
            {
                foreach($issue_slip_details_data as $ctKey => $ctVal)
                {
                    if($ctVal != null)
                    {
                        $issue_slip_details_data = ItemIssueSlipDetails::create([
                            'iisd_iis_id' => $issue_slip_data->id,
                            'iisd_item_id' => !empty($ctVal['iisd_item_id']) ? $ctVal['iisd_item_id'] : null,
                            'iisd_batch_no_id' => !empty($ctVal['iisd_batch_no_id']) ? $ctVal['iisd_batch_no_id'] : null,
                            'iisd_sr_no_id' => !empty($ctVal['iisd_sr_no_id']) ? $ctVal['iisd_sr_no_id'] : null,
                            'iisd_sr_no_or_batch_no' => !empty($ctVal['iisd_sr_no_or_batch_no']) ? $ctVal['iisd_sr_no_or_batch_no'] : null,
                            'iisd_issue_qty' => !empty($ctVal['iisd_issue_qty']) ? $ctVal['iisd_issue_qty'] : null,
                            'iisd_issue_type' => !empty($ctVal['iisd_issue_type']) ? $ctVal['iisd_issue_type'] : null,
                            'iisd_remark' => !empty($ctVal['iisd_remark']) ? $ctVal['iisd_remark'] : null,
                            'status' => 'Y',
                        ]);
                    }
                }
            }

            if($issue_slip_data->save())
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
        $issue_slip_data =  DB::select('CALL item_issue_slip_master(?)', [$request->id]);
        if(!empty($issue_slip_data))
        {
            $issue_slip_data = $issue_slip_data[0];
            $issue_slip_data->iis_date = $issue_slip_data->iis_date != "" ? Date::createFromFormat('Y-m-d',  $issue_slip_data->iis_date)->format('d/m/Y') : "";
        }

        $issue_slip_details_data = DB::select('CALL item_issue_slip_details(?)', [$request->id]);


        if($issue_slip_details_data){
            foreach($issue_slip_details_data as $dKey => $dVal)
            {

                $total_return_qty = ItemReturnSlipDetails::where('irsd_iisd_id', '=', $dVal->iisd_id)->sum('irsd_return_qty');
                $total_used_qty = $total_return_qty;

                    if($total_used_qty != null && $total_used_qty > 0){
                        $dVal->in_use = true;
                        $dVal->used_qty = $total_used_qty;
                        $isAnyPartInUse = true;
                    } else {
                        $dVal->in_use = false;
                        $dVal->used_qty = 0;
                    }
            }
            
        }
        if($isAnyPartInUse == true){
            $issue_slip_data->in_use = true;
        } else {
            $issue_slip_data->in_use = false;
        }

        if($issue_slip_data)
        {
            return response()->json([
                'issue_slip_data'     => $issue_slip_data,
                'issue_slip_details_data'     => $issue_slip_details_data,
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
            $validated = $request->validate(['iis_sequence' => ['required', 'max:155', Rule::unique('item_issue_slip')->where(function ($query) use ($request, $year_data) { $query->where('year_id', '=', $year_data->id);})->ignore($request->id, 'iis_id')],
                'iis_number' => ['required','max:155',Rule::unique('item_issue_slip')->where(function ($query) use ($request, $year_data) { $query->where('year_id', '=', $year_data->id); })->ignore($request->id, 'iis_id')],
            ], [
                'iis_sequence.unique' => 'Issue Slip No. Already Exists',
                'iis_sequence.required' => 'Please Enter Issue Slip No.',
            ]);

            $issue_slip_data = ItemIssueSlip::where('iis_id', $request->id)->update([
                'iis_sequence' => $request->iis_sequence ?? null,
                'iis_number'   => $request->iis_number ?? null,
                'iis_date' => isset($request->iis_date) ? Date::createFromFormat('d/m/Y', $request->iis_date)->format('Y-m-d') : null,
                'iis_issuer_id' => $request->iis_issuer_id ?? null,
                'iis_receiver_id' => $request->iis_receiver_id ?? null,
                'iis_special_note' => $request->iis_special_note ?? null,
                'iis_checked_by_issuer' => $request->iis_checked_by_issuer ?? null,
                'iis_checked_by_receiver' => $request->iis_checked_by_receiver ?? null,
                'last_on' => Carbon::now('Asia/Kolkata'),
                'last_by' => Auth::id(),
            ]);

            if(!$issue_slip_data)
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }

            $update_details =  ItemIssueSlipDetails::where('iisd_iis_id',$request->id)->update(['status' => 'D']);
            $issue_slip_details_data = json_decode($request->issue_slip_details_data, true);
            foreach($issue_slip_details_data as $ctVal)
            {
                if($ctVal['iisd_id'] == 0)
                {
                    ItemIssueSlipDetails::create([
                        'iisd_iis_id' => $request->id,
                        'iisd_item_id' => !empty($ctVal['iisd_item_id']) ? $ctVal['iisd_item_id'] : null,
                        'iisd_batch_no_id' => !empty($ctVal['iisd_batch_no_id']) ? $ctVal['iisd_batch_no_id'] : null,
                        'iisd_sr_no_id' => !empty($ctVal['iisd_sr_no_id']) ? $ctVal['iisd_sr_no_id'] : null,
                        'iisd_sr_no_or_batch_no' => !empty($ctVal['iisd_sr_no_or_batch_no']) ? $ctVal['iisd_sr_no_or_batch_no'] : null,
                        'iisd_issue_qty' => !empty($ctVal['iisd_issue_qty']) ? $ctVal['iisd_issue_qty'] : null,
                        'iisd_issue_type' => !empty($ctVal['iisd_issue_type']) ? $ctVal['iisd_issue_type'] : null,
                        'iisd_remark' => !empty($ctVal['iisd_remark']) ? $ctVal['iisd_remark'] : null,
                        'status' => 'Y',
                    ]);
                }
                else
                {
                    ItemIssueSlipDetails::where('iisd_id', $ctVal['iisd_id'])->update([
                        'iisd_item_id' => !empty($ctVal['iisd_item_id']) ? $ctVal['iisd_item_id'] : null,
                        'iisd_batch_no_id' => !empty($ctVal['iisd_batch_no_id']) ? $ctVal['iisd_batch_no_id'] : null,
                        'iisd_sr_no_id' => !empty($ctVal['iisd_sr_no_id']) ? $ctVal['iisd_sr_no_id'] : null,
                        'iisd_sr_no_or_batch_no' => !empty($ctVal['iisd_sr_no_or_batch_no']) ? $ctVal['iisd_sr_no_or_batch_no'] : null,
                        'iisd_issue_qty' => !empty($ctVal['iisd_issue_qty']) ? $ctVal['iisd_issue_qty'] : null,
                        'iisd_issue_type' => !empty($ctVal['iisd_issue_type']) ? $ctVal['iisd_issue_type'] : null,
                        'iisd_remark' => !empty($ctVal['iisd_remark']) ? $ctVal['iisd_remark'] : null,
                        'status' => 'Y',
                    ]);
                }
            }

            ItemIssueSlipDetails::where('iisd_iis_id',$request->id)->where('status','D')->delete();

            if($issue_slip_data)
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
            $return_data = ItemReturnSlipDetails::
            leftJoin('item_issue_slip_details','item_issue_slip_details.iisd_id','=','item_return_slip_details.irsd_iisd_id')
            ->where('item_issue_slip_details.iisd_iis_id',$request->id)->get();
            
            if($return_data->isNotEmpty()){ 
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, Item Issue Is Used In Item Return",
                ]);
            }


            ItemIssueSlip::where('iis_id',$request->id)->delete();
            ItemIssueSlipDetails::where('iisd_iis_id',$request->id)->delete();
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
}