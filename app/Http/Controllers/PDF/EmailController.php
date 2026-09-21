<?php

namespace App\Http\Controllers\PDF;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\Transaction\ServicePO;

class EmailController extends Controller
{
    public function EmailPurchaseOrder(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $search_value = $request->search_value;

        $current_location_id = getCurrentLocation()->location_id;
        $po_query = PurchaseOrder::select([
            'purchase_order.po_id',
            'purchase_order.po_number',
            'suppliers.supplier_name',
            'suppliers.email_id as supplier_email',
        ])
        ->leftJoin('suppliers','suppliers.id','=','purchase_order.po_supplier_id')
        ->where("purchase_order.current_location_id", $current_location_id);

        if($from_date != "" && $to_date != "") {
            $from = \Illuminate\Support\Facades\Date::createFromFormat('d/m/Y', $from_date)->format('Y-m-d');
            $to = \Illuminate\Support\Facades\Date::createFromFormat('d/m/Y', $to_date)->format('Y-m-d');
            $po_query->whereDate('purchase_order.po_date','>=',$from);
            $po_query->whereDate('purchase_order.po_date','<=',$to);
        } else if($from_date != "") {
            $from = \Illuminate\Support\Facades\Date::createFromFormat('d/m/Y', $from_date)->format('Y-m-d');
            $po_query->where('purchase_order.po_date','>=',$from);
        } else if($to_date != "") {
            $to = \Illuminate\Support\Facades\Date::createFromFormat('d/m/Y', $to_date)->format('Y-m-d');
            $po_query->where('purchase_order.po_date','<=',$to);
        }

        if (!empty($search_value)) {
            $po_query->where(function($q) use ($search_value) {
                $q->where('purchase_order.po_number', 'like', "%{$search_value}%")
                  ->orWhere('suppliers.supplier_name', 'like', "%{$search_value}%");
            });
        }

        $po_data = $po_query->get();

        \Illuminate\Support\Facades\Log::info("EmailPurchaseOrder requested", [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'search_value' => $search_value,
            'current_location_id' => $current_location_id,
            'sql' => $po_query->toSql(),
            'bindings' => $po_query->getBindings(),
            'count_all' => count($po_data),
        ]);

        $report_data = [];
        foreach ($po_data as $row) {
            if (empty($row->supplier_email)) continue;
            
            $po_number = $row->po_number ? str_replace('/', '_', $row->po_number) : "";
            $supplier_for_wolh = $row->supplier_name ? preg_replace('/[^A-Za-z0-9_]/', '_', $row->supplier_name) : "";
            $pdf_name = "Purchase_Order_{$po_number}_{$supplier_for_wolh}";

            $report_data[] = (object)[
                'id' => base64_encode($row->po_id),
                'name' => $pdf_name,
                'type' => 'purchase_order',
                'email_id' => $row->supplier_email,
                'customer_name' => $row->supplier_name
            ];
        }

        return sendReportEmails($report_data, 'purchase_order', 'Purchase Order');
    }

    public function EmailServicePO(Request $request)
    {
        $from_date = $request->from_date;
        $to_date = $request->to_date;
        $search_value = $request->search_value;

        $current_location = getCurrentLocation();
        $year_data = getCurrentYearData();
        $po_query = ServicePO::select([
            'service_po.ser_po_id',
            'service_po.ser_po_number',
            'suppliers.supplier_name',
            'suppliers.email_id as supplier_email',
        ])
        ->leftJoin('suppliers','suppliers.id','=','service_po.supplier_id')
        ->where('service_po.year_id', $year_data->id)
        ->when($current_location->location_type != 'HO', function ($q) use ($current_location) {
            $q->where(function ($sub) use ($current_location) {
                $sub->where('service_po.current_location_id', $current_location->location_id)
                    ->orWhere('service_po.for_location_id', $current_location->location_id);
            });
        });

        if($from_date != "" && $to_date != "") {
            $from = \Illuminate\Support\Facades\Date::createFromFormat('d/m/Y', $from_date)->format('Y-m-d');
            $to = \Illuminate\Support\Facades\Date::createFromFormat('d/m/Y', $to_date)->format('Y-m-d');
            $po_query->whereDate('service_po.ser_po_date','>=',$from);
            $po_query->whereDate('service_po.ser_po_date','<=',$to);
        } else if($from_date != "") {
            $from = \Illuminate\Support\Facades\Date::createFromFormat('d/m/Y', $from_date)->format('Y-m-d');
            $po_query->where('service_po.ser_po_date','>=',$from);
        } else if($to_date != "") {
            $to = \Illuminate\Support\Facades\Date::createFromFormat('d/m/Y', $to_date)->format('Y-m-d');
            $po_query->where('service_po.ser_po_date','<=',$to);
        }

        if (!empty($search_value)) {
            $po_query->where(function($q) use ($search_value) {
                $q->where('service_po.ser_po_number', 'like', "%{$search_value}%")
                  ->orWhere('suppliers.supplier_name', 'like', "%{$search_value}%");
            });
        }

        $po_data = $po_query->get();

        \Illuminate\Support\Facades\Log::info("EmailServicePO requested", [
            'from_date' => $from_date,
            'to_date' => $to_date,
            'search_value' => $search_value,
            'current_location_id' => $current_location->location_id,
            'sql' => $po_query->toSql(),
            'bindings' => $po_query->getBindings(),
            'count_all' => count($po_data),
        ]);

        $report_data = [];
        foreach ($po_data as $row) {
            if (empty($row->supplier_email)) continue;
            
            $ser_po_number = $row->ser_po_number ? str_replace('/', '_', $row->ser_po_number) : "";
            $supplier_name = $row->supplier_name ? preg_replace('/[^A-Za-z0-9_]/', '_', $row->supplier_name) : "";
            $pdf_name = "Service_PO_{$ser_po_number}_{$supplier_name}";

            $report_data[] = (object)[
                'id' => base64_encode($row->ser_po_id),
                'name' => $pdf_name,
                'type' => 'service_po',
                'email_id' => $row->supplier_email,
                'customer_name' => $row->supplier_name
            ];
        }

        return sendReportEmails($report_data, 'service_po', 'Service PO');
    }

