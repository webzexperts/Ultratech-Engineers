<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction\GRNSupplier;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\Models\Admin;
use App\Models\Transaction\GRNSupplierDetails;
use App\Models\Item;
use App\Models\MaterialMpt;
use App\Models\ProbeUT;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetails;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;
use App\Models\EquipmentMPT;
use App\Models\LPTChemical;
use App\Models\EquipmentUT;



class GRNSupplierController extends Controller
{
    public function manage()
    {
        return view('manage.transaction.manage-grn_supplier');
    }

    public function index(GRNSupplier $grn_sup_data, Request $request, DataTables $datatables)
    {

        $year_data = getCurrentYearData();
        $current_location = getCurrentLocation()->location_id;
        $grn_sup_data = GRNSupplier::select([
            'grn_supplier.grn_id',
            'grn_supplier.grn_type_id',
            'grn_supplier.grn_sequence',
            'grn_supplier.grn_number',
            'grn_supplier.grn_date',
            'grn_supplier.grn_supplier_id',
            'suppliers.supplier_name',
            'grn_supplier.grn_challan_number',
            'grn_supplier.grn_challan_date',
            'grn_supplier_details.table_unique_id',
            'grn_supplier_details.table_pk_id',
            \DB::raw("
            CASE 
            WHEN grn_supplier_details.table_unique_id = 'Against PO' THEN po.po_number
            WHEN grn_supplier_details.table_unique_id = 'Against DC' THEN dc.sup_dc_number
                    ELSE ''
                END as ref_number
            "),\DB::raw(" CASE 
                    WHEN grn_supplier_details.table_unique_id = 'Against PO' THEN po.po_date
                    WHEN grn_supplier_details.table_unique_id = 'Against DC' THEN dc.sup_dc_date
                    ELSE NULL
                    END as ref_date
            "),
            'sr.name_for_display',
            'item.item_name',
            'item_group.item_group',
            'item.item_type',
            'unit.unit',
            'grn_supplier_details.sr_table_unique_id',
            'grn_supplier_details.sr_table_pk_id',
            'grn_supplier_details.grnd_qty',
            'grn_supplier_details.grnd_rate_unit',
            'grn_supplier_details.grnd_amount',
            'grn_supplier_details.grnd_remark',
            'admin.person_name as user_name',
            'grn_supplier.created_on',
            'grn_supplier.created_by',
            'grn_supplier.last_by',
            'grn_supplier.last_on'
        ])
        ->leftJoin('grn_supplier_details','grn_supplier_details.grnd_grn_id','=','grn_supplier.grn_id')
        ->leftJoin('admin','admin.id','=','grn_supplier.prepared_by_user_id')
        ->leftJoin('suppliers','suppliers.id','=','grn_supplier.grn_supplier_id')
        ->leftJoin('item','item.id','=','grn_supplier_details.grnd_item_id')
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        ->leftJoin('purchase_order_details as pod', function ($join) {
            $join->on('pod.pod_id', '=', 'grn_supplier_details.table_pk_id')
                ->where('grn_supplier_details.table_unique_id', '=', 'Against PO');
        })
        ->leftJoin('purchase_order as po', 'po.po_id', '=', 'pod.pod_po_id')
        ->leftJoin('supplier_dc_details as dcd', function ($join) {
            $join->on('dcd.sup_dcd_id', '=', 'grn_supplier_details.table_pk_id')
                ->where('grn_supplier_details.table_unique_id', '=', 'Against DC');
        })
        ->leftJoin('supplier_dc as dc', 'dc.sup_dc_id', '=', 'dcd.sup_dcd_dc_id')
        ->where('grn_supplier.year_id', $year_data->id)
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'grn_supplier_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'grn_supplier_details.sr_table_unique_id');
        })
        ->where('grn_supplier.current_location_id',$current_location);


