<?php

namespace App\Http\Controllers;

use App\Models\SMTPConfiguration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;
use DataTables;

class SMTPConfigurationController extends Controller
{
    public function manage()
    {
        return view('manage.manage-smtp_configuration');
    }

    public function index(Request $request, DataTables $datatables)
    {
        $smtp_data = SMTPConfiguration::select([
            'smtp_configurations.sc_id',
            'smtp_configurations.email',
            'smtp_configurations.cc_email',
            'smtp_configurations.mail_host',
            'smtp_configurations.out_port_no',
            'smtp_configurations.reply_email',
            'smtp_configurations.purchase',
            'smtp_configurations.enable_ssl',
            'smtp_configurations.created_on',
            'smtp_configurations.created_by',
            'smtp_configurations.last_by',
            'smtp_configurations.last_on'
        ]);

        $dataTable = DataTables::of($smtp_data)
        ->editColumn('enable_ssl', function($smtp_data) {
            return $smtp_data->enable_ssl ? 'Yes' : 'No';
        })
        ->editColumn('purchase', function($smtp_data) {
            return $smtp_data->purchase ? 'Yes' : 'No';
        })
        ->addColumn('options', function($smtp_data) {
            $action = '<div class="dropdown d-inline-block">
                <button class="btn btn-soft-secondary btn-sm dropdown" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="ri-more-fill align-middle"></i>
                </button>
                <ul class="dropdown-menu">';

            if (hasAccess("smtp_configuration", "edit")) {
                $action .= '<li><a class="dropdown-item edit-item-btn edit_smtp_configuration"><i class="ri-pencil-fill align-bottom me-2 text-muted" id="edit_a"></i> Edit</a></li>';
            }

            if (hasAccess("smtp_configuration", "delete")) {
                $action .= '<li> <a class="dropdown-item remove-item-btn">
                                <i class="ri-delete-bin-fill align-bottom me-2 text-muted" id="del_a"></i> Delete
                                </a>
                            </li>';
            }
            $action .= '</ul></div>';
            return $action;
        });

        $dataTable = applyCommonCreatedLastOnColumnsFilter($dataTable, 'smtp_configurations');
        return $dataTable
        ->rawColumns(['options', 'last_by', 'last_on', 'created_by', 'created_on'])
        ->make(true);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
                'mail_host' => 'required',
                'out_port_no' => 'required',
            ]);

            // Form-wise Duplicate Check for Purchase flag
            if ($request->has('purchase')) {
                $dupPo = SMTPConfiguration::where('purchase', 1)
                    ->exists();
                if ($dupPo) {
                    DB::rollBack();
                    return response()->json([
                        'response_code' => '0',
                        'response_message' => 'Duplicate Purchase Found.'
                    ]);
                }
            }

            $smtp_data = SMTPConfiguration::create([
                'email'             => $request->email,
                'cc_email'          => $request->cc_email,
                'password'          => $request->password,
                'mail_host'         => $request->mail_host,
                'out_port_no'       => $request->out_port_no,
                'enable_ssl'        => $request->has('enable_ssl') ? 1 : 0,
                'reply_email'       => $request->reply_email,
                'purchase'          => $request->has('purchase') ? 1 : 0,
                'created_on'        => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'created_by'        => Auth::user()->id
            ]);

            if ($smtp_data->save()) {
                DB::commit();
                return response()->json([
                    'response_code' => '1',
                    'response_message' => 'Record Inserted.',
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Something went wrong!',
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Something went wrong!',
                'original_error' => $e->getMessage()
            ]);
        }
    }

    public function edit(Request $request)
    {
        $smtp_data = SMTPConfiguration::where('sc_id', '=', $request->id)->first();
        if ($smtp_data) {
            // Return password as stored in plain text
            return response()->json([
                'smtp_data' => $smtp_data,
                'response_code' => '1',
                'response_message' => '',
            ]);
        } else {
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Record Does Not Exist.',
            ]);
        }
    }

    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $request->validate([
                'email' => 'required|email',
                'mail_host' => 'required',
                'out_port_no' => 'required',
            ]);

            // Form-wise Duplicate Check for Purchase flag
            if ($request->has('purchase')) {
                $dupPo = SMTPConfiguration::where('purchase', 1)
                    ->where('sc_id', '!=', $request->id)
                    ->exists();
                if ($dupPo) {
                    DB::rollBack();
                    return response()->json([
                        'response_code' => '0',
                        'response_message' => 'Duplicate Purchase Found.'
                    ]);
                }
            }

            $updateData = [
                'email'             => $request->email,
                'cc_email'          => $request->cc_email,
                'mail_host'         => $request->mail_host,
                'out_port_no'       => $request->out_port_no,
                'enable_ssl'        => $request->has('enable_ssl') ? 1 : 0,
                'reply_email'       => $request->reply_email,
                'purchase'          => $request->has('purchase') ? 1 : 0,
                'last_on'           => Carbon::now('Asia/Kolkata')->toDateTimeString(),
                'last_by'           => Auth::user()->id
            ];

            if ($request->filled('password')) {
                $updateData['password'] = $request->password;
            }

            $smtp_data = SMTPConfiguration::where('sc_id', '=', $request->id)->update($updateData);

            if ($smtp_data) {
                DB::commit();
                return response()->json([
                    'response_code' => '1',
                    'response_message' => 'Record Updated.',
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Something went wrong!',
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Something went wrong!',
                'original_error' => $e->getMessage()
            ]);
        }
    }

    public function destroy(Request $request)
    {
        DB::beginTransaction();
        try {
            $smtp_data = SMTPConfiguration::where('sc_id', '=', $request->id)->delete();
            if ($smtp_data) {
                DB::commit();
                return response()->json([
                    'response_code' => '1',
                    'response_message' => 'Record Deleted.',
                ]);
            } else {
                return response()->json([
                    'response_code' => '0',
                    'response_message' => 'Something went wrong!',
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'response_code' => '0',
                'response_message' => 'Something went wrong!',
                'original_error' => $e->getMessage()
            ]);
        }
    }
}