    public function EmailSinglePurchaseOrder(Request $request)
    {
        $po_id = $request->id;
        
        $po = PurchaseOrder::select([
            'purchase_order.po_id',
            'purchase_order.po_number',
            'purchase_order.po_supplier_id',
            'suppliers.supplier_name',
            'supplier_details.email_id as supplier_email',
        ])
        ->leftJoin('suppliers','suppliers.id','=','purchase_order.po_supplier_id')
        ->leftJoin('supplier_details','supplier_details.sup_id','=','suppliers.id')
        ->where('purchase_order.po_id', $po_id)
        ->first();

        if (!$po) {
            return response()->json(['status' => false, 'message' => 'Purchase Order Not Found.'], 404);
        }
        
        $emails = [];
        if (!empty($po->supplier_email)) {
            $emails[] = $po->supplier_email;
        }
        
        $supplier_details_emails = \App\Models\SupplierDetails::where('sup_id', $po->po_supplier_id)
            ->whereNotNull('email_id')
            ->where('email_id', '!=', '')
            ->pluck('email_id')
            ->toArray();
            
        $emails = array_merge($emails, $supplier_details_emails);
        
        $unique_emails = [];
        foreach ($emails as $email_str) {
            $parts = explode(',', $email_str);
            foreach ($parts as $part) {
                $trimmed = trim($part);
                if (!empty($trimmed) && filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
                    $unique_emails[] = $trimmed;
                }
            }
        }
        $unique_emails = array_unique($unique_emails);
        
        if (empty($unique_emails)) {
            return response()->json(['status' => false, 'message' => 'Email Not Found For This Supplier.'], 422);
        }
        
        $po_number = $po->po_number ? str_replace('/', '_', $po->po_number) : "";
        $supplier_for_wolh = $po->supplier_name ? preg_replace('/[^A-Za-z0-9_]/', '_', $po->supplier_name) : "";
        $pdf_name = "Purchase_Order_{$po_number}_{$supplier_for_wolh}";
        
        $report_data = [];
        $report_data[] = (object)[
            'id' => base64_encode($po->po_id),
            'name' => $pdf_name,
            'type' => 'purchase_order',
            'email_id' => implode(',', $unique_emails),
            'customer_name' => $po->supplier_name
        ];
        
        return sendReportEmails($report_data, 'purchase_order', 'Purchase Order');
    }

    public function EmailSingleServicePO(Request $request)
    {
        $ser_po_id = $request->id;
        
        $po = ServicePO::select([
            'service_po.ser_po_id',
            'service_po.ser_po_number',
            'service_po.supplier_id',
            'suppliers.supplier_name',
            'supplier_details.email_id as supplier_email',
        ])
        ->leftJoin('suppliers','suppliers.id','=','service_po.supplier_id')
        ->leftJoin('supplier_details','supplier_details.sup_id','=','suppliers.id')
        ->where('service_po.ser_po_id', $ser_po_id)
        ->first();
        
        if (!$po) {
            return response()->json(['status' => false, 'message' => 'Service PO Not Found.'], 404);
        }
        
        $emails = [];
        if (!empty($po->supplier_email)) {
            $emails[] = $po->supplier_email;
        }
        
        $supplier_details_emails = \App\Models\SupplierDetails::where('sup_id', $po->supplier_id)
            ->whereNotNull('email_id')
            ->where('email_id', '!=', '')
            ->pluck('email_id')
            ->toArray();
            
        $emails = array_merge($emails, $supplier_details_emails);
        
        $unique_emails = [];
        foreach ($emails as $email_str) {
            $parts = explode(',', $email_str);
            foreach ($parts as $part) {
                $trimmed = trim($part);
                if (!empty($trimmed) && filter_var($trimmed, FILTER_VALIDATE_EMAIL)) {
                    $unique_emails[] = $trimmed;
                }
            }
        }
        $unique_emails = array_unique($unique_emails);
        
        if (empty($unique_emails)) {
            return response()->json(['status' => false, 'message' => 'Email Not Found For This Supplier'], 422);
        }
        
        $ser_po_number = $po->ser_po_number ? str_replace('/', '_', $po->ser_po_number) : "";
        $supplier_name = $po->supplier_name ? preg_replace('/[^A-Za-z0-9_]/', '_', $po->supplier_name) : "";
        $pdf_name = "Service_PO_{$ser_po_number}_{$supplier_name}";
        
        $report_data = [];
        $report_data[] = (object)[
            'id' => base64_encode($po->ser_po_id),
            'name' => $pdf_name,
            'type' => 'service_po',
            'email_id' => implode(',', $unique_emails),
            'customer_name' => $po->supplier_name
        ];
        
        return sendReportEmails($report_data, 'service_po', 'Service PO');
    }
}
