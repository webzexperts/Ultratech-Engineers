<?php
namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Enclosure;
use App\Models\AuthorityPerson;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Date;
use App\Models\Admin;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Yajra\DataTables\DataTables;
use Illuminate\Validation\Rule;

class EnclosureRPDetailController extends Controller
{
    public function manage()
    {
        return view('manage.reports.manage-enclosure_rt_detail_report');
    }

    public function index(Request $request, DataTables $datatables)
    {
        $LocationData = getCurrentLocation();

        $enclosures_query = Enclosure::select([
            'enclosure.enclosure_id',
            'enclosure.enclosure_name',
            'enclosure.enclosure_no',
            'enclosure.enclosure_layout',
            'enclosure.enclosure_permission_to_use',
            'enclosure.enclosure_validity',
            'enclosure.current_location_id'
        ])
        ->where('enclosure.enclosure_type_value_fix', '=', 'Enclosure')
        ->orderBy('enclosure.enclosure_name', 'asc');

        if ($LocationData->location_type != 'HO') {
            $enclosures_query->where('enclosure.current_location_id', '=', $LocationData->location_id);
        }

        $enclosures = $enclosures_query->get()->groupBy('current_location_id');

        $authPersonsQuery = AuthorityPerson::whereIn('authority_person_type_value_fix', ['RSO', 'Radiographer'])
            ->where('status', '=', 'Active')
            ->orderBy('operator', 'asc');

        if ($LocationData->location_type != 'HO') {
            $authPersonsQuery->where('current_location_id', '=', $LocationData->location_id);
        }

        $authPersons = $authPersonsQuery->get()->groupBy(['current_location_id', 'authority_person_type_value_fix']);

        $location_query = DB::table('location');
        if ($LocationData->location_type != 'HO') {
            $location_query->where('location_id', '=', $LocationData->location_id);
        }
        $locations = $location_query->orderBy('location_name', 'asc')->pluck('location_name', 'location_id');

        $locationIds = $enclosures->keys()->concat($authPersons->keys())->unique();

        $rows = [];
        foreach ($locationIds as $locId) {
            $locName = isset($locations[$locId]) ? $locations[$locId] : '';
            $locEnclosures = $enclosures->get($locId, collect())->values();
            $rsos = isset($authPersons[$locId]['RSO']) ? $authPersons[$locId]['RSO']->values() : collect();
            $radiographers = isset($authPersons[$locId]['Radiographer']) ? $authPersons[$locId]['Radiographer']->values() : collect();

            $maxRows = max($locEnclosures->count(), $rsos->count(), $radiographers->count());

            for ($i = 0; $i < $maxRows; $i++) {
                $enclosure = $locEnclosures->get($i);
                $rso = $rsos->get($i);
                $rad = $radiographers->get($i);

                $rows[] = [
                    'id'                          => $enclosure ? $enclosure->enclosure_id : '',
                    'location_name'               => $locName,
                    'enclosure_name'              => $enclosure ? $enclosure->enclosure_name : '',
                    'enclosure_no'                => $enclosure ? $enclosure->enclosure_no : '',
                    'enclosure_layout'            => $enclosure ? $enclosure->enclosure_layout : '',
                    'enclosure_permission_to_use' => $enclosure ? $enclosure->enclosure_permission_to_use : '',
                    'enclosure_validity'          => $enclosure ? $enclosure->enclosure_validity : null,

                    'rso_name'                    => $rso ? $rso->operator : '',
                    'rso_validity'                => $rso ? $rso->validity : null,
                    'rso_pms_no'                  => $rso ? $rso->pms_no : '',
                    'rso_certificate'             => $rso ? $rso->certificate : '',

                    'rad_name'                    => $rad ? $rad->operator : '',
                    'rad_pms_no'                  => $rad ? $rad->pms_no : '',
                    'rad_certificate'             => $rad ? $rad->certificate : '',
                ];
            }
        }

        $dataTable = DataTables::of(collect($rows))
        ->editColumn('enclosure_layout', function($row){
            if(!empty($row['enclosure_layout'])) {
                $url = asset('storage/' . $row['enclosure_layout']);
                return '<a href="' . $url . '" target="_blank"><i class="ri-eye-fill action-icon remove_filters_short_qty"></i></a>';
            }
            return '';
        })
        ->editColumn('enclosure_permission_to_use', function($row){
            if(!empty($row['enclosure_permission_to_use'])) {
                $url = asset('storage/' . $row['enclosure_permission_to_use']);
                return '<a href="' . $url . '" target="_blank"><i class="ri-eye-fill action-icon remove_filters_short_qty"></i></a>';
            }
            return '';
        })
        ->editColumn('enclosure_validity', function($row){
            if ($row['enclosure_validity'] != null) {
                return Date::createFromFormat('Y-m-d', $row['enclosure_validity'])->format(DATE_FORMAT);
            }
            return '';
        })
        ->editColumn('rso_validity', function($row){
            if ($row['rso_validity'] != null) {
                return Date::createFromFormat('Y-m-d', $row['rso_validity'])->format(DATE_FORMAT);
            }
            return '';
        })
        ->editColumn('rso_certificate', function($row){
            if(!empty($row['rso_certificate'])) {
                $url = asset('storage/' . $row['rso_certificate']);
                return '<a href="' . $url . '" target="_blank"><i class="ri-eye-fill action-icon remove_filters_short_qty"></i></a>';
            }
            return '';
        })
        ->editColumn('rad_certificate', function($row){
            if(!empty($row['rad_certificate'])) {
                $url = asset('storage/' . $row['rad_certificate']);
                return '<a href="' . $url . '" target="_blank"><i class="ri-eye-fill action-icon remove_filters_short_qty"></i></a>';
            }
            return '';
        });

        return $dataTable
        ->rawColumns(['enclosure_layout', 'enclosure_permission_to_use', 'rso_certificate', 'rad_certificate'])
        ->make(true);
    }
}
?>