        $dataTable = DataTables::of($grn_sup_data)
        ->filterColumn('grn_supplier.grn_number', function($query, $keyword) {
            applynumberprefix($query, $keyword, 'grn_supplier.grn_number');
        })
        ->filterColumn('ref_number', function($query, $keyword) {

            applynumberprefix($query, $keyword, 'po.po_number');

            $query->orWhere(function($q) use ($keyword) {
                applynumberprefix($q, $keyword, 'dc.sup_dc_number');
            });

        })
         ->editColumn('item_type', function($grn_sup_data) {
            return config('app.item_type')[$grn_sup_data->item_type] ?? $grn_sup_data->item_type;
        })
        ->filterColumn('item.item_type', function($query, $keyword) {

            $itemTypes = config('app.item_type');
            $matchedKeys = [];
            foreach ($itemTypes as $key => $value) {
                if (stripos($value, $keyword) !== false) {
                    $matchedKeys[] = $key;
                }
            }
            if (!empty($matchedKeys)) {
                $query->whereIn('item.item_type', $matchedKeys);
            } else {
                $query->where('item.item_type', 'like', "%{$keyword}%");
            }
        })
        ->editColumn('grn_date', function($grn_sup_data){
            if ($grn_sup_data->grn_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $grn_sup_data->grn_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('grn_supplier.grn_date', function ($q, $k) {
            applyDate($q, $k, 'grn_supplier.grn_date');
        })
        ->editColumn('ref_date', function($grn_sup_data){
            if ($grn_sup_data->ref_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $grn_sup_data->ref_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('ref_date', function($q, $k) {

            $q->where(function($query) use ($k) {

                // ✅ PO Date
                $query->where(function($q1) use ($k) {
                    $q1->where('grn_supplier_details.table_unique_id', 'Against PO');
                    applyDate($q1, $k, 'po.po_date');
                });

                // ✅ DC Date
                $query->orWhere(function($q2) use ($k) {
                    $q2->where('grn_supplier_details.table_unique_id', 'Against DC');
                    applyDate($q2, $k, 'dc.sup_dc_date');
                });

            });

        })
        ->editColumn('grn_challan_date', function($grn_sup_data){
            if ($grn_sup_data->grn_challan_date != null) {
                $formatedDate = Date::createFromFormat('Y-m-d', $grn_sup_data->grn_challan_date)->format(DATE_FORMAT); return $formatedDate;
            } else {
                return '';
            }
        })
        ->filterColumn('grn_supplier.grn_challan_date', function ($q, $k) {
            applyDate($q, $k, 'grn_supplier.grn_challan_date');
        })

        ->editColumn('grnd_qty', function($grn_sup_data) {
            return $grn_sup_data->grnd_qty > 0 ? number_format((float)$grn_sup_data->grnd_qty, 3, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('grn_supplier_details.grnd_qty', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('grnd_qty')
                    ->orWhere('grnd_qty', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(grnd_qty AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('grnd_qty', 'like', "%{$search}%");
            }
        })
        ->editColumn('grnd_rate_unit', function($grn_sup_data) {
            return $grn_sup_data->grnd_rate_unit > 0 ? number_format((float)$grn_sup_data->grnd_rate_unit, 2, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('grn_supplier_details.grnd_rate_unit', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('grnd_rate_unit')
                    ->orWhere('grnd_rate_unit', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(grnd_rate_unit AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('grnd_rate_unit', 'like', "%{$search}%");
            }
        })
        ->editColumn('grnd_amount', function($grn_sup_data) {
            return $grn_sup_data->grnd_amount > 0 ? number_format((float)$grn_sup_data->grnd_amount, 2, '.','') : number_format((float) 0, 3, '.','');
        })
        ->filterColumn('grn_supplier_details.grnd_amount', function($query, $keyword) {
            $search = trim($keyword);
            if (preg_match('/^0(\.0{0,3})?$/', $search)) {
                $query->where(function($q) {
                    $q->whereNull('grnd_amount')
                    ->orWhere('grnd_amount', 0);
                });
            }
            elseif (is_numeric($search)) {
                $query->whereRaw("CAST(grnd_amount AS CHAR) LIKE ?", ["{$search}%"]);
            }
            else {
                $query->where('grnd_amount', 'like', "%{$search}%");
            }
        })
        
        ->addColumn('options',function($grn_sup_data){
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

                if(hasAccess("grn_supplier", "print")) {
                    $grn_number = !empty($grn_sup_data->grn_number) ? '_'.str_replace('/', '_', $grn_sup_data->grn_number) : "";

                    $supplier_for_wolh = !empty($grn_sup_data->supplier_name) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $grn_sup_data->supplier_name) : "";

                    $pdfName  = 'GRN_Supplier'.$grn_number.$supplier_for_wolh;
                    $encodedId = base64_encode($grn_sup_data->grn_number);
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdfName}&type=grn_supplier");
                    $action .= '<li><a  class="dropdown-item" target="_blank" href="' . $url . '"><i class="bx bxs-file-pdf align-bottom me-2 text-muted" id="print_a"></i> Print</a></li>';
                }

                if (hasAccess("grn_supplier", "edit")) {
                    $action .= '<li><a class="dropdown-item edit-item-btn edit-grn_supplier"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
                }

                if (hasAccess("grn_supplier", "delete")) {
                    $action .= '<li> <a class="dropdown-item remove-item-btn">
                                    <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                    </a>
                                </li>';
                }
            $action .= '</ul></div>';
            return $action;
        });
        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'grn_supplier');
        return $dataTable
        ->rawColumns(['options','last_by','last_on','created_by','created_on'])
        ->make(true);
    }

    public function getLatestGrnSupplierNumber(Request $request)
    {
        $modal  =  GRNSupplier::class;
        $sequence = 'grn_sequence';           
        $prefix = 'GRN';           
        $sup_num_format = getLatestSequence($modal,$sequence,$prefix);   

        return response()->json([
          'response_code' => 1,
          'latest_no'     => $sup_num_format['format'],
          'number'        => $sup_num_format['isFound'],
      ]);
    }


    // public function store(Request $request)
    // {
    //     dd($request->all());
    //     $year_data = getCurrentYearData();
    //     $existNumber = GRNSupplier::where([['grn_sequence',  $request->grn_sequence],['grn_number',$request->grn_number],['year_id',$year_data->id]])->first();
        
    //     if($existNumber)
    //     {
    //         $latestNo = $this->getLatestGrnNumber($request);
    //         $tmp =  $latestNo->getContent();
    //         $area = json_decode($tmp, true);
    //         $grn_number =   $area['latest_no'];
    //         $grn_sequence = $area['number'];
    //     }
    //     else
    //     {
    //         $grn_number   = $request->grn_number;
    //         $grn_sequence = $request->grn_sequence;
    //     }

    //     DB::beginTransaction();
    //     try
    //     {
    //         $grn_data =  GRNSupplier::create([
    //             'grn_number'           => $grn_number ?? null,
    //             'grn_sequence'         => $grn_sequence ?? null,
    //             'grn_date'             => isset($request->grn_date) ? Date::createFromFormat('d/m/Y', $request->grn_date)->format('Y-m-d') : null,
    //             'grn_supplier_id'       => $request->grn_supplier_id ?? null,
    //             'grn_type_id'           => $request->grn_type_id ?? null,
    //             'grn_challan_number'    => $request->grn_challan_number ?? null,
    //             'grn_challan_date'     => isset($request->grn_challan_date) ? Date::createFromFormat('d/m/Y', $request->grn_challan_date)->format('Y-m-d') : null,
    //             'grn_transporter'       => $request->grn_transporter ?? null,
    //             'grn_vehicle_number'    => $request->grn_vehicle_number ?? null,
    //             'grn_total_amount'      => $request->grn_total_amount ?? null,
    //             'grn_special_note'      => $request->grn_special_note ?? null,
    //             'year_id'               => $year_data->id,
    //             'company_id'            => Auth::user()->company_id,
    //             'created_on'            => Carbon::now('Asia/Kolkata')->toDateTimeString(),
    //             'created_by'            => Auth::user()->id,
    //         ]);


    //         $grn_details_data = $request->grn_details_data = json_decode($request->grn_details_data, true);
    //         // dd($grn_details_data);
    //         if(!empty($grn_details_data))
    //         {
    //             foreach($grn_details_data as $ctKey => $ctVal)
    //             {
    //                 if($ctVal != null)
    //                 {
    //                     $grn_details_data = GRNSupplierDetails::create([

    //                         'grnd_grn_id'        => $grn_data->grn_id,
    //                         'grnd_pod_id'        => !empty($ctVal['grnd_pod_id']) ? $ctVal['grnd_pod_id'] : null,

    //                         'grnd_item_id'       => !empty($ctVal['grnd_item_id']) ? $ctVal['grnd_item_id'] : null,
                           
    //                         'grnd_description'   => !empty($ctVal['grnd_description']) ? $ctVal['grnd_description'] : null,

    //                         'grnd_qty'           => !empty($ctVal['grnd_qty']) ? $ctVal['grnd_qty'] : null,

    //                         'grnd_qty_unit_id'           => !empty($ctVal['grnd_qty_unit_id']) ? $ctVal['grnd_qty_unit_id'] : null,

    //                         'grnd_rate_unit'  => !empty($ctVal['grnd_rate_unit']) ? $ctVal['grnd_rate_unit'] : null,

    //                         'grnd_amount'  => !empty($ctVal['grnd_amount']) ? $ctVal['grnd_amount'] : null,

    //                         'grnd_remark'  => !empty($ctVal['grnd_remark']) ? $ctVal['grnd_remark'] : null,

    //                         'status' => 'Y',
    //                     ]);
    //                 }
    //             }
    //         }

    //         if($grn_data->save())
    //         {
    //             DB::commit();

    //             $get_supplier = GRNSupplier::select('suppliers.supplier_name')
    //             ->leftJoin('suppliers', 'suppliers.id', '=', 'grn.grn_supplier_id')
    //             ->where('grn.grn_supplier_id', $grn_data->grn_supplier_id)
    //             ->first();

    //             $grn_number = !empty($grn_data->grn_number) ? '_'.str_replace('/', '_', $grn_data->grn_number) : "";

    //             $customer_for_wolh = !empty($get_supplier) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $get_supplier->supplier_name) : "";

    //             $pdf_name = 'GRN'.$grn_number.$customer_for_wolh;

    //             GeneratePdf($grn_data->grn_id,$pdf_name,'grn','add');
    //             return response()->json([
    //                 'response_code' => '1',
    //                 'response_message' => getResponseMessage('store_success'),
    //             ]);
    //         }
    //         else
    //         {
    //             return response()->json([
    //                 'response_code' => '0',
    //                 'response_message' => getResponseMessage('store_error'),
    //             ]);
    //         }
    //     }
    //     catch(\Exception $e)
    //     {
    //         DB::rollBack();
    //         return response()->json([
    //             'response_code' => '0',
    //             'response_message' => getResponseMessage('store_error'),
    //             'original_error' => $e->getMessage()
    //         ]);
    //     }
    // }
    public function store(Request $request)
    {
        // dd($request->all());
        $year_data = getCurrentYearData();
        $LocationData = getCurrentLocation();

        DB::beginTransaction();

        try {

            $existNumber = GRNSupplier::where([
                ['grn_sequence', $request->grn_sequence],
                ['grn_number', $request->grn_number],
                ['year_id', $year_data->id]
            ])->first();

            if ($existNumber) {
                $latestNo = $this->getLatestGrnSupplierNumber($request);
                $tmp = $latestNo->getContent();
                $area = json_decode($tmp, true);

                $grn_number = $area['latest_no'];
                $grn_sequence = $area['number'];
            } else {
                $grn_number = $request->grn_number;
                $grn_sequence = $request->grn_sequence;
            }

            $page_id = getMenuIdBassedOnDisplayName('grn_supplier');
            $assign_format_no = getAssignFormateNoForTransaction(
                $LocationData->location_id,
                $page_id->id,
                $request->grn_date
            );
            $amount_rupee = $request->grn_total_amount;
            $amount_in_word = digitsToWords($amount_rupee);

            $grn_data = GRNSupplier::create([
                'grn_number'        => $grn_number,
                'grn_sequence'      => $grn_sequence,
                'grn_date'          => $request->grn_date ? Date::createFromFormat('d/m/Y', $request->grn_date)->format('Y-m-d') : null,
                'grn_supplier_id'   => $request->grn_supplier_id,
                'grn_type_id'       => $request->grn_type_id,
                'grn_challan_number'=> $request->grn_challan_number,
                'grn_challan_date'  => $request->grn_challan_date ? Date::createFromFormat('d/m/Y', $request->grn_challan_date)->format('Y-m-d') : null,
                'grn_transporter'   => $request->grn_transporter,
                'mode_of_transport' => $request->mode_of_transport,   
                'grn_vehicle_number'=> $request->grn_vehicle_number,
                'grn_total_amount'  => $request->grn_total_amount,
                'grn_special_note'  => $request->grn_special_note,
                'amount_in_word'    => $amount_in_word ?? null,
                'prepared_by_user_id' => $request->prepared_by_user_id,
                'assign_format_no'  => $assign_format_no ?? null,
                'current_location_id' => $LocationData->location_id ?? null,
                'year_id'           => $year_data->id,
                'company_id'        => Auth::user()->company_id,
                'created_by'        => Auth::user()->id,
                'created_on'        => Carbon::now('Asia/Kolkata'),
            ]);

            $details = json_decode($request->grn_details_data, true);

            if (!empty($details)) {
                foreach ($details as $row) {

                    if (!empty($row['grnd_item_id'])) {

                        $from_qty         = (float)$row['grnd_qty'];
                        $next_qty         = 0;
                        $transaction_mode = 'I';
                        $grnd_id          = 0;                    
                        $table_pk_id      = $row['table_pk_id'] ?? 0;

                        $checkQty = $this->qtyValidation($from_qty, $next_qty, $transaction_mode, $grnd_id, $table_pk_id, $request->grn_type_id);

                        if ($checkQty) {
                            return $checkQty;
                        }

                        // Move certificate file from temp to uploads
                        $cali_certificate = null;
                        $cali_certificate_blob = null;
                        if (!empty($row['calibration_certificate_doc'])) {
                            $fileHelper = new \App\Models\File();
                            $isFound = $fileHelper->getFileFromTemp($row['calibration_certificate_doc'], 'calibration_certificate');
                            if ($isFound) {
                                $cali_certificate = $isFound;
                                if (file_exists(storage_path('app/public/' . $isFound))) {
                                    $cali_certificate_blob = file_get_contents(storage_path('app/public/' . $isFound));
                                }
                            } else {
                                $cali_certificate = $row['calibration_certificate_doc'];
                                if (file_exists(storage_path('app/public/' . $cali_certificate))) {
                                    $cali_certificate_blob = file_get_contents(storage_path('app/public/' . $cali_certificate));
                                }
                            }
                        }

                        $for_calibration = 'No';
                        if (($row['table_unique_id'] ?? '') === 'Against DC' && !empty($row['table_pk_id'])) {
                            $for_calibration_val = DB::table('supplier_dc_details')
                                ->where('sup_dcd_id', $row['table_pk_id'])
                                ->value('for_calibration');
                            if ($for_calibration_val === 'Yes') {
                                $for_calibration = 'Yes';
                            }
                        }
                        $isCaliActive = ($request->grn_type_id == 'Against DC' && $for_calibration == 'Yes');

                        // Load previous values from master if serial number is present
                        $previous_last_cali_date = null;
                        $previous_next_cali_due_date = null;
                        $previous_cali_freq = null;
                        $previous_cali_certificate = null;
                        $previous_cali_certificate_blob = null;

                        if ($isCaliActive && !empty($row['sr_table_unique_id']) && !empty($row['sr_table_pk_id'])) {
                            $tableMap = [
                                'rt_camera' => ['table' => 'rt_camera', 'pk' => 'rt_camera_id', 'prefix' => 'rt_'],
                                'mpt_equipment' => ['table' => 'equipment_mpt', 'pk' => 'em_id', 'prefix' => 'em_'],
                                'mpt_material' => ['table' => 'material_mpt', 'pk' => 'mm_id', 'prefix' => 'mm_'],
                                'ut_equipment' => ['table' => 'equipment_ut', 'pk' => 'eu_id', 'prefix' => 'eu_'],
                                'dpt_chemical' => ['table' => 'dpt_chemical', 'pk' => 'dpt_id', 'prefix' => 'dpt_'],
                                'instrument' => ['table' => 'instrument', 'pk' => 'ins_id', 'prefix' => 'ins_'],
                                'probe_ut' => ['table' => 'probe_ut', 'pk' => 'pu_id', 'prefix' => 'pu_'],
                            ];

                            if (isset($tableMap[$row['sr_table_unique_id']])) {
                                $tableInfo = $tableMap[$row['sr_table_unique_id']];
                                $prefix = $tableInfo['prefix'];

                                $masterRecord = DB::table($tableInfo['table'])
                                    ->where($tableInfo['pk'], $row['sr_table_pk_id'])
                                    ->first();

                                if ($masterRecord) {
                                    $previous_last_cali_date = $masterRecord->{$prefix . 'last_cali_date'} ?? null;
                                    $previous_next_cali_due_date = $masterRecord->{$prefix . 'next_cali_due_date'} ?? null;
                                    $previous_cali_freq = $masterRecord->{$prefix . 'cali_freq'} ?? null;
                                    $previous_cali_certificate = $masterRecord->{$prefix . 'cali_certificate'} ?? null;
                                    $previous_cali_certificate_blob = $masterRecord->{$prefix . 'cali_certificate_blob'} ?? null;

                                    // Update master with new calibration dates
                                    $updateData = [];
                                    if (property_exists($masterRecord, $prefix . 'last_cali_date')) {
                                        $updateData[$prefix . 'last_cali_date'] = !empty($row['ins_last_cali_date']) ? Date::createFromFormat('d/m/Y', $row['ins_last_cali_date'])->format('Y-m-d') : null;
                                    }
                                    if (property_exists($masterRecord, $prefix . 'next_cali_due_date')) {
                                        $updateData[$prefix . 'next_cali_due_date'] = !empty($row['ins_next_cali_due_date']) ? Date::createFromFormat('d/m/Y', $row['ins_next_cali_due_date'])->format('Y-m-d') : null;
                                    }
                                    if (property_exists($masterRecord, $prefix . 'cali_freq')) {
                                        $updateData[$prefix . 'cali_freq'] = (isset($row['ins_cali_freq']) && $row['ins_cali_freq'] !== '') ? $row['ins_cali_freq'] : null;
                                    }
                                    if (property_exists($masterRecord, $prefix . 'cali_certificate')) {
                                        $updateData[$prefix . 'cali_certificate'] = $cali_certificate;
                                    }
                                    if (property_exists($masterRecord, $prefix . 'cali_certificate_blob')) {
                                        $updateData[$prefix . 'cali_certificate_blob'] = $cali_certificate_blob;
                                    }

                                    if (!empty($updateData)) {
                                        DB::table($tableInfo['table'])
                                            ->where($tableInfo['pk'], $row['sr_table_pk_id'])
                                            ->update($updateData);
                                    }
                                }
                            }
                        }

                        $grn_supplier_details = GRNSupplierDetails::create([
                            'grnd_grn_id'     => $grn_data->grn_id,
                            'table_unique_id'     => !empty($row['table_unique_id']) ? $row['table_unique_id'] : "Manual",
                            'table_pk_id'     => !empty($row['table_pk_id']) ? $row['table_pk_id'] : null,
                            'grnd_item_id'    => $row['grnd_item_id'],
                            'sr_table_unique_id' => !empty($row['sr_table_unique_id']) ? $row['sr_table_unique_id'] : null,
                            'sr_table_pk_id'     => !empty($row['sr_table_pk_id']) ? $row['sr_table_pk_id'] : null,
                            'grnd_qty'        => $row['grnd_qty'],
                            'grnd_rate_unit'  => $row['grnd_rate_unit'],
                            'grnd_amount'     => $row['grnd_amount'],
                            'grnd_remark'     => $row['grnd_remark'] ?? null,
                            'last_cali_date'  => ($isCaliActive && !empty($row['ins_last_cali_date'])) ? Date::createFromFormat('d/m/Y', $row['ins_last_cali_date'])->format('Y-m-d') : null,
                            'next_cali_due_date' => ($isCaliActive && !empty($row['ins_next_cali_due_date'])) ? Date::createFromFormat('d/m/Y', $row['ins_next_cali_due_date'])->format('Y-m-d') : null,
                            'cali_freq'       => ($isCaliActive && isset($row['ins_cali_freq']) && $row['ins_cali_freq'] !== '') ? $row['ins_cali_freq'] : null,
                            'cali_certificate' => $isCaliActive ? $cali_certificate : null,
                            'cali_certificate_blob' => $isCaliActive ? $cali_certificate_blob : null,
                            'previous_last_cali_date' => $previous_last_cali_date,
                            'previous_next_cali_due_date' => $previous_next_cali_due_date,
                            'previous_cali_freq' => $previous_cali_freq,
                            'previous_cali_certificate' => $previous_cali_certificate,
                            'previous_cali_certificate_blob' => $previous_cali_certificate_blob,
                            'for_calibration' => $for_calibration,
                            'status'          => 'Y',
                            'previous_status_transaction_id' => null,
                            'current_status_transaction_id'  => null,
                        ]);

                    //    if($request->grn_type_id != "Against DC") {
                           stockEffect($LocationData->location_id, $row['grnd_item_id'],$row['grnd_item_id'],$row['grnd_qty'],0,$row['grnd_amount'],0,'Insert','U','GRN Supplier',$grn_data->grn_id, $row['sr_table_unique_id'] ?? null, $row['sr_table_pk_id'] ?? null);
                    //    }
                            if($request->grn_type_id == "Against DC" && !empty($row['sr_table_pk_id'])) {
                                changeStatusAndLocationForSrNo($row['sr_table_unique_id'], $row['sr_table_pk_id'], 'Active', 'Outside', $LocationData->location_id, 'Insert', $grn_supplier_details);
                            }

                    }
                }
            }

            DB::commit();

            $get_supplier = GRNSupplier::select('suppliers.supplier_name')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
                ->where('grn_supplier.grn_supplier_id', $grn_data->grn_supplier_id)
                ->first();

            $grn_number = !empty($grn_data->grn_number) ? '_' . str_replace('/', '_', $grn_data->grn_number) : "";

            $customer_for_wolh = !empty($get_supplier)
                ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $get_supplier->supplier_name)
                : "";

            $pdf_name = 'GRN_Supplier' . $grn_number . $customer_for_wolh;

            GeneratePdf($grn_data->grn_id, $pdf_name, 'grn_supplier', 'add');

            $encodedId = base64_encode($grn_data->grn_id);

            if (hasAccess("grn_supplier", "print")) {
                $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=grn_supplier");
            } else {
                $url = "";
            }

            return response()->json([
                'response_code' => '1',
                'url' => $url,
                'response_message' => getResponseMessage('store_success'),
            ]);

        } catch (\Exception $e) {

            report($e);
            DB::rollBack();

            $message = $e->getMessage();
            
            if (in_array($message, [
                    'Insufficient Stock',
                    'Invalid Location Code',
                    'Invalid Item'
                ]) || str_contains($message, 'Sr. No. Is Already') || str_contains($message, "You Can't Delete")
            ) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            } else{
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('store_error'),
                    'original_error' => $e->getMessage()
                ]);
            }
        }
    }

    public function edit(Request $request)
    {
        $isAnyPartInUse = false;
        $itemTypes = getItemType();
        $current_location_id = getCurrentLocation()->location_id;

        $grn_supplier_data =  DB::select('CALL grn_supplier_master(?)', [$request->id]);
        if(!empty($grn_supplier_data))
        {
            $grn_supplier_data = $grn_supplier_data[0];
            $grn_supplier_data->grn_date = $grn_supplier_data->grn_date != "" ? Date::createFromFormat('Y-m-d',  $grn_supplier_data->grn_date)->format('d/m/Y') : "";
            $grn_supplier_data->grn_challan_date = $grn_supplier_data->grn_challan_date != "" ? Date::createFromFormat('Y-m-d',  $grn_supplier_data->grn_challan_date)->format('d/m/Y') : "";

            $grn_number = !empty($grn_supplier_data->grn_number) ? '_'.str_replace('/', '_', $grn_supplier_data->grn_number) : "";

            $customer_for_wolh = !empty($grn_supplier_data) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $grn_supplier_data->supplier_name) : "";

            $grn_supplier_data->pdf_name = 'GRN_Supplier'.$grn_number.$customer_for_wolh;
            if(isset($grn_supplier_data->cmp_logo))
            {
                $grn_supplier_data->cmp_logo = base64_encode($grn_supplier_data->cmp_logo);
            }
            
        }
        
        $grn_supplier_details_data = DB::select('CALL grn_supplier_details(?)', [$request->id]);
       
        if($grn_supplier_details_data){
            foreach($grn_supplier_details_data as $dKey => $dVal)
            {
                $dVal->po_date = $dVal->po_date != "" ? Date::createFromFormat('Y-m-d',  $dVal->po_date)->format('d/m/Y') : "";
                $dVal->sup_dc_date = $dVal->sup_dc_date != "" ? Date::createFromFormat('Y-m-d',  $dVal->sup_dc_date)->format('d/m/Y') : "";
                // $dVal->main_group = $itemTypes[$dVal->main_group] ?? $dVal->main_group;
                if($grn_supplier_data->grn_type_id != "Manual") {
                    $dVal->pending_qty = $dVal->pending_qty + $dVal->grnd_qty;
                }
                 $dVal->name_for_display = $dVal->sr_no;
                  
                $dVal->ins_cali_freq = $dVal->cali_freq;
                $dVal->ins_last_cali_date = $dVal->last_cali_date ? Date::createFromFormat('Y-m-d', $dVal->last_cali_date)->format('d/m/Y') : null;
                $dVal->ins_next_cali_due_date = $dVal->next_cali_due_date ? Date::createFromFormat('Y-m-d', $dVal->next_cali_due_date)->format('d/m/Y') : null;
                $dVal->calibration_certificate_doc = $dVal->cali_certificate;
                if (property_exists($dVal, 'cali_certificate_blob')) {
                    $dVal->calibration_certificate_blob = $dVal->cali_certificate_blob ? base64_encode($dVal->cali_certificate_blob) : null;
                    unset($dVal->cali_certificate_blob);
                }
                if (property_exists($dVal, 'previous_cali_certificate_blob')) {
                    unset($dVal->previous_cali_certificate_blob);
                }
               
                     
                if ($grn_supplier_data->grn_type_id == "Against DC" && !empty($dVal->table_pk_id)) {
                    $dVal->for_calibration = DB::table('supplier_dc_details')
                        ->where('sup_dcd_id', $dVal->table_pk_id)
                        ->value('for_calibration');
                } else {
                    $dVal->for_calibration = 'No';
                }


              $PendingQty = DB::table(DB::raw("
                            (
                                SELECT item_id, table_pk_id, location_id, table_unique_id, pending_qty FROM rt_camera_pending_qty
                                UNION ALL
                                SELECT item_id, table_pk_id, location_id, table_unique_id, pending_qty FROM mpt_equipment_pending_qty
                                UNION ALL
                                SELECT item_id, table_pk_id, location_id, table_unique_id, pending_qty FROM mpt_material_pending_qty
                                UNION ALL
                                SELECT item_id, table_pk_id, location_id, table_unique_id, pending_qty FROM dpt_chemical_pending_qty
                                UNION ALL
                                SELECT item_id, table_pk_id, location_id, table_unique_id, pending_qty FROM ut_equipment_pending_qty
                                UNION ALL
                                SELECT item_id, table_pk_id, location_id, table_unique_id, pending_qty FROM instrument_pending_qty
                            ) as combined_views
                        "))
                        ->where('combined_views.item_id', $dVal->grnd_item_id)
                        ->where('combined_views.table_pk_id', $dVal->grnd_id)
                        ->where('combined_views.location_id', $current_location_id)
                        ->where('combined_views.table_unique_id', 'GRN')
                        ->sum('combined_views.pending_qty');
                 if($grn_supplier_data->grn_type_id == "Against DC") {
                    $total_qty = 0;           
                 }else{
                    $total_qty = $dVal->grnd_qty;
                 }
                               
                $isFound = $total_qty - $PendingQty;

                if($isFound > 0){
                    $dVal->in_use = true;
                    $dVal->used_qty = $isFound;
                    $isAnyPartInUse = true;
                } else {
                    $dVal->in_use = false;
                    $dVal->used_qty = 0;
                }

            }
            unset($dVal->sr_no);
        }

        if($isAnyPartInUse == true){
            $grn_supplier_data->in_use = true;
        } else {
            $grn_supplier_data->in_use = false;
        }

        if($grn_supplier_data)
        {
            return response()->json([
                'grn_supplier_data'         => $grn_supplier_data,
                'grn_supplier_details_data' => $grn_supplier_details_data,
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
        $year_data = getCurrentYearData();
        $LocationData = getCurrentLocation();

        DB::beginTransaction();
        $validated = $request->validate([
            'grn_sequence' => [
                'required',
                'max:155',
                Rule::unique('grn_supplier')
                    ->where(function ($query) use ($year_data, $LocationData) {
                        $query->where('year_id', $year_data->id)
                            ->where('current_location_id', $LocationData->location_id);
                    })
                    ->ignore($request->id, 'grn_id'),
            ],
        ], [
            'grn_sequence.unique' => "Duplicate GRN No. Found.",
            'grn_sequence.required' => 'Enter GRN No.',
        ]);

        try
        {
            $year_data = getCurrentYearData();
            $amount_rupee = $request->grn_total_amount;
            $amount_in_word = digitsToWords($amount_rupee);

            $grn_data = GRNSupplier::where('grn_id', $request->id)->update([
                'grn_number'        => $request->grn_number,
                'grn_sequence'      => $request->grn_sequence,
                'grn_date'          => $request->grn_date ? Date::createFromFormat('d/m/Y', $request->grn_date)->format('Y-m-d') : null,
                'grn_supplier_id'   => $request->grn_supplier_id,
                'grn_type_id'       => $request->grn_type_id,
                'grn_challan_number'=> $request->grn_challan_number,
                'grn_challan_date'  => $request->grn_challan_date ? Date::createFromFormat('d/m/Y', $request->grn_challan_date)->format('Y-m-d') : null,
                'grn_transporter'   => $request->grn_transporter,
                'mode_of_transport' => $request->mode_of_transport,   
                'grn_vehicle_number'=> $request->grn_vehicle_number,
                'grn_total_amount'  => $request->grn_total_amount,
                'grn_special_note'  => $request->grn_special_note,
                'amount_in_word'    => $amount_in_word ?? null,
                'prepared_by_user_id' => $request->prepared_by_user_id,
              //  'assign_format_no'  => $assign_format_no ?? null,
                'current_location_id' => $LocationData->location_id ?? null,
                'year_id'           => $year_data->id,
                'company_id'        => Auth::user()->company_id,
                'last_on'               => Carbon::now('Asia/Kolkata'),
                'last_by'               => Auth::id(),
            ]);

            if(!$grn_data)
            {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                ]);
            }

            $update_details =  GRNSupplierDetails::where('grnd_grn_id',$request->id)->update(['status' => 'D',]);
            $grn_details_data = json_decode($request->grn_details_data, true);
            foreach($grn_details_data as $ctVal)
            {
                if($ctVal['grnd_id'] == 0)
                {

                        $from_qty         = (float)$ctVal['grnd_qty'];
                        $next_qty         = 0;
                        $transaction_mode = 'I';
                        $grnd_id           = 0;
                        $table_pk_id           = $ctVal['table_pk_id'] ?? 0;

                        $checkQty = $this->qtyValidation($from_qty, $next_qty, $transaction_mode, $grnd_id, $table_pk_id, $request->grn_type_id);
                        if ($checkQty) {
                            return $checkQty;
                        }

                        // Move certificate file from temp to uploads
                        $cali_certificate = null;
                        $cali_certificate_blob = null;
                        if (!empty($ctVal['calibration_certificate_doc'])) {
                            $fileHelper = new \App\Models\File();
                            $isFound = $fileHelper->getFileFromTemp($ctVal['calibration_certificate_doc'], 'calibration_certificate');
                            if ($isFound) {
                                $cali_certificate = $isFound;
                                if (file_exists(storage_path('app/public/' . $isFound))) {
                                    $cali_certificate_blob = file_get_contents(storage_path('app/public/' . $isFound));
                                }
                            } else {
                                $cali_certificate = $ctVal['calibration_certificate_doc'];
                                if (file_exists(storage_path('app/public/' . $cali_certificate))) {
                                    $cali_certificate_blob = file_get_contents(storage_path('app/public/' . $cali_certificate));
                                }
                            }
                        }

                        $for_calibration = 'No';
                        if (($ctVal['table_unique_id'] ?? '') === 'Against DC' && !empty($ctVal['table_pk_id'])) {
                            $for_calibration_val = DB::table('supplier_dc_details')
                                ->where('sup_dcd_id', $ctVal['table_pk_id'])
                                ->value('for_calibration');
                            if ($for_calibration_val === 'Yes') {
                                $for_calibration = 'Yes';
                            }
                        }
                        $isCaliActive = ($request->grn_type_id == 'Against DC' && $for_calibration == 'Yes');

                        // Load previous values from master if serial number is present
                        $previous_last_cali_date = null;
                        $previous_next_cali_due_date = null;
                        $previous_cali_freq = null;
                        $previous_cali_certificate = null;
                        $previous_cali_certificate_blob = null;

                        if ($isCaliActive && !empty($ctVal['sr_table_unique_id']) && !empty($ctVal['sr_table_pk_id'])) {
                            $tableMap = [
                                'rt_camera' => ['table' => 'rt_camera', 'pk' => 'rt_camera_id', 'prefix' => 'rt_'],
                                'mpt_equipment' => ['table' => 'equipment_mpt', 'pk' => 'em_id', 'prefix' => 'em_'],
                                'mpt_material' => ['table' => 'material_mpt', 'pk' => 'mm_id', 'prefix' => 'mm_'],
                                'ut_equipment' => ['table' => 'equipment_ut', 'pk' => 'eu_id', 'prefix' => 'eu_'],
                                'dpt_chemical' => ['table' => 'dpt_chemical', 'pk' => 'dpt_id', 'prefix' => 'dpt_'],
                                'instrument' => ['table' => 'instrument', 'pk' => 'ins_id', 'prefix' => 'ins_'],
                                'probe_ut' => ['table' => 'probe_ut', 'pk' => 'pu_id', 'prefix' => 'pu_'],
                            ];

                            if (isset($tableMap[$ctVal['sr_table_unique_id']])) {
                                $tableInfo = $tableMap[$ctVal['sr_table_unique_id']];
                                $prefix = $tableInfo['prefix'];

                                $masterRecord = DB::table($tableInfo['table'])
                                    ->where($tableInfo['pk'], $ctVal['sr_table_pk_id'])
                                    ->first();

                                if ($masterRecord) {
                                    $previous_last_cali_date = $masterRecord->{$prefix . 'last_cali_date'} ?? null;
                                    $previous_next_cali_due_date = $masterRecord->{$prefix . 'next_cali_due_date'} ?? null;
                                    $previous_cali_freq = $masterRecord->{$prefix . 'cali_freq'} ?? null;
                                    $previous_cali_certificate = $masterRecord->{$prefix . 'cali_certificate'} ?? null;
                                    $previous_cali_certificate_blob = $masterRecord->{$prefix . 'cali_certificate_blob'} ?? null;

                                    // Update master with new calibration dates
                                    $updateData = [];
                                    if (property_exists($masterRecord, $prefix . 'last_cali_date')) {
                                        $updateData[$prefix . 'last_cali_date'] = !empty($ctVal['ins_last_cali_date']) ? Date::createFromFormat('d/m/Y', $ctVal['ins_last_cali_date'])->format('Y-m-d') : null;
                                    }
                                    if (property_exists($masterRecord, $prefix . 'next_cali_due_date')) {
                                        $updateData[$prefix . 'next_cali_due_date'] = !empty($ctVal['ins_next_cali_due_date']) ? Date::createFromFormat('d/m/Y', $ctVal['ins_next_cali_due_date'])->format('Y-m-d') : null;
                                    }
                                    if (property_exists($masterRecord, $prefix . 'cali_freq')) {
                                         $updateData[$prefix . 'cali_freq'] = (isset($ctVal['ins_cali_freq']) && $ctVal['ins_cali_freq'] !== '') ? $ctVal['ins_cali_freq'] : null;
                                     }
                                    if (property_exists($masterRecord, $prefix . 'cali_certificate')) {
                                        $updateData[$prefix . 'cali_certificate'] = $cali_certificate;
                                    }
                                    if (property_exists($masterRecord, $prefix . 'cali_certificate_blob')) {
                                        $updateData[$prefix . 'cali_certificate_blob'] = $cali_certificate_blob;
                                    }

                                    if (!empty($updateData)) {
                                        DB::table($tableInfo['table'])
                                            ->where($tableInfo['pk'], $ctVal['sr_table_pk_id'])
                                            ->update($updateData);
                                    }
                                }
                            }
                        }

                        $grn_supplier_details = GRNSupplierDetails::create([
                            'grnd_grn_id'     => $request->id,
                            'table_unique_id'     => !empty($ctVal['table_unique_id']) ? $ctVal['table_unique_id'] : "Manual",
                            'table_pk_id'     => !empty($ctVal['table_pk_id']) ? $ctVal['table_pk_id'] : null,
                            'grnd_item_id'    => $ctVal['grnd_item_id'],
                            'sr_table_unique_id' => !empty($ctVal['sr_table_unique_id']) ? $ctVal['sr_table_unique_id'] : null,
                            'sr_table_pk_id'     => !empty($ctVal['sr_table_pk_id']) ? $ctVal['sr_table_pk_id'] : null,
                            'grnd_qty'        => $ctVal['grnd_qty'],
                            'grnd_rate_unit'  => $ctVal['grnd_rate_unit'],
                            'grnd_amount'     => $ctVal['grnd_amount'],
                            'grnd_remark'     => $ctVal['grnd_remark'] ?? null,
                            'last_cali_date'  => ($isCaliActive && !empty($ctVal['ins_last_cali_date'])) ? Date::createFromFormat('d/m/Y', $ctVal['ins_last_cali_date'])->format('Y-m-d') : null,
                            'next_cali_due_date' => ($isCaliActive && !empty($ctVal['ins_next_cali_due_date'])) ? Date::createFromFormat('d/m/Y', $ctVal['ins_next_cali_due_date'])->format('Y-m-d') : null,
                            'cali_freq'       => ($isCaliActive && isset($ctVal['ins_cali_freq']) && $ctVal['ins_cali_freq'] !== '') ? $ctVal['ins_cali_freq'] : null,
                            'cali_certificate' => $isCaliActive ? $cali_certificate : null,
                            'cali_certificate_blob' => $isCaliActive ? $cali_certificate_blob : null,
                            'previous_last_cali_date' => $previous_last_cali_date,
                            'previous_next_cali_due_date' => $previous_next_cali_due_date,
                            'previous_cali_freq' => $previous_cali_freq,
                            'previous_cali_certificate' => $previous_cali_certificate,
                            'previous_cali_certificate_blob' => $previous_cali_certificate_blob,
                            'for_calibration' => $for_calibration,
                            'status'          => 'Y',
                            'previous_status_transaction_id' => null,
                            'current_status_transaction_id'  => null,
                        ]);

                        // if($request->grn_type_id != "Against DC") {
                           stockEffect($LocationData->location_id, $ctVal['grnd_item_id'],$ctVal['grnd_item_id'],$ctVal['grnd_qty'],0,$ctVal['grnd_amount'],0,'Insert','U','GRN Supplier',$request->id, $ctVal['sr_table_unique_id'] ?? null, $ctVal['sr_table_pk_id'] ?? null);

                            if($request->grn_type_id == "Against DC" && !empty($ctVal['sr_table_pk_id'])) {
                                changeStatusAndLocationForSrNo($ctVal['sr_table_unique_id'], $ctVal['sr_table_pk_id'], 'Active', 'Outside',$LocationData->location_id, 'Insert', $grn_supplier_details);
                            }
                       //}
                }
                else
                {
                    $olddata = GRNSupplierDetails::where('grnd_id', $ctVal['grnd_id'])->select('grnd_amount','grnd_item_id','grnd_qty')->first();

                    $grnd_qty       = (float)$ctVal['grnd_qty'];
                    $from_qty         = max(0, $grnd_qty - $olddata->grnd_qty);   // increase
                    $next_qty         = max(0, $olddata->grnd_qty - $grnd_qty);   // decrease
                    $transaction_mode = 'U';
                    $grnd_id           = $ctVal['grnd_id'];
                    $table_pk_id           = $ctVal['table_pk_id'] ?? 0;

                    $checkQty = $this->qtyValidation($from_qty, $next_qty, $transaction_mode, $grnd_id, $table_pk_id, $request->grn_type_id);
                    if ($checkQty) {
                        return $checkQty;
                    }

                   

                    GRNSupplierDetails::where('grnd_id', $ctVal['grnd_id'])->update([

                            'table_unique_id'     => !empty($ctVal['table_unique_id']) ? $ctVal['table_unique_id'] : "Manual",
                            'table_pk_id'     => !empty($ctVal['table_pk_id']) ? $ctVal['table_pk_id'] : null,
                            'grnd_item_id'    => $ctVal['grnd_item_id'],
                            'sr_table_unique_id' => !empty($ctVal['sr_table_unique_id']) ? $ctVal['sr_table_unique_id'] : null,
                            'sr_table_pk_id'     => !empty($ctVal['sr_table_pk_id']) ? $ctVal['sr_table_pk_id'] : null,
                            'grnd_qty'        => $ctVal['grnd_qty'],
                            'grnd_rate_unit'  => $ctVal['grnd_rate_unit'],
                            'grnd_amount'     => $ctVal['grnd_amount'],
                            'grnd_remark'     => $ctVal['grnd_remark'] ?? null,
                            'status'          => 'Y',
                    ]);

                    // if($request->grn_type_id != "Against DC") {

                       stockEffect($LocationData->location_id,$ctVal['grnd_item_id'], $olddata->grnd_item_id,$ctVal['grnd_qty'],$olddata->grnd_qty,$ctVal['grnd_amount'],$olddata->grnd_amount,'Update','U','GRN Supplier',$ctVal['grnd_grn_id'], $ctVal['sr_table_unique_id'] ?? $olddata->sr_table_unique_id ?? null, $ctVal['sr_table_pk_id'] ?? $olddata->sr_table_pk_id ?? null);
                    // }
                }
            }
            $deleteDetails = GRNSupplierDetails::where('grnd_grn_id',$request->id)->where('status','D')->get();

                if(!empty($deleteDetails)){
                    foreach($deleteDetails as $dkey => $dval){

                        // if($request->grn_type_id != "Against DC") {

                            stockEffect($LocationData->location_id,$dval['grnd_item_id'],$dval['grnd_item_id'],0,$dval['grnd_qty'],0,$dval['grnd_amount'],'Delete','U','GRN Supplier',$dval['grnd_grn_id'], $dval['sr_table_unique_id'] ?? null, $dval['sr_table_pk_id'] ?? null);

                            if($request->grn_type_id == "Against DC" && ($dval['sr_table_unique_id'] != null || $dval['sr_table_unique_id'] != '')){
                                changeStatusAndLocationForSrNo($dval['sr_table_unique_id'],$dval['sr_table_pk_id'],'Active','Outside',$LocationData->location_id,'Delete', $dval);
                            }
                            $this->restoreMasterCalibration($dval);
                        // }
                    }

                }

            GRNSupplierDetails::where('grnd_grn_id',$request->id)->where('status','D')->delete();

            if($grn_data)
            {
                DB::commit();

                $get_suppliers = GRNSupplier::select('suppliers.supplier_name')
                    ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
                    ->where('grn_supplier.grn_supplier_id', $request->grn_supplier_id)
                    ->first();
                    
                $grn_number = !empty($request->grn_number) ? '_'.str_replace('/', '_', $request->grn_number) : "";

                $customer_for_wolh = !empty($get_suppliers) ? '_' . preg_replace('/[^A-Za-z0-9_]/', '_', $get_suppliers->supplier_name) : "";

                $pdf_name = 'GRN_Supplier'.$grn_number.$customer_for_wolh;
                GeneratePdf($request->id,$pdf_name,'grn_supplier','edit');
                $encodedId = base64_encode($request->id);
                
                if(hasAccess("grn_supplier", "print")){  
                    $url = url("/check-file_exists?id={$encodedId}&name={$pdf_name}&type=grn_supplier");
                }else{   
                    $url ="";
                }
               
                return response()->json([
                    'response_code' => '1',
                    'url' => $url,
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
             $message = $e->getMessage();
            if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451)
            {
                $error_msg = "This Is Used Somewhere, You Can't Delete";
            }
             else if(in_array($message, [
                    'Insufficient Stock',
                    'Invalid Location Code',
                    'Invalid Item'
                ]) || str_contains($message, 'Sr. No. Is Already') || str_contains($message, "You Can't Delete"))
            {
                $error_msg = $message;
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

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try
        {

            $grn_data =  DB::select('CALL grn_used_list(?)', [$request->id]);


            if(!empty($grn_data)){ 
                return response()->json([
                    'response_code' => '0',
                    'response_message' => "You Can't Delete, GRN - Supplier Is Used In " . $grn_data[0]->table_name . ".",
                ]);
            }

            $LocationData = getCurrentLocation()->location_id;

            $grnsupplierQty = GRNSupplierDetails::where('grnd_grn_id',$request->id)->get();
            $grnsupplier = GRNSupplier::where('grn_id',$request->id)->first();

            foreach($grnsupplierQty as $gkey=>$gval){
                // if($grnsupplier->grn_type_id != "Against DC") {
                    stockEffect($LocationData,$gval['grnd_item_id'],$gval['grnd_item_id'],0,$gval['grnd_qty'],0,$gval['grnd_amount'],'Delete','U','GRN Supplier',$gval['grnd_grn_id'], $gval['sr_table_unique_id'] ?? null, $gval['sr_table_pk_id'] ?? null);
                // }

                 if($grnsupplier->grn_type_id == "Against DC" && ($gval['sr_table_unique_id'] != null || $gval['sr_table_unique_id'] != '')){
                    changeStatusAndLocationForSrNo($gval['sr_table_unique_id'],$gval['sr_table_pk_id'],'Active','Outside',$LocationData,'Delete', $gval);
                }
                
                $this->restoreMasterCalibration($gval);
            }



            
            GRNSupplier::where('grn_id',$request->id)->delete();
            GRNSupplierDetails::where('grnd_grn_id',$request->id)->delete();

             DB::commit();
            return response()->json([
                'response_code' => '1',
                'response_message' => getResponseMessage('delete_success'),
            ]);
        }
         catch(\Exception $e)
        {
            report($e);
            DB::rollBack(); 
            $message = $e->getMessage();
            if(isset($e->errorInfo[1]) && $e->errorInfo[1] == 1451)
            {
                $error_msg = "This Is Used Somewhere, You Can't Delete";
            }
            else if(in_array($message, [
                    'Insufficient Stock',
                    'Invalid Location Code',
                    'Invalid Item'
                ]) || str_contains($message, 'Sr. No. Is Already') || str_contains($message, "You Can't Delete"))
            {
                $error_msg = $message;
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

    public function getPendingSupplierForGRN(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();
        $current_location = getCurrentLocation();

        if($request->grn_type_id == "Against PO"){
            $get_po_supplier = DB::table('pending_purchase_order_qty as pend')->select([
            'suppliers.supplier_name','suppliers.id',
            ])
            ->leftJoin('purchase_order_details', 'purchase_order_details.pod_id', '=', 'pend.pod_id')
            ->leftJoin('purchase_order', 'purchase_order.po_id', '=', 'purchase_order_details.pod_po_id')           
            ->leftJoin('suppliers', 'suppliers.id', '=', 'purchase_order.po_supplier_id')            
            ->whereIn('purchase_order.year_id', $yearIds)
            ->Where('purchase_order.ship_to_location_id', $current_location->location_id)
            ->where('pend.pending_qty', '>', 0)
            ->distinct()
            ->orderBy('suppliers.supplier_name', 'asc')
            ->get();
           
        }elseif($request->grn_type_id == "Against DC"){
            $get_po_supplier = DB::table('pending_supplier_dc_qty as pend')->select([
            'suppliers.supplier_name','suppliers.id',
            ])
            ->leftJoin('supplier_dc_details', 'supplier_dc_details.sup_dcd_id', '=', 'pend.sup_dcd_id')
            ->leftJoin('supplier_dc', 'supplier_dc.sup_dc_id', '=', 'supplier_dc_details.sup_dcd_dc_id')           
            ->leftJoin('suppliers', 'suppliers.id', '=', 'supplier_dc.supplier_id')            
            ->whereIn('supplier_dc.year_id', $yearIds)
            ->Where('supplier_dc.current_location_id', $current_location->location_id)
            ->where('pend.pending_qty', '>', 0)
            ->distinct()
            ->orderBy('suppliers.supplier_name', 'asc')
            ->get();
        }else{
            $get_po_supplier =  Supplier::select('id','supplier_name')->orderBy('supplier_name','asc')->get();

        }

        return response()->json([
            'response_code' => 1,
            'get_po_supplier'  => $get_po_supplier,
        ]);
    }

    public function getPendingPoListForGrn(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();
        $current_location = getCurrentLocation();

        $itemTypes = getItemType();

        $po_data = DB::table('pending_purchase_order_qty as pend')->select(['purchase_order_details.pod_id','purchase_order.po_id','purchase_order.po_number','purchase_order.po_date','purchase_order.ref_no_date','bill_to.location_name as bill_to','ship_to.location_name as ship_to','item.item_name','item_group.item_group','item.item_type as main_group','pend.pending_qty','unit.unit','purchase_order_details.pod_del_date','purchase_order_details.pod_remark','admin.person_name as prepared_by',])
        ->leftJoin('purchase_order_details','purchase_order_details.pod_id','=','pend.pod_id')
        ->leftJoin('purchase_order', 'purchase_order.po_id', '=', 'purchase_order_details.pod_po_id')    
        ->leftJoin('item','item.id','=','purchase_order_details.pod_item_id')  
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->leftJoin('admin', 'admin.id', '=', 'purchase_order.prepared_by_user_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')  
        ->leftJoin('location as bill_to', 'bill_to.location_id', '=', 'purchase_order.bill_to_location_id')
        ->leftJoin('location as ship_to', 'ship_to.location_id', '=', 'purchase_order.ship_to_location_id')
        ->where('purchase_order.po_supplier_id',$request->grn_supplier_id)
        ->whereIn('purchase_order.year_id',$yearIds)  
        ->Where('purchase_order.ship_to_location_id', $current_location->location_id)
        ->where('pend.pending_qty', '>', 0)
        ->get();

        
        if ($po_data != null) {
            foreach ($po_data as $cpKey => $cpVal) {
                if ($cpVal->po_date != null) {
                    $cpVal->po_date = Date::createFromFormat('Y-m-d', $cpVal->po_date)->format('d/m/Y');
                }
                if ($cpVal->pod_del_date != null) {
                    $cpVal->pod_del_date = Date::createFromFormat('Y-m-d', $cpVal->pod_del_date)->format('d/m/Y');
                }
                $cpVal->main_group = $itemTypes[$cpVal->main_group] ?? $item->main_group;
               
            }
        }


        $po_data = $po_data->sortBy('purchase_order.po_id')->values();

        

        if ($po_data != null) {
            return response()->json([
                'response_code' => 1,
                'po_data'  => $po_data,            
            ]);
        }else{
            return response()->json([
                'response_code' => 1,
                'po_data'  => [],            
            ]);

        }

    }

    public function getPendingPoForGrn(Request $request){

        $yearIds = getCompanyYearIdsToTill();
        $itemTypes = getItemType();
        $request->pod_ids = explode(',', $request->pod_ids);

        $po_data = DB::table('pending_purchase_order_qty as pend')->select(['purchase_order_details.pod_id','purchase_order_details.pod_rate_unit','purchase_order.po_id','purchase_order.po_number','purchase_order.po_date','item.item_name','item_group.item_group','item.item_type as main_group','pend.pending_qty','purchase_order_details.pod_item_id as grnd_item_id','unit.unit',])
        ->leftJoin('purchase_order_details','purchase_order_details.pod_id','=','pend.pod_id')
        ->leftJoin('purchase_order', 'purchase_order.po_id', '=', 'purchase_order_details.pod_po_id')    
        ->leftJoin('item','item.id','=','purchase_order_details.pod_item_id')  
        ->leftJoin('unit','unit.id','=','item.unit_id')  
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')       
        ->whereIn('purchase_order_details.pod_id', $request->pod_ids)
        ->where('pend.pending_qty', '>', 0)
        ->get();

      
        if ($po_data != null) {
            foreach ($po_data as $cpKey => $cpVal) {

                if ($cpVal->po_date != null) {
                    $cpVal->po_date = Date::createFromFormat('Y-m-d', $cpVal->po_date)->format('d/m/Y');
                }               

                $cpVal->main_group = $itemTypes[$cpVal->main_group] ?? $cpVal->main_group;

                $cpVal->table_unique_id = "Against PO";
                $cpVal->table_pk_id = $cpVal->pod_id;
                $cpVal->grnd_qty = $cpVal->pending_qty;
                $cpVal->grnd_rate_unit = $cpVal->pod_rate_unit;
                $cpVal->grnd_amount = $cpVal->grnd_rate_unit *  $cpVal->grnd_qty;
                $cpVal->mode = "Insert";
            }
        }

        $po_data = $po_data->sortBy('po_id')
        ->sortBy('pod_id')
        ->values();

       
        
        if ($po_data != null) {
            return response()->json([
                'response_code' => '1',
                'po_data' => $po_data,
               
            ]);
        } else {
            return response()->json([
                'response_code' => '1',
                'po_data' => []
            ]);
        }
    }

    public function getSupplierDCListForGrn(Request $request)
    {
        $yearIds = getCompanyYearIdsToTill();
        $current_location = getCurrentLocation();
        $itemTypes = getItemType();

        $dc_data = DB::table('pending_supplier_dc_qty as pend')->select(['supplier_dc_details.sup_dcd_id','supplier_dc.sup_dc_id','supplier_dc.sup_dc_number','supplier_dc.sup_dc_date','supplier_dc.ref_no_date','item.item_name','item_group.item_group','item.item_type as main_group','pend.pending_qty','unit.unit','sr.name_for_display','supplier_dc_details.remark','admin.person_name as prepared_by',])
        ->leftJoin('supplier_dc_details', 'supplier_dc_details.sup_dcd_id', '=', 'pend.sup_dcd_id')
        ->leftJoin('supplier_dc', 'supplier_dc.sup_dc_id', '=', 'supplier_dc_details.sup_dcd_dc_id') 
        ->leftJoin('item','item.id','=','supplier_dc_details.item_id')  
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->leftJoin('admin', 'admin.id', '=', 'supplier_dc.prepared_by_user_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')  
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'supplier_dc_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'supplier_dc_details.sr_table_unique_id');
        })
        ->where('supplier_dc.supplier_id',$request->grn_supplier_id)
        ->whereIn('supplier_dc.year_id',$yearIds)  
        ->Where('supplier_dc.current_location_id', $current_location->location_id)
        ->where('pend.pending_qty', '>', 0)
        ->get();

        
        if ($dc_data != null) {
            foreach ($dc_data as $cpKey => $cpVal) {
                if ($cpVal->sup_dc_date != null) {
                    $cpVal->sup_dc_date = Date::createFromFormat('Y-m-d', $cpVal->sup_dc_date)->format('d/m/Y');
                }
                // if ($cpVal->ref_no_date != null) {
                //     $cpVal->ref_no_date = Date::createFromFormat('Y-m-d', $cpVal->ref_no_date)->format('d/m/Y');
                // }
                $cpVal->main_group = $itemTypes[$cpVal->main_group] ?? $cpVal->main_group;
               
            }
        }


        $dc_data = $dc_data->sortBy('supplier_dc.sup_dc_id')->values();

        

        if ($dc_data != null) {
            return response()->json([
                'response_code' => 1,
                'dc_data'  => $dc_data,            
            ]);
        }else{
            return response()->json([
                'response_code' => 1,
                'dc_data'  => [],            
            ]);

        }

    }

    public function getPendingSupplierDCForGrn(Request $request){

        $yearIds = getCompanyYearIdsToTill();
        $itemTypes = getItemType();
        $request->sup_dcd_ids = explode(',', $request->sup_dcd_ids);

        $dc_data = DB::table('pending_supplier_dc_qty as pend')->select(['supplier_dc_details.sup_dcd_id','supplier_dc.sup_dc_id','supplier_dc.sup_dc_number','supplier_dc.sup_dc_date','supplier_dc.ref_no_date','item.item_name','item.id as grnd_item_id','item_group.item_group','item.item_type as main_group','pend.pending_qty','unit.unit','sr.name_for_display', 'supplier_dc_details.sr_table_unique_id','supplier_dc_details.sr_table_pk_id','supplier_dc_details.remark','admin.person_name as prepared_by','supplier_dc_details.for_calibration',])
        ->leftJoin('supplier_dc_details', 'supplier_dc_details.sup_dcd_id', '=', 'pend.sup_dcd_id')
        ->leftJoin('supplier_dc', 'supplier_dc.sup_dc_id', '=', 'supplier_dc_details.sup_dcd_dc_id') 
        ->leftJoin('item','item.id','=','supplier_dc_details.item_id')  
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->leftJoin('admin', 'admin.id', '=', 'supplier_dc.prepared_by_user_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')  
        ->leftJoin('item_sr_no_name_for_display as sr', function($join) {
            $join->on('sr.sr_table_pk_id', '=', 'supplier_dc_details.sr_table_pk_id')
                ->on('sr.sr_table_unique_id', '=', 'supplier_dc_details.sr_table_unique_id');
        })    
        ->whereIn('supplier_dc_details.sup_dcd_id', $request->sup_dcd_ids)
        ->where('pend.pending_qty', '>', 0)
        ->get();

      
        if ($dc_data != null) {
            foreach ($dc_data as $cpKey => $cpVal) {

                if ($cpVal->sup_dc_date != null) {
                    $cpVal->sup_dc_date = Date::createFromFormat('Y-m-d', $cpVal->sup_dc_date)->format('d/m/Y');
                }               

                $cpVal->main_group = $itemTypes[$cpVal->main_group] ?? $cpVal->main_group;

                $cpVal->table_unique_id = "Against DC";
                $cpVal->table_pk_id = $cpVal->sup_dcd_id;
                $cpVal->grnd_qty = $cpVal->pending_qty;
            }
        }

        $dc_data = $dc_data->sortBy('supplier_dc.sup_dc_id')
        ->sortBy('supplier_dc_details.sup_dcd_id')
        ->values();

       
        
        if ($dc_data != null) {
            return response()->json([
                'response_code' => '1',
                'dc_data' => $dc_data,
               
            ]);
        } else {
            return response()->json([
                'response_code' => '1',
                'dc_data' => []
            ]);
        }
    }


    public function qtyValidation($from_qty, $next_qty, $transaction_mode, $grnd_id, $table_pk_id = null, $grn_type_id = null)
    {      

        // ===================== 1. GRN SUPPLIER VALIDATION FOR  PURCHASE ORDER =====================
        // if ($from_qty > 0 && $grn_type_id === 'Against PO' && !empty($table_pk_id) && $table_pk_id != 0) {

        //     $pending_order_qty = DB::table('pending_purchase_order_qty')
        //         ->where('pod_id', $table_pk_id)
        //         ->value('pending_qty') ?? 0;

        //     if ((float)$pending_order_qty < (float)$from_qty) {
        //         DB::rollBack();
        //         return response()->json([
        //             'response_code'    => '0',
        //             'response_message' => 'Purchase Order Qty. Used.',
        //         ]);
        //     }
        // }


        if ($from_qty > 0 && $grn_type_id === 'Against DC' && !empty($table_pk_id) && $table_pk_id != 0) {

            $pending_order_qty = DB::table('pending_supplier_dc_qty')
                ->where('sup_dcd_id', $table_pk_id)
                ->value('pending_qty') ?? 0;

            if ((float)$pending_order_qty < (float)$from_qty) {
                DB::rollBack();
                return response()->json([
                    'response_code'    => '0',
                    'response_message' => 'DC Qty. Used.',
                ]);
            }
        }


        // ===================== 2. GRN VALIDATION FOR Camera - RT || Equipment - MPT || Material - MPT  || Chemical - DPT || Equipment - UT || Instrument =====================
        if ($next_qty > 0  && $grn_type_id != 'Against DC') {

            $pending_po_qty = DB::table(DB::raw("
                            (
                                SELECT item_id, table_pk_id, location_id, table_unique_id, pending_qty FROM rt_camera_pending_qty
                                UNION ALL
                                SELECT item_id, table_pk_id, location_id, table_unique_id, pending_qty FROM mpt_equipment_pending_qty
                                UNION ALL
                                SELECT item_id, table_pk_id, location_id, table_unique_id, pending_qty FROM mpt_material_pending_qty
                                UNION ALL
                                SELECT item_id, table_pk_id, location_id, table_unique_id, pending_qty FROM dpt_chemical_pending_qty
                                UNION ALL
                                SELECT item_id, table_pk_id, location_id, table_unique_id, pending_qty FROM ut_equipment_pending_qty
                                UNION ALL
                                SELECT item_id, table_pk_id, location_id, table_unique_id, pending_qty FROM instrument_pending_qty
                            ) as combined_views
                        "))
                        ->where('combined_views.table_unique_id', 'GRN')
                        ->where('combined_views.table_pk_id', $grnd_id)
                        ->sum('combined_views.pending_qty');

            if ((float)$pending_po_qty < (float)$next_qty) {
                DB::rollBack();

                $message = ($transaction_mode === 'D')
                    ? "You Can't Delete, GRN Qty. Is Used."
                    : "You Can't Update, GRN Qty. Is Used.";

                return response()->json([
                    'response_code'    => '0',
                    'response_message' => $message,
                ]);
            }
        }

        return null;   
    }
    public function getPODetailsForGRN(Request $request)
    {
        $supplierID = $request->supplier_id;
        $itemID = $request->item_id;

        $grn_data_1 = collect(); // important
        $grnTypeID = ['Against PO', 'Manual'];

        if (isset($supplierID) && !empty($supplierID)) {

            // Same Supplier
            $grn_data_1 = GRNSupplier::select(
                    'grn_id','grn_number','grn_date',
                    'grn_supplier_details.grnd_qty',
                    'grn_supplier_details.grnd_rate_unit',
                    'suppliers.supplier_name'
                )
                ->leftJoin('grn_supplier_details', 'grn_supplier_details.grnd_grn_id', '=', 'grn_supplier.grn_id')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
                ->whereIn('grn_supplier.grn_type_id',$grnTypeID)
                ->where('grn_supplier_id', $supplierID)
                ->where('grnd_item_id', $itemID)
                ->orderBy('grn_id', 'desc')
                ->limit(5)
                ->get();

            // Other Supplier
            $grn_data_2 = GRNSupplier::select(
                    'grn_id','grn_number','grn_date',
                    'grn_supplier_details.grnd_qty',
                    'grn_supplier_details.grnd_rate_unit',
                    'suppliers.supplier_name'
                )
                ->leftJoin('grn_supplier_details', 'grn_supplier_details.grnd_grn_id', '=', 'grn_supplier.grn_id')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
                ->whereIn('grn_supplier.grn_type_id',$grnTypeID)
                ->where('grn_supplier_id', '!=', $supplierID)
                ->where('grnd_item_id', $itemID)
                ->orderBy('grn_id', 'desc')
                ->limit(5)
                ->get();

        } else {
            // Only all suppliers
            $grn_data_2 = GRNSupplier::select(
                    'grn_id','grn_number','grn_date',
                    'grn_supplier_details.grnd_qty',
                    'grn_supplier_details.grnd_rate_unit',
                    'suppliers.supplier_name'
                )
                ->leftJoin('grn_supplier_details', 'grn_supplier_details.grnd_grn_id', '=', 'grn_supplier.grn_id')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
                ->whereIn('grn_supplier_details.table_unique_id',$grnTypeID)
                ->where('grnd_item_id', $itemID)
                ->orderBy('grn_id', 'desc')
                ->limit(5)
                ->get();
        }

        // Merge safely 
        $grn_data = $grn_data_1->merge($grn_data_2)->values();

        // Format date
        foreach ($grn_data as $item) {
            $item->grn_date = $item->grn_date != "" ? Date::createFromFormat('Y-m-d', $item->grn_date)->format('d/m/Y') : "";
        }

        return response()->json([
            'response_code' => '1',
            'grn_data' => $grn_data->toArray()
        ]);
    }
    public function getServicePODetailsForGRN(Request $request)
    {
        $supplierID = $request->supplier_id;
        $itemID = $request->item_id;

        $grn_data_1 = collect(); // important
        $grnTypeID = ['Against DC'];

        if (isset($supplierID) && !empty($supplierID)) {

            // Same Supplier
            // $grn_data_1 = GRNSupplier::select(
            //         'grn_id','grn_number','grn_date',
            //         'grn_supplier_details.grnd_qty',
            //         'grn_supplier_details.grnd_rate_unit',
            //         'suppliers.supplier_name'
            //     )
            //     ->leftJoin('grn_supplier_details', 'grn_supplier_details.grnd_grn_id', '=', 'grn_supplier.grn_id')
            //     ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
            //     ->whereIn('grn_supplier.grn_type_id',$grnTypeID)
            //     ->where('grn_supplier_id', $supplierID)
            //     ->where('grnd_item_id', $itemID)
            //     ->orderBy('grn_id', 'desc')
            //     ->limit(5)
            //     ->get();

            $grn_data_1 = GRNSupplier::select(
                    'grn_id','grn_number','grn_date',
                    'grn_supplier_details.grnd_qty',
                    'grn_supplier_details.grnd_rate_unit',
                    'suppliers.supplier_name',
                    'service_po.purpose',
                )
                ->leftJoin('grn_supplier_details', 'grn_supplier_details.grnd_grn_id', '=', 'grn_supplier.grn_id')
                ->leftJoin('supplier_dc_details', 'supplier_dc_details.sup_dcd_id', '=', 'grn_supplier_details.table_pk_id')
                ->leftJoin('supplier_dc', 'supplier_dc.sup_dc_id', '=', 'supplier_dc_details.sup_dcd_dc_id')
                ->leftJoin('service_po_details', 'service_po_details.ser_pod_id', '=', 'supplier_dc_details.ser_pod_id')
                
                ->leftJoin('service_po', 'service_po.ser_po_id', '=', 'service_po_details.ser_pod_po_id')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
                ->whereIn('grn_supplier_details.table_unique_id',$grnTypeID)
                ->where('grn_supplier_id', $supplierID)
                ->where('grnd_item_id', $itemID)
                ->where('supplier_dc.sup_dc_type_id','=','Returnable - Service PO')
                ->orderBy('grn_id', 'desc')
                ->limit(5)
                ->get();

            // Other Supplier
            // $grn_data_2 = GRNSupplier::select(
            //         'grn_id','grn_number','grn_date',
            //         'grn_supplier_details.grnd_qty',
            //         'grn_supplier_details.grnd_rate_unit',
            //         'suppliers.supplier_name'
            //     )
            //     ->leftJoin('grn_supplier_details', 'grn_supplier_details.grnd_grn_id', '=', 'grn_supplier.grn_id')
            //     ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
            //     ->whereIn('grn_supplier.grn_type_id',$grnTypeID)
            //     ->where('grn_supplier_id', '!=', $supplierID)
            //     ->where('grnd_item_id', $itemID)
            //     ->orderBy('grn_id', 'desc')
            //     ->limit(5)
            //     ->get();
            $grn_data_2 = GRNSupplier::select(
                    'grn_id','grn_number','grn_date',
                    'grn_supplier_details.grnd_qty',
                    'grn_supplier_details.grnd_rate_unit',
                    'suppliers.supplier_name',
                    'service_po.purpose',
                )
                ->leftJoin('grn_supplier_details', 'grn_supplier_details.grnd_grn_id', '=', 'grn_supplier.grn_id')
                ->leftJoin('supplier_dc_details', 'supplier_dc_details.sup_dcd_id', '=', 'grn_supplier_details.table_pk_id')
                ->leftJoin('supplier_dc', 'supplier_dc.sup_dc_id', '=', 'supplier_dc_details.sup_dcd_dc_id')
                ->leftJoin('service_po_details', 'service_po_details.ser_pod_id', '=', 'supplier_dc_details.ser_pod_id')
                
                ->leftJoin('service_po', 'service_po.ser_po_id', '=', 'service_po_details.ser_pod_po_id')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
                ->whereIn('grn_supplier_details.table_unique_id',$grnTypeID)
                ->where('grn_supplier_id', '!=', $supplierID)
                ->where('grnd_item_id', $itemID)
                ->where('supplier_dc.sup_dc_type_id','=','Returnable - Service PO')
                ->orderBy('grn_id', 'desc')
                ->limit(5)
                ->get();

        } else {
            // Only all suppliers
                 $grn_data_2 = GRNSupplier::select(
                    'grn_id','grn_number','grn_date',
                    'grn_supplier_details.grnd_qty',
                    'grn_supplier_details.grnd_rate_unit',
                    'suppliers.supplier_name',
                    'service_po.purpose',
                )
                ->leftJoin('grn_supplier_details', 'grn_supplier_details.grnd_grn_id', '=', 'grn_supplier.grn_id')
                ->leftJoin('supplier_dc_details', 'supplier_dc_details.sup_dcd_id', '=', 'grn_supplier_details.table_pk_id')
                ->leftJoin('supplier_dc', 'supplier_dc.sup_dc_id', '=', 'supplier_dc_details.sup_dcd_dc_id')
                ->leftJoin('service_po_details', 'service_po_details.ser_pod_id', '=', 'supplier_dc_details.ser_pod_id')
                ->leftJoin('service_po', 'service_po.ser_po_id', '=', 'service_po_details.ser_pod_po_id')
                ->leftJoin('suppliers', 'suppliers.id', '=', 'grn_supplier.grn_supplier_id')
                ->whereIn('grn_supplier_details.table_unique_id',$grnTypeID)
                ->where('grnd_item_id', $itemID)
                ->where('supplier_dc.sup_dc_type_id','=','Returnable - Service PO')
                ->orderBy('grn_id', 'desc')
                ->limit(5)
                ->get();
        }

        // Merge safely 
        $grn_data = $grn_data_1->merge($grn_data_2)->values();

        // Format date
        foreach ($grn_data as $item) {
            $item->grn_date = $item->grn_date != "" ? Date::createFromFormat('Y-m-d', $item->grn_date)->format('d/m/Y') : "";
        }

        return response()->json([
            'response_code' => '1',
            'grn_data' => $grn_data->toArray()
        ]);
    }

    private function restoreMasterCalibration($detailRecord)
    {
        if ($detailRecord && !empty($detailRecord->sr_table_unique_id) && !empty($detailRecord->sr_table_pk_id)) {
            $tableMap = [
                'rt_camera' => ['table' => 'rt_camera', 'pk' => 'rt_camera_id', 'prefix' => 'rt_'],
                'mpt_equipment' => ['table' => 'equipment_mpt', 'pk' => 'em_id', 'prefix' => 'em_'],
                'mpt_material' => ['table' => 'material_mpt', 'pk' => 'mm_id', 'prefix' => 'mm_'],
                'ut_equipment' => ['table' => 'equipment_ut', 'pk' => 'eu_id', 'prefix' => 'eu_'],
                'dpt_chemical' => ['table' => 'dpt_chemical', 'pk' => 'dpt_id', 'prefix' => 'dpt_'],
                'instrument' => ['table' => 'instrument', 'pk' => 'ins_id', 'prefix' => 'ins_'],
                'probe_ut' => ['table' => 'probe_ut', 'pk' => 'pu_id', 'prefix' => 'pu_'],
            ];

            if (isset($tableMap[$detailRecord->sr_table_unique_id])) {
                $tableInfo = $tableMap[$detailRecord->sr_table_unique_id];
                $prefix = $tableInfo['prefix'];

                $masterRecord = DB::table($tableInfo['table'])
                    ->where($tableInfo['pk'], $detailRecord->sr_table_pk_id)
                    ->first();

                if ($masterRecord) {
                    $updateData = [];
                    if (property_exists($masterRecord, $prefix . 'last_cali_date')) {
                        $updateData[$prefix . 'last_cali_date'] = $detailRecord->previous_last_cali_date;
                    }
                    if (property_exists($masterRecord, $prefix . 'next_cali_due_date')) {
                        $updateData[$prefix . 'next_cali_due_date'] = $detailRecord->previous_next_cali_due_date;
                    }
                    if (property_exists($masterRecord, $prefix . 'cali_freq')) {
                        $updateData[$prefix . 'cali_freq'] = $detailRecord->previous_cali_freq;
                    }
                    if (property_exists($masterRecord, $prefix . 'cali_certificate')) {
                        $updateData[$prefix . 'cali_certificate'] = $detailRecord->previous_cali_certificate;
                    }
                    if (property_exists($masterRecord, $prefix . 'cali_certificate_blob')) {
                        $updateData[$prefix . 'cali_certificate_blob'] = $detailRecord->previous_cali_certificate_blob;
                    }

                    if (!empty($updateData)) {
                        DB::table($tableInfo['table'])
                            ->where($tableInfo['pk'], $detailRecord->sr_table_pk_id)
                            ->update($updateData);
                    }
                }
            }
        }
    }
}