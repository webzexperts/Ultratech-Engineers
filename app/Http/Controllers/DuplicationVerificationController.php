<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\AcceptanceStandard;
use App\Models\Operator;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Material;
use App\Models\EvaluationAsPer;
use App\Models\Sensitivity;
use App\Models\ProcedureReference;
use App\Models\GstConfiguration;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Inquiry;
use App\Models\TypeOfJob;
use App\Models\Unit;
use App\Models\JobDescription;
use App\Models\ItemGroup;
use App\Models\Item;
use App\Models\Part;
use App\Models\Reason;
use App\Models\Location;
use App\Models\Quotation;
use App\Models\PurchaseOrder;
use App\Models\GRN;
use App\Models\Transaction\ItemIssue;
use App\Models\ItemReturnSlip;
use App\Models\MaterialInspection;
use App\Models\NABLConfiguration;
use App\Models\RTCamera;
use App\Models\EquipmentMPT;
use App\Models\MaterialMpt;
use App\Models\DPTChemical;
use App\Models\EquipmentUT;
use App\Models\Instrument;
use App\Models\Shift;
use App\Models\PurchaseIndent;
use App\Models\AssignFormatNo;
use App\Models\Transaction\DeliveryChallanCustomer;
use App\Models\Transaction\NonRetMatChallan;
use App\Models\Transaction\OrderAcceptance;
use App\Models\Transaction\PlanningManagement;
use App\Models\Transaction\InterLocationTransfer;
use App\Models\Transaction\ServicePO;
use App\Models\Transaction\GRNLocation;
use App\Models\Transaction\GRNSupplier;
use App\Models\Transaction\ItemReturnCustomer;
use App\Models\Transaction\SupplierDC;
use App\Models\Transaction\MaterialInward;
use App\Models\Transaction\Offer;
use App\Models\Transaction\TechniqueSheetRt;
use App\Models\Transaction\TestReportRt;
use App\Models\IqiDesignation;
use App\Models\Film;
use App\Models\IqiSensitivity;
use App\Models\AreaOfCoverage;
use App\Models\FilmBrand;
use App\Models\FilmType;
use App\Models\FindingLevel;
use App\Models\Finding;
use App\Models\AuthorityPerson;
use App\Models\Enclosure;
use App\Models\AerbDocument;
use App\Models\FilmResult;
use App\Models\ProbeUT;
use App\Models\Transaction\MeasurementSheet;
use App\Models\Transaction\ObservationSheet;
use App\Models\Transaction\ProductionEntry;
use App\Models\Transaction\TestReportUt;
use App\Models\Transaction\TestReportDpt;
use App\Models\Transaction\TestReportMpt;
use Date;

