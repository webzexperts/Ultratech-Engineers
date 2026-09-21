<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\ItemOpeningProdArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ItemOpeningProdAreaController extends Controller
{
    public function manage()
    {
        return view('manage.manage-item_opening_prod_area');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $location_data = getCurrentLocation();
            $items = json_decode($request->item_opening_data, true);
            if (!empty($items)) {
                foreach ($items as $ctVal) {
                    if (empty($ctVal)) continue;

                    $itemId = $ctVal['item_id'] ?? null;
                    // Force quantity as integer
                    $CurQty = isset($ctVal['io_opening_qty']) ? intval($ctVal['io_opening_qty']) : 0;
                    
                    if (!$itemId) continue;

                    $itemData = ItemOpeningProdArea::where([
                        'item_id' => $itemId,
                        'current_location_id' => $location_data->location_id,
                    ])->first();

                    if (!$itemData) {
                        $itemData = ItemOpeningProdArea::create([
                            'item_id'             => $itemId,
                            'opening_sq_in'       => $CurQty,
                            'stock_sq_in'         => 0,
                            'current_location_id' => $location_data->location_id,
                            'company_id'           => Auth::user()->company_id,
                            'created_by'           => Auth::user()->id,
                            'created_on'           => Carbon::now('Asia/Kolkata'),
                        ]);

                        stockEffectSQIN($location_data->location_id, $itemId, $itemId, $CurQty, 0, 'Insert', 'U', 'Item Opening Prod Area', $itemData->item_opening_prod_area_id);
                    } else {
                        $CurQty = ($CurQty === '' || $CurQty === null) ? 0 : $CurQty;
                        $oldQty = $itemData->opening_sq_in ?? 0;

                        $itemData->update([
                            'opening_sq_in' => $CurQty,
                            'last_by'       => Auth::user()->id,
                            'last_on'       => Carbon::now('Asia/Kolkata'),
                        ]);

                        stockEffectSQIN($location_data->location_id, $itemId, $itemId, $CurQty, $oldQty, 'Update', 'U', 'Item Opening Prod Area', $itemData->item_opening_prod_area_id);
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
                    'Insufficient Stock in Prod. Area',
                    'Invalid Location Code',
                    'Invalid Item'
                ]) || str_contains($message, 'Sr. No. Is Already')
            ) {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => $message,
                ]);
            } else {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => getResponseMessage('update_error'),
                    'original_error' => $message
                ]);
            }
        }
    }

    public function getItemOpening(Request $request)
    {
        $location_data = getCurrentLocation();

        $item_data = Item::select([
            'item_opening_prod_area.item_opening_prod_area_id',
            'item.id as item_id',
            'item.item_name',
            'item_group.item_group',
            'item.item_type',
            'item_opening_prod_area.opening_sq_in',
            'item_opening_prod_area.stock_sq_in',
            'item_opening_prod_area.current_location_id',
        ])
        ->leftJoin('item_opening_prod_area', function ($join) use ($location_data) {
            $join->on('item_opening_prod_area.item_id', '=', 'item.id')
                 ->where('item_opening_prod_area.current_location_id', '=', $location_data->location_id);
        })
        ->leftJoin('item_group', 'item_group.id', '=', 'item.item_group_id')
        ->where('item.item_type', '=', 'film')
        ->get();

        return response()->json([
            'response_code' => 1,
            'item_data' => $item_data
        ]);
    }
}
