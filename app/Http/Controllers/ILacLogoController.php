<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Admin;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use DataTables;
use Date;

class ILacLogoController extends Controller
{    
    public function index()
    {
        $companies = Company::all();
        return view('modals.ilaclogo_modal', compact('companies'));
    }

    public function updateLogo(Request $request, Company $company)
    {
        $company->update(['ilca_logo' => $request->has('ilca_logo') ? 'Y' : 'N']);
        return redirect()->route('manage-ilac_logo')->with('success', 'Logo setting updated!');
    }
}