class DuplicationVerificationController extends Controller
{
    // verify user name
    public function verifyUserName(Request $request)
    {
        if(!empty($request->user_name))
        {
            $name = $request->user_name;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = Admin::where('user_name',$name)->where('id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = Admin::where('user_name',$name)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'user_name' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate User Name Found.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // acceptance standard verification controller
    public function verifyAcceptanceStandard(Request $request)
    {
        if(!empty($request->acceptance_standard))
        {
            $name = $request->acceptance_standard;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = AcceptanceStandard::where('acceptance_standard',$name)->where('id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = AcceptanceStandard::where('acceptance_standard',$name)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'acceptance_standard' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Acceptance Standard Found.',
                    // 'response_message' => 'The Acceptance Standard Name Has Already Been Taken',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // operator verification controller
    public function verifyOperator(Request $request)
    {
        if(!empty($request->operator))
        { 
            $name = $request->operator;
            $category = $request->category;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = Operator::where('operator',$name)->where('category',$category)->where('id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = Operator::where('operator',$name)->where('category',$category)->first();
                }
            }
            
            if($users_count)
            {
                return response()->json([
                    'operator' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Record already exists.',
                    // 'response_message' => 'The Operator Name Has Already Been Taken',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // country verification controller 
    public function verifyCountry(Request $request)
    {
        if(!empty($request->country_name))
        { 
            $name = $request->country_name;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = Country::where('country_name',$name)->where('id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = Country::where('country_name',$name)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'country_name' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Country Found.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // state verification controller
    public function verifyState(Request $request)
    {
        if(!empty($request->state))
        {
            $name = $request->state;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    // $users_count = State::where('state',$request->state)->where('country_id', $request->country)->where('id','!=',$request->id)->first();
                    $users_count = State::where('state',$request->state)->where('id','!=',$request->id)->first();
                }
                else
                {
                    // $users_count = State::where('state',$request->state)->where('country_id', $request->country)->first();
                    $users_count = State::where('state',$request->state)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'state' => $users_count,
                    'response_code' => '1',
                    // 'response_message' => 'Duplicate State, Country Found.',
                    'response_message' => 'Duplicate State Found.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    public function verifyAssignFormatNo(Request $request)
    {
        if (!empty($request->assign_effect_date) && !empty($request->page_id) && !empty($request->location_id)) {

            $effectiveDate = Date::createFromFormat('d/m/Y', $request->assign_effect_date)->format('Y-m-d');

            $query = AssignFormatNo::where('page_id', $request->page_id)
                ->where('assign_location_id', $request->location_id);

            // Edit case
            if (!empty($request->id)) {
                $query->where('assign_id', '!=', $request->id);
            }

            // Same location + page ma thi last effective date
            $lastRecord = $query->orderBy('assign_effect_date', 'desc')->first();

            if ($lastRecord) {
                if ($effectiveDate <= $lastRecord->assign_effect_date) {
                    return response()->json([
                        'response_code' => '1',
                        'response_message' => 'Eff. Date must be greater than last Eff. Date (' . date('d/m/Y', strtotime($lastRecord->assign_effect_date)) . ').'
                    ]);
                }
            }

            return response()->json([
                'response_code' => '2',
                'response_message' => 'Success',
            ]);
        }

        return response()->json([
            'response_code' => '0',
            'response_message' => 'Record Does Not Exists'
        ]);
    }

    // state and code verification controller
    public function verifyStateCode(Request $request)
    {
        if(!empty($request->state_code))
        {
            $name = $request->state_code;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = State::where('state_code',$request->state_code)->where('country_id', $request->countryId)->where('id','!=',$request->id)->first();
                }
                else
                {
                    $users_count =  State::where('state_code',$request->state_code)->where('country_id', $request->countryId)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Country , GST Code Found.',
                    // 'response_message' => 'Record already exists.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // city verification controller
    public function verifyCityData(Request $request)
    {
        if(!empty($request->city))
        {
            $name =  $request->city;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    // $users_count = City::where('city',$name)->where('state_id',$request->state)->where('id','!=',$request->id)->first();
                    $users_count = City::where('city',$name)->where('id','!=',$request->id)->first();
                }
                else
                {
                    // $users_count = City::where('city',$name)->where('state_id',$request->state)->first();
                    $users_count = City::where('city',$name)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'response_code' => '1',
                    'response_message' => 'Duplicate City Found.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists.',
            ]);
        }
    }

    // material verification controller
    public function verifyMaterial(Request $request)
    {
        if(!empty($request->material))
        {
            $name = $request->material;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = Material::where('material',$name)->where('id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = Material::where('material',$name)->first();
                }
            }
            
            if($users_count)
            {
                return response()->json([
                    'material' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Material Found.',
                    // 'response_message' => 'The Material Has Already Been Taken',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // evaluation_as_per verification controller
    public function verifyEvaluation(Request $request)
    {
        if(!empty($request->evaluation_as_per))
        {
            $name = $request->evaluation_as_per;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = EvaluationAsPer::where('evaluation_as_per',$name)->where('evaluation_as_per_id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = EvaluationAsPer::where('evaluation_as_per',$name)->first();
                }
            }
            
            if($users_count)
            {
                return response()->json([
                    'evaluation_as_per' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Evaluation As Per Found.',
                    // 'response_message' => 'The Evaliation As Per Has Already Been Taken',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // sensitivity verification controller
    public function verifySensitivity(Request $request)
    {
        if(!empty($request->sensitivity))
        {
            $name = $request->sensitivity;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = Sensitivity::where('sensitivity',$name)->where('id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = Sensitivity::where('sensitivity',$name)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'sensitivity' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Sensitivity Found.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // procedure reference verification controller
    public function verifyProcedureReference(Request $request)
    {
        if(!empty($request->procedure_reference))
        {
            $name = $request->procedure_reference;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = ProcedureReference::where('procedure_reference',$name)->where('procedure_reference_id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = ProcedureReference::where('procedure_reference',$name)->first();
                }
            }
            
            if($users_count)
            {
                return response()->json([
                    'film_brand' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Procedure Reference Found.',
                    // 'response_message' => 'The Procedure Reference Has Already Been Taken',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // gst configuration sac verification controller
    public function verifySAC(Request $request)
    {
        if(!empty($request->sac))
        { 
            $name = $request->sac;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = GstConfiguration::where('gc_sac',$name)->where('gc_id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = GstConfiguration::where('gc_sac',$name)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'sac' => $users_count,                
                    'response_code' => '1',
                    'response_message' => 'Record already exists.',
                    // 'response_message' => 'The SAC Has Already Been Taken',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // customer verification controller
    public function verifyCustomer(Request $request)
    {
        if(!empty($request->customer))
        {
            $name = $request->customer;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = Customer::where('customer',$name)->where('id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = Customer::where('customer',$name)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'customer' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Customer Found.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // customer code verification controller
    public function verifyCustomerCode(Request $request)
    {
        if(!empty($request->customer_code))
        {
            $name = $request->customer_code;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = Customer::where('customer_code',$name)->where('id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = Customer::where('customer_code',$name)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'customer' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Record already exists.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // supplier verification controller
    public function verifySupplier(Request $request)
    {
        if(!empty($request->supplier_name))
        {
            $name = $request->supplier_name;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = Supplier::where('supplier_name',$name)->where('id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = Supplier::where('supplier_name',$name)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'supplier_name' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Supplier Found.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // type of job verification controller
    public function verifyTypeofJob(Request $request)
    {
        if(!empty($request->type_of_job))
        {
            $name = $request->type_of_job;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = TypeOfJob::where('type_of_job',$name)->where('id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = TypeOfJob::where('type_of_job',$name)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'type_of_job' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Type of Job Found.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // unit verification controller
    public function verifyUnit(Request $request)
    {
        if(!empty($request->unit))
        {
            $name = $request->unit;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = Unit::where('unit',$name)->where('id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = Unit::where('unit',$name)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'unit' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Unit Found.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // shift verification controller
    public function verifyShift(Request $request)
    {
        if(!empty($request->shift_name))
        {
            $name = $request->shift_name;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = Shift::where('shift_name',$name)->where('shift_id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = Shift::where('shift_name',$name)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'shift' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Shift Found.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // job description verification controller
    public function verifyJobDescription(Request $request)
    {
        if(!empty($request->job_description))
        {
            $name = $request->job_description;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = JobDescription::where('job_description',$name)->where('id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = jobDescription::where('job_description',$name)->first();
                }
            }
 
            if($users_count)
            {
                return response()->json([
                    'job_description' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Job Description Found.',
                    // 'response_message' => 'The Country Name Has Already Been Taken',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // Item Group verification controller
    public function verifyItemGroup(Request $request)
    {
        if(!empty($request->item_group))
        {
            $name = $request->item_group;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = ItemGroup::where('item_group',$name)->where('id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = ItemGroup::where('item_group',$name)->first();
                }
            }
 
            if($users_count)
            {
                return response()->json([
                    'item_group' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Item Group Found.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // item verification controller
    public function verifyItem(Request $request)
    {
        if(!empty($request->item_name))
        {
            $name = $request->item_name;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = Item::where('item_name',$name)->where('id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = Item::where('item_name',$name)->first();
                }
            }
 
            if($users_count)
            {
                return response()->json([
                    'item_name' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Item Found.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // item code verification controller
    public function verifyItemCode(Request $request)
    {
        if(!empty($request->item_code))
        {
            $name = $request->item_code;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = Item::where('item_code',$name)->where('id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = Item::where('item_code',$name)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'item_code' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Record already exists.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    public function verifyPart(Request $request)
    {
        // dd($request->all());
        if(!empty($request->part_no))
        {
            $part_no = $request->part_no;
            $drg_no = $request->drg_no ?? '';
            $job_desc_id = $request->job_desc_id;
           
            $query = Part::where('part_no', $part_no)
                         ->where('job_desc_id', $job_desc_id)
                         ->where(function($q) use ($drg_no) {
                             if ($drg_no === '') {
                                 $q->whereNull('drg_no')->orWhere('drg_no', '');
                             } else {
                                 $q->where('drg_no', $drg_no);
                             }
                         });
                         
            if(isset($request->part_id) && $request->part_id != "")
            {
                $query->where('part_id', '!=', $request->part_id);
            }
           
            $users_count = $query->first();
 
            if($users_count)
            {
                return response()->json([
                    'part' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Job Description , Part No. & Drg. No. Found.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    public function verifyReason(Request $request)
    {
        if(!empty($request->reason_name))
        {
            $name = $request->reason_name;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = Reason::where('reason_name',$name)->where('id','!=',$request->id)->where('reason_type','=',$request->reason_type)->first();
                }
                else
                {
                    $users_count = Reason::where('reason_name',$name)->where('reason_type','=',$request->reason_type)->first();
                }
            }
 
            if($users_count)
            {
                return response()->json([
                    'reason' => $users_count,
                    'response_code' => '1',
                    // 'response_message' => 'Record already exists.',
                    'response_message' => 'Duplicate Reason Name Found.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    public function verifyLocationName(Request $request)
    {
        if(!empty($request->location_name))
        {
            $name = $request->location_name;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    // $users_count = Location::where('location_name',$name)->where('location_id','!=',$request->id)->where('location_code','=',$request->location_code)->first();
                    $users_count = Location::where('location_name',$name)->where('location_id','!=',$request->id)->first();
                }
                else
                {
                    // $users_count = Location::where('location_name',$name)->where('location_code','=',$request->location_code)->first();
                    $users_count = Location::where('location_name',$name)->first();
                }
            }
 
            if($users_count)
            {
                return response()->json([
                    // 'reason' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Location Found.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

        public function verifyNABLLocation(Request $request){
    
        if (!empty($request->nabl_location)) { 
            $nabl_location = $request->nabl_location;
            if(!empty($nabl_location)) {
                
                if (NABLConfiguration::where('nabl_location', $nabl_location)
                    ->when($request->id, fn($q) => $q->where('nabl_id', '!=', $request->id))
                    ->exists()) {

                    return response()->json([
                        'response_code' => '1',
                        'response_message' => 'Duplicate NABL Location Found.',
                    ]);
                }
                if(isset($request->id)){
                    $users_count = NABLConfiguration::where('nabl_id','!=',$request->id)->where('tc_no','=',$request->tc_no)->first();
                }else{
                    $users_count = NABLConfiguration::where('tc_no','=',$request->tc_no)->first();

                }
            }
            
            if($users_count){
                return response()->json([
                    'serial_no' => $users_count,                
                    'response_code' => '1',
                    'response_message' => 'Duplicate TC No. Found.',
                ]);
            }
            else{
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
            // if (!empty($request->id)) {
            //     $excludeId = $request->id;
            // } else {
            //     $excludeId = null;
            // }

            // // Check nabl_location

            // // Check tc_no
            // if (NABLConfiguration::where('tc_no', $request->tc_no)
            //     ->when($excludeId, fn($q) => $q->where('nabl_id', '!=', $excludeId))
            //     ->exists()) {

            //     return response()->json([
            //         'response_code' => '1',
            //         'response_message' => 'TC No already exists',
            //     ]);
            // }

            // // Check location_no
            // if (NABLConfiguration::where('location_no', $request->location_no)
            //     ->when($excludeId, fn($q) => $q->where('nabl_id', '!=', $excludeId))
            //     ->exists()) {

            //     return response()->json([
            //         'response_code' => '1',
            //         'response_message' => 'Location No already exists',
            //     ]);
            // }

            // If all passed
            // return response()->json([
            //     'response_code' => '2',
            //     'response_message' => 'Success',
            // ]);
        } else {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // RtCamera verification controller
    public function verifyRTCamera(Request $request)
    {

        if(!empty($request->rt_camera_name))
        {
            $name = $request->rt_camera_name;
            if(!empty($name))
            {
                if(isset($request->camera_id))
                {
                    $users_count = RTCamera::where('rt_camera_name',$request->rt_camera_name)->where('rt_serial_no', $request->rt_serial_no)->where('rt_camera_id','!=',$request->camera_id)->first();
                }
                else
                {
                    $users_count = RTCamera::where('rt_camera_name',$request->rt_camera_name)->where('rt_serial_no', $request->rt_serial_no)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Camera Name, Camera Sr. No. Found.',
                    
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // ProbeUT verification controller
    public function verifyProbeUT(Request $request)
    {
        if(!empty($request->pu_probe))
        {
            $name = $request->pu_probe;
            if(!empty($name))
            {
                if(isset($request->pu_id))
                {
                    $users_count = ProbeUT::where('pu_probe',$request->pu_probe)->where('pu_serial_number', $request->pu_serial_number)->where('pu_id','!=',$request->pu_id)->first();
                }
                else
                {
                    $users_count = ProbeUT::where('pu_probe',$request->pu_probe)->where('pu_serial_number', $request->pu_serial_number)->first();
                    // dd($users_count);
                }
            }
            // dd($users_count);

            if($users_count)
            {
                return response()->json([
                    'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Probe, Probe Sr. No. Found.',
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // EquipmentMPT verification controller
    public function verifyEquipmentMPT(Request $request)
    {

        if(!empty($request->em_make))
        {
            $name = $request->em_make;
            if(!empty($name))
            {
                if(isset($request->em_id))
                {
                    $users_count = EquipmentMPT::where('em_equipment_name',$request->em_equipment_name)->where('em_make',$request->em_make)->where('em_serial_no', $request->em_serial_no)->where('em_id','!=',$request->em_id)->first();
                }
                else
                {
                    $users_count = EquipmentMPT::where('em_equipment_name',$request->em_equipment_name)->where('em_make',$request->em_make)->where('em_serial_no', $request->em_serial_no)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Equipment, Make, Mfg. Sr. No. Found.',
                    
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // EquipmentUT verification controller
    public function verifyEquipmentUT(Request $request)
    {

        if(!empty($request->eu_make))
        {
            $name = $request->eu_make;
            if(!empty($name))
            {
                if(isset($request->eu_id))
                {
                    $users_count = EquipmentUT::where('eu_equipment_name',$request->eu_equipment_name)->where('eu_make',$request->eu_make)->where('eu_serial_no', $request->eu_serial_no)->where('eu_id','!=',$request->eu_id)->first();
                }
                else
                {
                    $users_count = EquipmentUT::where('eu_equipment_name',$request->eu_equipment_name)->where('eu_make',$request->eu_make)->where('eu_serial_no', $request->eu_serial_no)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Equipment, Make, Mfg. Sr. No. Found.',
                    
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

     // MaterialMpt verification controller
    public function verifyMaterialMpt(Request $request)
    {
        
        if(!empty($request->mm_material))
        {
            $name = $request->mm_material;
            if(!empty($name))
            {
                $query = MaterialMpt::where('mm_material', $request->mm_material)
                    ->where('mm_material_make', $request->mm_material_make)
                    ->where('mm_batch_no', $request->mm_batch_no);

                if (isset($request->table_unique_id)) {
                    $query->where('table_unique_id', $request->table_unique_id)->where('table_pk_id', $request->table_pk_id);
                }

                if (isset($request->mm_id)) {
                    $query->where('mm_id', '!=', $request->mm_id);
                }

                $users_count = $query->first();
            }

            if($users_count)
            {
                return response()->json([
                    'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Material Name, Make, Batch No. Found.',
                    
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    public function verifyDPTChemical(Request $request)
    {

        if(!empty($request->dpt_chemical))
        {
            $name = $request->dpt_chemical;
            if(!empty($name))
            {
                $query = DPTChemical::where('dpt_chemical', $request->dpt_chemical)
                    ->where('dpt_batch_no', $request->dpt_batch_no);

                if (isset($request->table_unique_id)) {
                    $query->where('table_unique_id', $request->table_unique_id)->where('table_pk_id', $request->table_pk_id);
                }

                if (isset($request->dpt_id)) {
                    $query->where('dpt_id', '!=', $request->dpt_id);
                }
                $users_count = $query->first();
            }

            if($users_count)
            {
                return response()->json([
                    'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Chemical, Batch No. Found.',
                    
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

     // Instrument verification controller
    public function verifyInstrument(Request $request)
    {
        
        if(!empty($request->ins_instrument_no))
        {
            $name = $request->ins_instrument_no;
            if(!empty($name))
            {
                if(isset($request->ins_id))
                {
                    $users_count = Instrument::where('ins_instrument_no',$request->ins_instrument_no)->where('ins_id','!=',$request->ins_id)->first();
                }
                else
                {
                    $users_count = Instrument::where('ins_instrument_no',$request->ins_instrument_no)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Instrument No. Found.',
                    
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

     // Iqi Designation verification controller
    public function verifyIqiDesignation(Request $request)
    {
        
        if(!empty($request->iqi_designation))
        {
            $name = $request->iqi_designation;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = IqiDesignation::where('iqi_designation',$request->iqi_designation)->where('iqi_designation_id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = IqiDesignation::where('iqi_designation',$request->iqi_designation)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate IQI Designation Found.',
                    
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // Film verification controller
    public function verifyFilm(Request $request)
    {
        if(!empty($request->film_size_inch))
        {
            if(isset($request->id))
            {
                $users_count = Film::where('film_size_inch',$request->film_size_inch)->where('film_id','!=',$request->id)->first();
            }
            else
            {
                $users_count = Film::where('film_size_inch',$request->film_size_inch)->first();
            }

            if($users_count)
            {
                return response()->json([
                    'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Film Size (inch) Found.',
                ]);
            }
        }

        if(!empty($request->film_size_cm))
        {
            if(isset($request->id))
            {
                $users_count = Film::where('film_size_cm',$request->film_size_cm)->where('film_id','!=',$request->id)->first();
            }
            else
            {
                $users_count = Film::where('film_size_cm',$request->film_size_cm)->first();
            }

            if($users_count)
            {
                return response()->json([
                    'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Film Size (cm) Found.',
                ]);
            }
        }

        if(!empty($request->film_size_inch) || !empty($request->film_size_cm))
        {
            return response()->json([
                'response_code' => '2',
                'response_message' => 'Success',
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

    // Iqi Designation verification controller
    public function verifyIqiSensitivity(Request $request)
    {
        
        if(!empty($request->iqi_sensitivity))
        {
            $name = $request->iqi_sensitivity;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = IqiSensitivity::where('iqi_sensitivity',$request->iqi_sensitivity)->where('iqi_sensitivity_id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = IqiSensitivity::where('iqi_sensitivity',$request->iqi_sensitivity)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate IQI Sensitivity Found.',
                    
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }
    // Area Of Coverage verification controller
    public function verifyAreaOfCoverage(Request $request)
    {
        
        if(!empty($request->area_of_coverage))
        {
            $name = $request->area_of_coverage;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = AreaOfCoverage::where('area_of_coverage',$request->area_of_coverage)->where('area_of_coverage_id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = AreaOfCoverage::where('area_of_coverage',$request->area_of_coverage)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Area of Coverage Found.',
                    
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }
    // Film Brand verification controller
    public function verifyFilmBrand(Request $request)
    {
        
        if(!empty($request->film_brand))
        {
            $name = $request->film_brand;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = FilmBrand::where('film_brand',$request->film_brand)->where('film_brand_id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = FilmBrand::where('film_brand',$request->film_brand)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Film Brand Found.',
                    
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }
    // Film Type verification controller
    public function verifyFilmType(Request $request)
    {
        
        if(!empty($request->film_type))
        {
            $name = $request->film_type;
            if(!empty($name))
            {
                if(isset($request->id))
                {
                    $users_count = FilmType::where('film_type',$request->film_type)->where('film_type_id','!=',$request->id)->first();
                }
                else
                {
                    $users_count = FilmType::where('film_type',$request->film_type)->first();
                }
            }

            if($users_count)
            {
                return response()->json([
                    'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Film Type Found.',
                    
                ]);
            }
            else
            {
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }
        else
        {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    // Finding Level verification controller
    public function verifyFindingLevel(Request $request)
    {
        
        if(!empty($request->finding_level_name)){
            $name = $request->finding_level_name;
            if(!empty($name)){
                if(isset($request->id)){
                    $users_count = FindingLevel::where('finding_level_name',$request->finding_level_name)->where('finding_level_id','!=',$request->id)->first();
                }else{
                    $users_count = FindingLevel::where('finding_level_name',$request->finding_level_name)->first();
                }
            }

            if($users_count){
                return response()->json([
                    'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Finding Level Found.',
                    
                ]);
            }else{
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }else{
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }
    // Finding verification controller
    public function verifyFinding(Request $request)
    {
        
        if(!empty($request->finding_name)){
            $name = $request->finding_name;
            if(!empty($name)){
                if(isset($request->id)){
                    $users_count = Finding::where('finding_name',$request->finding_name)->where('finding_id','!=',$request->id)->first();
                }else{
                    $users_count = Finding::where('finding_name',$request->finding_name)->first();
                }
            }

            if($users_count){
                return response()->json([
                    'finding' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Finding Found.',
                    
                ]);
            }else{
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }else{
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }
    // Authority Person verification controller
    public function verifyAuthorityPerson(Request $request)
    {
        
        if(!empty($request->operator)){
            $operator = $request->operator;
            if(!empty($operator)){
                if(isset($request->id)){
                    $users_count = AuthorityPerson::where('operator',$request->operator)->where('authority_person_id','!=',$request->id)->first();
                }else{
                    $users_count = AuthorityPerson::where('operator',$request->operator)->first();
                }
            }

            if($users_count){
                return response()->json([
                    // 'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Authority Person Found.',
                ]);
            }else{
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }else{
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }
    // Authority Person verification controller
    public function verifyEnclosure(Request $request)
    {
        if(!empty($request->enclosure_name))
        {
            if(isset($request->id))
            {
                $users_count = Enclosure::where('enclosure_name',$request->enclosure_name)->where('enclosure_id','!=',$request->id)->first();
            }
            else
            {
                $users_count = Enclosure::where('enclosure_name',$request->enclosure_name)->first();
            }

            if($users_count)
            {
                return response()->json([
                    // 'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Enclosure Name Found.',
                ]);
            }
        }

        if(!empty($request->enclosure_no))
        {
            if(isset($request->id))
            {
                $users_count = Enclosure::where('enclosure_no',$request->enclosure_no)->where('enclosure_id','!=',$request->id)->first();
            }
            else
            {
                $users_count = Enclosure::where('enclosure_no',$request->enclosure_no)->first();
            }

            if($users_count)
            {
                return response()->json([
                    // 'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Enclosure No. Found.',
                ]);
            }
        }

        if(!empty($request->enclosure_name) || !empty($request->enclosure_no))
        {
            return response()->json([
                'response_code' => '2',
                'response_message' => 'Success',
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
    // Aerb Document verification controller
    public function verifyAerbDocuments(Request $request)
    {
        
        if(!empty($request->aerb_documents_name)){
            $aerb_documents_name = $request->aerb_documents_name;
            if(!empty($aerb_documents_name)){
                if(isset($request->id)){
                    $users_count = AerbDocument::where('aerb_documents_name',$aerb_documents_name)->where('aerb_documents_id','!=',$request->id)->first();
                }else{
                    $users_count = AerbDocument::where('aerb_documents_name',$aerb_documents_name)->first();
                }
            }

            if($users_count){
                return response()->json([
                    // 'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate AERB Document Name Found.',
                ]);
            }else{
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }else{
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }
    // Film Result verification controller
    public function verifyFilmResult(Request $request)
    {
        
        if(!empty($request->film_result_name)){
            $film_result_name = $request->film_result_name;
            if(!empty($film_result_name)){
                if(isset($request->id)){
                    $users_count = FilmResult::where('film_result_name',$film_result_name)->where('film_result_id','!=',$request->id)->first();
                }else{
                    $users_count = FilmResult::where('film_result_name',$film_result_name)->first();
                }
            }

            if($users_count){
                return response()->json([
                    // 'state' => $users_count,
                    'response_code' => '1',
                    'response_message' => 'Duplicate Film Result Found.',
                ]);
            }else{
                return response()->json([
                    'response_code' => '2',
                    'response_message' => 'Success',
                ]);
            }
        }else{
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exists',
            ]);
        }
    }

    


    // Latest Number Duplication Check
    public function checkInquirySequnceDuplication(Request $request)
    {
       $seq = "inq_sequence";
       $modal  =  Inquiry::class;
       $sequence = $request->inq_sequence;
       $message = "Inquiry No.";
       $id = $request->id;
       $table_id = 'inq_id';
       return  $this->checkMarketingTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id) ;
    }

    // Latest Quotation Number Duplication Check
    public function checkQuotationSequnceDuplication(Request $request)
    {    
       $seq = "quot_sequence";
       $modal  =  Quotation::class;
       $sequence = $request->quot_sequence;      
       $message = "Quotation No.";
       $id = $request->id;  
       $table_id = 'quot_id';
       return  $this->checkMarketingTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id) ;
    }

    public function checkTechniqueSheetNumberDuplication(Request $request)
    {        

       $seq = "technique_sheet_rt_sequence";
       $modal  =  TechniqueSheetRt::class;
       $sequence = $request->technique_sheet_rt_sequence;      
       $message = "Sr. No.";
       $id = $request->id;  
       $table_id = 'technique_sheet_rt_id';
       $pre_fix = 'RSS';
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id , $pre_fix) ;
    }

    public function checkTestReportRtNumberDuplication(Request $request)
    {        
       $seq = "test_report_sequence";
       $modal  =  TestReportRt::class;
       $sequence = $request->test_report_sequence;      
       $message = "Report No.";
       $id = $request->id;  
       $table_id = 'test_report_rt_id';
       $pre_fix = 'RT';
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id , $pre_fix) ;
    }
    public function checkTestReportUTNumberDuplication(Request $request)
    {        
       $seq = "test_report_sequence";
       $modal  =  TestReportUt::class;
       $sequence = $request->test_report_sequence;      
       $message = "Report No.";
       $id = $request->id;  
       $table_id = 'test_report_ut_id';
       $pre_fix = 'UT';
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id , $pre_fix);
    }

    public function checkTestReportDPTNumberDuplication(Request $request)
    {        
       $seq = "test_report_sequence";
       $modal  =  TestReportDpt::class;
       $sequence = $request->test_report_sequence;      
       $message = "Report No.";
       $id = $request->id;  
       $table_id = 'test_report_dpt_id';
       $pre_fix = 'DPT';
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id , $pre_fix);
    }

    public function checkTestReportMPTNumberDuplication(Request $request)
    {        
       $seq = "test_report_sequence";
       $modal  =  TestReportMpt::class;
       $sequence = $request->test_report_sequence;      
       $message = "Report No.";
       $id = $request->id;  
       $table_id = 'test_report_mpt_id';
       $pre_fix = 'MPT';
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id , $pre_fix);
    }

    public function checkMeasurementSheetNumberDuplication(Request $request)
    {        
       $seq = "measurement_sheet_sequence";
       $modal  =  MeasurementSheet::class;
       $sequence = $request->measurement_sheet_sequence;      
       $message = "Measurement Sheet No.";
       $id = $request->id;  
       $table_id = 'measurement_sheet_id';
       $pre_fix = 'MS';
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id , $pre_fix);
    }

    public function checkObservationSheetSequenceDuplication(Request $request)
    {        
       $seq = "observation_sheet_sequence";
       $modal  =  ObservationSheet::class;
       $sequence = $request->observation_sheet_sequence;      
       $message = "Sr. No.";
       $id = $request->id;  
       $table_id = 'observation_sheet_id';
       $pre_fix = 'OS';
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id,$pre_fix);
    }
   

    // Latest Purchase Order Number Duplication Check
    public function checkPurchaseOrderSequnceDuplication(Request $request)
    {    
       $seq = "po_sequence";
       $modal  =  PurchaseOrder::class;
       $sequence = $request->po_sequence;      
       $message = "Purchase Order No.";
       $id = $request->id;  
       $table_id = 'po_id';
       $pre_fix = 'PO';
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id, $pre_fix) ;
    }
    // Latest Purchase Indent Number Duplication Check
    public function checkPISequnceDuplication(Request $request)
    {    
       $seq = "pi_sequence";
       $modal  =  PurchaseIndent::class;
       $sequence = $request->pi_sequence;      
       $message = "Indent No.";
       $id = $request->id;  
       $table_id = 'pi_id';
       $pre_fix = 'PI';
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id, $pre_fix) ;
    }

    // Latest Item Return Slip Number Duplication Check
    public function checkIrsSequnceDuplication(Request $request)
    {    
       $seq = "irs_sequence";
       $modal  =  ItemReturnSlip::class;
       $sequence = $request->irs_sequence;      
       $message = "Item Return Slip No.";
       $id = $request->id;  
       $table_id = 'irs_id';
       $pre_fix = 'RET/';  
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id,$pre_fix) ;
    }

     // Latest Grn Number Duplication Check
    public function checkGrnSupplierSequnceDuplication(Request $request)
    {    
       $seq = "grn_sequence";
       $modal  =  GRNSupplier::class;
       $sequence = $request->grn_sequence;      
       $message = "GRN No.";
       $id = $request->id;  
       $table_id = 'grn_id';
       $pre_fix = 'GRN';  
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id,$pre_fix) ;
    }

     // Latest Inter Location Transfer Number Duplication Check
    public function checkInterLocationTransferSequnceDuplication(Request $request)
    {    
       $seq = "dc_sequence";
       $modal  =  InterLocationTransfer::class;
       $sequence = $request->dc_sequence;      
       $message = "Inter Location Transfer No.";
       $id = $request->id;  
       $table_id = 'ilt_id';
       $pre_fix = 'DC/LOC';  
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id,$pre_fix) ;
    }

    // Latest Service PO Number Duplication Check
    public function checkServicePOSequnceDuplication(Request $request)
    {    
       $seq = "ser_po_sequence";
       $modal  =  ServicePO::class;
       $sequence = $request->ser_po_sequence;      
       $message = "Service PO No.";
       $id = $request->id;  
       $table_id = 'ser_po_id';
       $pre_fix = 'S-PO';
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id, $pre_fix) ;
    }

    // Latest Issue Slip Number Duplication Check
    public function checkItemIssueSequnceDuplication(Request $request)
    {
       $seq = "issue_sequence";
       $modal  =  ItemIssue::class;
       $sequence = $request->issue_sequence;
       $message = "Issue No.";
       $id = $request->id;
       $table_id = 'issue_id';
       $pre_fix = 'ISSUE';
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id , $pre_fix) ;
    }

    // Latest GRN Location Number Duplication Check
    public function checkGRNLocationSequnceDuplication(Request $request)
    {
       $seq = "grn_loc_sequence";
       $modal  =  GRNLocation::class;
       $sequence = $request->grn_loc_sequence;
       $message = "GRN No.";
       $id = $request->id;
       $table_id = 'grn_loc_id';
       $pre_fix = 'GRN/LOC';
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id , $pre_fix) ;
    }
        
    // Latest SupplierDC Number Duplication Check
    public function checkSupplierDCSequnceDuplication(Request $request)
    {
       $seq = "sup_dc_sequence";
       $modal  =  SupplierDC::class;
       $sequence = $request->sup_dc_sequence;
       $message = "DC No.";
       $id = $request->id;
       $table_id = 'sup_dc_id';
       $pre_fix = 'DC/SUPP';
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id , $pre_fix) ;
    }

    // Latest Deliver Challan Customer Number Duplication Check
    public function checkDeliveryChallanCustomerSequnceDuplication(Request $request)
    {    
       $seq = "dc_sequence";
       $modal  =  DeliveryChallanCustomer::class;
       $sequence = $request->dc_sequence;      
       $message = "Delivery Challan No.";
       $id = $request->id;  
       $table_id = 'dc_id';
       $pre_fix = 'DC/CUST';
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id, $pre_fix) ;
    }

    // Latest Item Return Customer Number Duplication Check
    public function checkItemReturnCustomerSequnceDuplication(Request $request)
    {    
       $seq = "return_sequence";
       $modal  =  ItemReturnCustomer::class;
       $sequence = $request->return_sequence;      
       $message = "GRN No.";
       $id = $request->id;  
       $table_id = 'return_id';
       $pre_fix = 'GRN/CUST';
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id, $pre_fix) ;
    }


     // Latest Material Inward Number Duplication Check
    public function checkMaterialInwardSequnceDuplication(Request $request)
    {
       $seq = "material_inward_sequence";
       $modal  =  MaterialInward::class;
       $sequence = $request->material_inward_sequence;
       $message = "Inward No.";
       $id = $request->id;
       $table_id = 'material_inward_id';
       $pre_fix = 'INW';  
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id, $pre_fix) ;
    }

    // Latest Offer Number Duplication Check
    public function checkOfferSequnceDuplication(Request $request)
    {
       $seq = "offer_sequence";
       $modal  =  Offer::class;
       $sequence = $request->offer_sequence;
       $message = "Offer No.";
       $id = $request->id;
       $table_id = 'offer_id';
       $pre_fix = 'OFFER';  
       return  $this->checkMarketingTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id, $pre_fix) ;
    }
     // Latest Material Inspection Number Duplication Check
    public function checkMaterialInspectionSequnceDuplication(Request $request)
    {
       $seq = "mins_sequence";
       $modal  =  MaterialInspection::class;
       $sequence = $request->mins_sequence;
       $message = "Inspection No.";
       $id = $request->id;
       $table_id = 'mins_id';
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id) ;
    }

    // Latest Material Inspection Number Duplication Check
    public function checkOASequnceDuplication(Request $request)
    {    
       $seq = "oa_sequence";
       $modal  =  OrderAcceptance::class;
       $sequence = $request->oa_sequence;      
       $message = "Order Acceptance No.";
       $id = $request->id;  
       $table_id = 'oa_id';
       $pre_fix = 'OA/';  
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id,$pre_fix) ;
    }

    // Latest Material Inspection Number Duplication Check
    public function checkNRMCDCSequnceDuplication(Request $request)
    {    
       $seq = "nrmc_sequence";
       $modal  =  NonRetMatChallan::class;
       $sequence = $request->nrmc_sequence;      
       $message = "DC No.";
       $id = $request->id;  
       $table_id = 'nrmc_id';
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id) ;
    }

    public function checkCustomerDCNonReturnableSequenceDuplication(Request $request)
    {
       $seq = "customer_dc_non_returnable_sequence";
       $modal  = \App\Models\Transaction\CustomerDCNonReturnable::class;
       $sequence = $request->customer_dc_non_returnable_sequence;
       $message = "DC No.";
       $id = $request->id;
       $table_id = 'customer_dc_non_returnable_id';
       $pre_fix = 'NRDC';
       return $this->checkTranscationDuplication($seq, $modal, $sequence, $message, $id, $table_id, $pre_fix);
    }
     // Latest Planning Management Number Duplication Check
    public function checkPMSequnceDuplication(Request $request)
    {
       $seq = "pm_sequence";
       $modal  =  PlanningManagement::class;
       $sequence = $request->pm_sequence;
       $message = "Planning No.";
       $id = $request->id;
       $table_id = 'pm_id';
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id) ;
    }

    public function checkProductionNumberDuplication(Request $request)
    {
       $seq = "production_entry_sequence";
       $modal  =  ProductionEntry::class;
       $sequence = $request->production_entry_sequence;
       $message = "Sr. No.";
       $id = $request->id;
       $table_id = 'production_entry_id';
       $pre_fix = 'PROD';  
       return  $this->checkTranscationDuplication($seq, $modal, $sequence, $message , $id, $table_id, $pre_fix) ;
    }

    // Transcation Controller
    protected static function checkTranscationDuplication($seq, $modal, $sequence, $message, $id , $table_id , $pre_fix = null)
    {
        $year_data = getCurrentYearData();
        $number_format = duplicationSequence($modal, $seq, $sequence, $id, $table_id , $pre_fix);
        if($number_format != null)
        {
            if($number_format['format'] == 0)
            {
                return response()->json([
                  'response_code' => '0',
                //   'response_message' => $message . ' Is Already Exists'
                  'response_message' => 'Duplicate '.$message . ' Found.'
                ]);
            }
            else
            {
              return response()->json([
                  'response_code' => 1,
                  'latest_no'     => $number_format['format'],
                  'number'        => intval($number_format['isFound']),
              ]);
            }
        }
    }


    protected static function checkMarketingTranscationDuplication($seq, $modal, $sequence, $message, $id , $table_id, $pre_fix=null)
    {
        $year_data = getCurrentYearData();
        $number_format = duplicationMarketingSequence($modal, $seq, $sequence, $id, $table_id, $pre_fix);
        if($number_format != null)
        {
            if($number_format['format'] == 0)
            {
                return response()->json([
                  'response_code' => '0',
                //   'response_message' => $message . ' Is Already Exists'
                  'response_message' => 'Duplicate '.$message . ' Found.'
                ]);
            }
            else
            {
              return response()->json([
                  'response_code' => 1,
                  'latest_no'     => $number_format['format'],
                  'number'        => intval($number_format['isFound']),
              ]);
            }
        }
    }
}