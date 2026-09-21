<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemOpening;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class ItemOpeningController extends Controller
{
    public function manage()
    {
        return view('manage.manage-item_opening');
    }

    // public function store(Request $request)
    // {
    //     DB::beginTransaction();
    //     try
    //     {
    //         // dd($request->all());
    //         $year_data = getCurrentYearData();
    //         $location_data = getCurrentLocation();

    //         $request->item_opening_data = json_decode($request->item_opening_data, true);

    //         if (!empty($request->item_opening_data)) {

    //             foreach ($request->item_opening_data as $ctVal) {

    //                 if ($ctVal == null) continue;

    //                 $itemId     = $ctVal['item_id'] ?? null;
    //                 $openingQty = $ctVal['io_opening_qty'] ?? 0;
    //                 $openingAmount = $ctVal['io_opening_amount'] ?? 0;

    //                 if (!$itemId) continue;

    //                 $itemData = ItemOpening::where([
    //                     'io_item_id' => $itemId,
    //                     'current_location_id'=>$location_data->location_id,
    //                     // 'company_id' => Auth::user()->company_id,
    //                 ])->first();

    //                 // dd($itemData);

    //                 if ($itemData) {

    //                     $oldOpening = $itemData->io_opening_qty ?? 0;
    //                     $oldAmount = $itemData->io_opening_amount ?? 0;
    //                     $Qtydiff = $openingQty - $oldOpening;
    //                     $Amountdiff = $openingAmount - $oldAmount;

    //                     $itemData->io_opening_qty = $openingQty;
    //                     $itemData->io_opening_amount = $oldAmount;
    //                     // $itemData->io_stock_qty  += $Qtydiff;
    //                     // $itemData->io_stock_amount += $Amountdiff;
    //                     // $itemData->io_stock_rate_unit= $itemData->io_stock_amount / $itemData->io_stock_qty;
    //                     $itemData->io_opening_rate_unit = $openingQty > 0 ? $openingAmount / $openingQty : 0;
    //                     $itemData->save();

    //                 } else {
    //                     // dd($location_data);
    //                     $itemData = ItemOpening::create([
    //                         'io_item_id'      => $itemId,
    //                         'io_opening_qty' => $openingQty,
    //                         // 'io_stock_qty'   => $openingQty,
    //                         'io_opening_rate_unit'=>$openingQty > 0 ? $openingAmount / $openingQty : 0,
    //                         'io_opening_amount'=>$openingAmount,
    //                         // 'io_stock_rate_unit'=>$openingAmount / $openingQty,
    //                         // 'io_stock_amount'=> $openingAmount,
    //                         'current_location_id'=>$location_data->location_id,
    //                         'company_id'     => Auth::user()->company_id,
    //                         'created_by'     => Auth::user()->id,
    //                         'created_on'     => Carbon::now('Asia/Kolkata'),
    //                     ]);
    //                 }
    //             }
    //         }

    //         if($itemData->save())
    //         {
    //             DB::commit();
    //             stockEffect($location_data->location_id,$request->io_item_id,$request->io_item_id,$openingQty,0,'add','U','Item Opening',$itemData->io_id);
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
        DB::beginTransaction();
        try {
            $location_data = getCurrentLocation();
            $items = json_decode($request->item_opening_data, true);
            if (!empty($items)) {
                foreach ($items as $ctVal) {
                    if (empty($ctVal)) continue;

                    $itemId        = $ctVal['item_id'] ?? null;
                    $CurQty    = $ctVal['io_opening_qty'] ?? 0;
                    $CurAmount = $ctVal['io_opening_qty'] > 0 ? $ctVal['io_opening_amount'] : 0;
                    
                    if (!$itemId) continue;

                    $itemData = ItemOpening::where([
                        'io_item_id' => $itemId,
                        'current_location_id' => $location_data->location_id,
                    ])->first();
                  
                    $rate = ($CurQty > 0) ? ($CurAmount / $CurQty) : 0;

                    if (!$itemData) {
                        $itemData = ItemOpening::create([
                            'io_item_id'           => $itemId,
                            'io_opening_qty'       => $CurQty,
                            'io_opening_amount'    =>$CurQty > 0 ?$CurAmount : 0,
                            'io_opening_rate_unit' => $rate,
                            'current_location_id'  => $location_data->location_id,
                            'company_id'           => Auth::user()->company_id,
                            'created_by'           => Auth::user()->id,
                            'created_on'           =>Carbon::now('Asia/Kolkata'),
                        ]);
                       

                        stockEffect($location_data->location_id,$itemId,$itemId,$CurQty,0,$CurAmount,0,'Insert','U','Item Opening',$itemData->io_id);

                    } else {
                        $CurQty    = ($CurQty === '' || $CurQty === null) ? 0 : $CurQty;
                        $CurAmount = ($CurAmount === '' || $CurAmount === null) ? 0 : $CurAmount;
                        $rate          = ($rate === '' || $rate === null) ? 0 : $rate;
                        
                        $oldQty = $itemData->io_opening_qty ?? 0;
                        $oldAmount = $itemData->io_opening_amount ?? 0;
                        $oldstockQty = $itemData->io_stock_qty ?? 0;
                        $oldstockAmount = $itemData->io_stock_amount ?? 0;

                        // $qtyDiff = $CurQty - $oldQty;
                        $itemData->update([
                            'io_opening_qty'        => $CurQty,
                            'io_opening_amount'    => $CurAmount, 
                            'io_opening_rate_unit' => $rate,
                            'last_by'           => Auth::user()->id,
                            'last_on'           => Carbon::now('Asia/Kolkata'),
                        ]);

                        // if ($qtyDiff != 0) {
                            stockEffect($location_data->location_id,$itemId, $itemId,$CurQty,$oldQty,$CurAmount,$oldAmount,'Update','U','Item Opening',$itemData->io_id);
                        // }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'response_code' => '1',
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
                ]) || str_contains($message, 'Sr. No. Is Already')
            ) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            } else{
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                    'original_error' => $e->getMessage()
                ]);
            }
        }
    }


    public function getItemOpening(Request $request){

    $itemTypes = getItemType();
    $location_data = getCurrentLocation();

         $item_data = Item::select([
            'item_opening.io_id',
            'item_opening.io_item_id',
            'item.id as item_id',
            // DB::raw("LPAD(item.item_code, 4, '0') as item_code"),
            'item.item_name',
            'item_group.item_group',
            'item.item_type',
            'unit.unit',
            'item_opening.io_opening_qty',
            'item_opening.io_opening_amount',
            'item_opening.io_stock_qty',
            'item_opening.io_stock_rate_unit',
            'item_opening.io_opening_rate_unit',
            'item_opening.current_location_id',
        ])
        // ->leftJoin('item_opening','item_opening.io_item_id','=','item.id')
        ->leftJoin('item_opening', function ($join) use ($request , $location_data) {

            $join->on('item_opening.io_item_id', '=', 'item.id')
                ->where('item_opening.current_location_id', '=', $location_data->location_id);
        })
        ->leftJoin('item_group','item_group.id','=','item.item_group_id')
        ->leftJoin('unit','unit.id','=','item.unit_id')
        
        // ->where('item.item_type','=','general')
        ->get();

        // $used_qty = DB::table('rt_camera_pending_qty')
        // ->select('item_id', 'pending_qty')
        // ->where('table_unique_id', '=', 'Opening')
        // ->get()
        // ->pluck('pending_qty', 'item_id') // Creates an associative array ['item_id' => 'pending_qty']
        // ->toArray(); // Convert it to a plain array for easier usage

        $tables = [
            'mpt_material_pending_qty',
            'mpt_equipment_pending_qty',
            'rt_camera_pending_qty',
            'dpt_chemical_pending_qty',	
            'instrument_pending_qty',
            'ut_equipment_pending_qty',
            'probe_ut_pending_qty',
        ];
        
        $collection = collect();
        
        foreach ($tables as $table) {
            $collection = $collection->merge(
                DB::table($table)
                    ->select('item_id', 'pending_qty')
                    ->where('table_unique_id', 'Opening')
                    ->where('location_id', $location_data->location_id)
                    ->get()
            );
        }
        
        $used_qty = $collection->pluck('pending_qty', 'item_id')->toArray();

        if($item_data)
        {
            $item_data->transform(function ($item) use ($itemTypes,$used_qty) {
            $item->item_type = $itemTypes[$item->item_type] ?? $item->item_type;
            $pending_qty = $used_qty[$item->item_id] ?? 0; 
            if($item->item_type != 'General' && $item->item_type != 'Industrial X-Ray Films'){    
                $item->min = $item->io_opening_qty - $pending_qty;
            }
            return $item;
        });

            return response()->json([
                'item_data' => $item_data,
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
}