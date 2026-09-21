<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DuplicationVerificationController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CompanyYearController;
use App\Http\Controllers\UserAccessController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\AcceptanceStandardController;
use App\Http\Controllers\OperatorController;
use App\Http\Controllers\ILacLogoController;
use App\Http\Controllers\CountryController;
use App\Http\Controllers\StateController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\EvaluationAsPerController;
use App\Http\Controllers\SensitivityController;
use App\Http\Controllers\ProcedureReferenceController;
use App\Http\Controllers\GstConfigurationController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TypeOfJobController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PartController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\EstimationCostingController;
use App\Http\Controllers\InquiryShortCloseController;
use App\Http\Controllers\JobDescriptionController;
use App\Http\Controllers\FeasibilityReviewController;
use App\Http\Controllers\ItemGroupController;
use App\Http\Controllers\ReasonController;
use App\Http\Controllers\RTCameraController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\EquipmentUTController;
use App\Http\Controllers\EquipmentMPTController;
use App\Http\Controllers\ProbeUTController;
use App\Http\Controllers\LPTChemicalController;
use App\Http\Controllers\MaterialMptController;
use App\Http\Controllers\ItemOpeningController;
use App\Http\Controllers\ItemOpeningProdAreaController;
use App\Http\Controllers\POShortCloseController;
use App\Http\Controllers\ItemReturnSlipController;
use App\Http\Controllers\POSummaryController;
use App\Http\Controllers\GRNSummaryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\Reports\PendInqForEastCostController;
use App\Http\Controllers\Reports\PendInqForFRController;
use App\Http\Controllers\Reports\PendInqForQuotationController;
use App\Http\Controllers\Reports\PendingPurchaseOrderforGRNController;
use App\Http\Controllers\FRSummaryController;
use App\Http\Controllers\InquirySummaryController;
use App\Http\Controllers\EstimationCostingSummaryController;
use App\Http\Controllers\PDF\ReportController;
use App\Http\Controllers\PDF\EmailController;
use App\Http\Controllers\Reports\QuotationSummaryController;
use App\Http\Controllers\Transaction\MaterialInwardController;
use App\Http\Controllers\Transaction\OfferController;
use App\Http\Controllers\Transaction\OfferAuthorizedController;
use App\Http\Controllers\Transaction\MaterialInspectionController;
use App\Http\Controllers\Transaction\NonRetMatChallanController;
use App\Http\Controllers\Transaction\OrderAcceptanceController;
use App\Http\Controllers\Transaction\PlanningManagementController;
use App\Http\Controllers\Transaction\POMappingProcessController;
use App\Http\Controllers\Transaction\GRNSupplierController;
use App\Http\Controllers\Transaction\InterLocationTransferController;
use App\Http\Controllers\Transaction\ServicePOController;
use App\Http\Controllers\Transaction\ItemIssueController;
use App\Http\Controllers\Transaction\GRNLocationController;
use App\Http\Controllers\Transaction\SupplierDCController;
use App\Http\Controllers\Transaction\ProductionEntryController;
use App\Http\Controllers\NABLConfigurationController;
use App\Http\Controllers\SMTPConfigurationController;
use App\Http\Controllers\DPTChemicalController;
use App\Http\Controllers\InstrumentController;
use App\Http\Controllers\PurchaseIndentController;
use App\Http\Controllers\PurchaseIndentShortCloseController;
use App\Http\Controllers\Reports\PendingPurchaseIndentforPOController;
use App\Http\Controllers\Reports\PurchaseIndentSummaryController;
use App\Http\Controllers\AssignFormatNoController;
use App\Http\Controllers\Reports\PurchaseOrderSummaryController;
use App\Http\Controllers\Reports\GRNSupplierSummaryController;
use App\Http\Controllers\Reports\InterLocationTransferSummaryController;
use App\Http\Controllers\Reports\GRNLocationSummaryController;
use App\Http\Controllers\Reports\ItemIssueInternalSummaryController;
use App\Http\Controllers\Reports\ServicePOSummaryController;
use App\Http\Controllers\Reports\SupplierDCSummaryController;
use App\Http\Controllers\Reports\ProductionSummaryController;
use App\Http\Controllers\Reports\AerbDocumentSummaryController;
use App\Http\Controllers\Transaction\DeliveryChallanCustomerController;
use App\Http\Controllers\Transaction\CustomerDCNonReturnableController;
use App\Http\Controllers\Transaction\ItemReturnCustomerController;
use App\Http\Controllers\Transaction\ServicePOShortCloseController;
use App\Http\Controllers\Transaction\TechniqueSheetRtController;
use App\Http\Controllers\Transaction\TestReportRtController;
use App\Http\Controllers\Transaction\MeasurementSheetController;
use App\Http\Controllers\Transaction\TestReportUtController;
use App\Http\Controllers\Transaction\TestReportDptController;
use App\Http\Controllers\Transaction\TestReportMptController;
use App\Http\Controllers\Transaction\RtLabelPrintController;
use App\Http\Controllers\Transaction\ObservationSheetController;

use App\Models\Transaction\DeliveryChallanCustomer;
use App\Http\Controllers\Reports\DeliveryChallanCustomerReportController;
use App\Http\Controllers\Reports\ItemStockSummaryController;
use App\Http\Controllers\Reports\PendingServicePOforDCController;
use App\Http\Controllers\Reports\PendingInterLocationTransferForGRNController;
use App\Http\Controllers\Reports\PendingSupplierDCForGRNController;
use App\Http\Controllers\Reports\PendingItemReturnFromCustomerController;
use App\Http\Controllers\Reports\ItemReturnCustomerSummaryController;
use App\Http\Controllers\Reports\ItemSrNoWiseStockSummaryController;
use App\Http\Controllers\IqiDesignationController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\IqiSensitivityController;
use App\Http\Controllers\AreaOfCoverageController;
use App\Http\Controllers\FilmBrandController;
use App\Http\Controllers\FilmTypeController;
use App\Http\Controllers\FindingLevelController;
use App\Http\Controllers\FindingController;
use App\Http\Controllers\AuthorityPersonController;
use App\Http\Controllers\EnclosureController;
use App\Http\Controllers\AerbDocumentController;
use App\Http\Controllers\FilmResultController;
use App\Http\Controllers\Reports\EnclosureRPDetailController;
use App\Http\Controllers\Reports\CameraMovementDetailReportController;
use App\Http\Controllers\Reports\CameraHistoryReportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

  Auth::routes();
  Route::post('/login',[AuthController::class,'login'])->name('login');
  Route::post('/logout',[AuthController::class,'logout'])->name('logout');
  Route::post('/check-login',[AdminController::class,'check'])->name('check-login');
  // Route::get('/manage-move_to_store',[ProductStoreController::class,'manage'])->name('manage-move_to_store');
  Route::get('/edit-user_access',[AdminController::class,'showUserAccess'])->name('edit-user_access');
  Route::get('/selectLocation',[AuthController::class,'selectYear'])->name('selectLocation')->middleware('auth.admin_access');

  Route::middleware(['auth','auth.user_access'])->group( function () {
    Route::get('/dashboard',[AuthController::class,'dashboard'])->middleware('clear.dashboard.cache')->name('dashboard'); // add middleware on 16-02-2026
    // Route::get('/dashboard',[AuthController::class,'dashboard'])->name('dashboard');
    Route::post('/expireDayDifference',[AuthController::class,'expireDayDifference'])->name('expireDayDifference');
    Route::get('/',[AuthController::class,'dashboard'])->name('/');
    Route::get('/get-user_access',[UserAccessController::class,'getUserAccess'])->name('get-user_access');
    Route::post('/update-user_access',[UserAccessController::class,'setUserAccess'])->name('update-user_access');
    Route::get('/get-pages',[UserAccessController::class,'getPages'])->name('get-pages');
    Route::get('/get-actions',[UserAccessController::class,'getActions'])->name('get-actions');
    Route::get('/get-access_modules',[UserAccessController::class,'getAccessModules'])->name('get-access_modules');

    /* Manage Route */
    Route::get('/manage-company_year',[CompanyYearController::class,'manage'])->name('manage-company_year');
    Route::get('/manage-company',[CompanyController::class,'manage'])->name('manage-company');
    Route::get('/manage-user',[AdminController::class,'manage'])->name('manage-user');
    Route::get('/manage-acceptance_standard',[AcceptanceStandardController::class,'manage'])->name('manage-acceptance_standard');
    Route::get('/manage-operator',[OperatorController::class,'manage'])->name('manage-operator');
    Route::get('/manage-country',[CountryController::class,'manage'])->name('manage-country');
    Route::get('/manage-state',[StateController::class,'manage'])->name('manage-state');
    Route::get('/manage-city',[CityController::class,'manage'])->name('manage-city');
    Route::get('/manage-material',[MaterialController::class,'manage'])->name('manage-material');
    Route::get('/manage-evaluation_as_per',[EvaluationAsPerController::class,'manage'])->name('manage-evaluation_as_per');
    Route::get('/manage-sensitivity',[SensitivityController::class,'manage'])->name('manage-sensitivity');
    Route::get('/manage-procedure_reference',[ProcedureReferenceController::class,'manage'])->name('manage-procedure_reference');
    Route::get('/manage-gst_configuration',[GstConfigurationController::class,'manage'])->name('manage-gst_configuration');
    Route::get('/manage-customer',[CustomerController::class,'manage'])->name('manage-customer');
    Route::get('/manage-supplier',[SupplierController::class,'manage'])->name('manage-supplier');
    Route::get('/manage-inquiry',[InquiryController::class,'manage'])->name('manage-inquiry');
    Route::get('/manage-type_of_job',[TypeOfJobController::class,'manage'])->name('manage-type_of_job');
    Route::get('/manage-unit',[UnitController::class,'manage'])->name('manage-unit');
    Route::get('/manage-shift',[ShiftController::class,'manage'])->name('manage-shift');
    Route::get('/manage-item',[ItemController::class,'manage'])->name('manage-item');
    Route::get('/manage-quotation',[QuotationController::class,'manage'])->name('manage-quotation');
    Route::get('/manage-part',[PartController::class,'manage'])->name('manage-part');
    Route::get('/manage-estimation_costing',[EstimationCostingController::class,'manage'])->name('manage-estimation_costing');
    Route::get('/manage-inquiry_short_close',[InquiryShortCloseController::class,'manage'])->name('manage-inquiry_short_close');
    Route::get('/manage-job_description',[JobDescriptionController::class,'manage'])->name('manage-job_description');
    Route::get('/manage-feasibility_review',[FeasibilityReviewController::class,'manage'])->name('manage-feasibility_review');
    Route::get('/manage-item_group',[ItemGroupController::class,'manage'])->name('manage-item_group');
    Route::get('/manage-reason',[ReasonController::class,'manage'])->name('manage-reason');
    Route::get('/manage-rt_camera',[RTCameraController::class,'manage'])->name('manage-rt_camera');
    Route::get('/manage-grn_supplier',[GRNSupplierController::class,'manage'])->name('manage-grn_supplier');
    Route::get('/manage-purchase_order',[PurchaseOrderController::class,'manage'])->name('manage-purchase_order');
    Route::get('/manage-equipment_ut',[EquipmentUTController::class,'manage'])->name('manage-equipment_ut');
    Route::get('/manage-equipment_mpt',[EquipmentMPTController::class,'manage'])->name('manage-equipment_mpt');
    Route::get('/manage-probe_ut',[ProbeUTController::class,'manage'])->name('manage-probe_ut');
    Route::get('/manage-lpt_chemical',[LPTChemicalController::class,'manage'])->name('manage-lpt_chemical');
    Route::get('/manage-material_mpt',[MaterialMptController::class,'manage'])->name('manage-material_mpt');
    Route::get('/manage-po_short_close',[POShortCloseController::class,'manage'])->name('manage-po_short_close');
    Route::get('/manage-item_opening',[ItemOpeningController::class,'manage'])->name('manage-item_opening');
    Route::get('/manage-item_opening_prod_area',[ItemOpeningProdAreaController::class,'manage'])->name('manage-item_opening_prod_area');
    Route::get('/manage-item_return_slip',[ItemReturnSlipController::class,'manage'])->name('manage-item_return_slip');
    Route::get('/manage-material_inward',[MaterialInwardController::class,'manage'])->name('manage-material_inward');
    Route::get('/manage-offer',[OfferController::class,'manage'])->name('manage-offer');
    Route::get('/manage-offer_authorised',[OfferAuthorizedController::class,'manage'])->name('manage-offer_authorised');
    Route::get('/manage-material_inspection',[MaterialInspectionController::class,'manage'])->name('manage-material_inspection');
    Route::get('/manage-order_acceptance',[OrderAcceptanceController::class,'manage'])->name('manage-order_acceptance');
    Route::get('/manage-non_returnable_material_challan',[NonRetMatChallanController::class,'manage'])->name('manage-non_returnable_material_challan');
    Route::get('/manage-planning_management',[PlanningManagementController::class,'manage'])->name('manage-planning_management');
    Route::get('/manage-po_mapping_process',[POMappingProcessController::class,'manage'])->name('manage-po_mapping_process');
    Route::get('/manage-location',[LocationController::class,'manage'])->name('manage-location');
    Route::get('/manage-nabl_configuration',[NABLConfigurationController::class,'manage'])->name('manage-nabl_configuration');
    Route::get('/manage-smtp_configuration',[SMTPConfigurationController::class,'manage'])->name('manage-smtp_configuration');
    Route::get('/manage-chemical_dpt',[DPTChemicalController::class,'manage'])->name('manage-chemical_dpt');
    Route::get('/manage-instrument',[InstrumentController::class,'manage'])->name('manage-instrument');
    Route::get('/manage-purchase_indent',[PurchaseIndentController::class,'manage'])->name('manage-purchase_indent');
    Route::get('/manage-purchase_indent_short_close',[PurchaseIndentShortCloseController::class,'manage'])->name('manage-purchase_indent_short_close');
    Route::get('/manage-pending_purchase_indent_for_po',[PendingPurchaseIndentforPOController::class,'manage'])->name('manage-pending_purchase_indent_for_po');
    Route::get('/manage-purchase_indent_summary',[PurchaseIndentSummaryController::class,'manage'])->name('manage-purchase_indent_summary');
    Route::get('/manage-assign_format_no',[AssignFormatNoController::class,'manage'])->name('manage-assign_format_no');
    Route::get('/manage-inter_location_transfer',[InterLocationTransferController::class,'manage'])->name('manage-inter_location_transfer');
    Route::get('/manage-service_po',[ServicePOController::class,'manage'])->name('manage-service_po');
    Route::get('/manage-item_issue',[ItemIssueController::class,'manage'])->name('manage-item_issue');
    Route::get('/manage-grn_location',[GRNLocationController::class,'manage'])->name('manage-grn_location');
    Route::get('/manage-service_po_short_close',[ServicePOShortCloseController::class,'manage'])->name('manage-service_po_short_close');
    Route::get('/manage-supplier_dc',[SupplierDCController::class,'manage'])->name('manage-supplier_dc');
    Route::get('/manage-delivery_challan_customer',[DeliveryChallanCustomerController::class,'manage'])->name('manage-delivery_challan_customer');
    Route::get('/manage-customer_dc_non_returnable',[CustomerDCNonReturnableController::class,'manage'])->name('manage-customer_dc_non_returnable');
    Route::get('/manage-item_return_customer',[ItemReturnCustomerController::class,'manage'])->name('manage-item_return_customer');
    Route::get('/manage-iqi_designation', [IqiDesignationController::class, 'manage'])->name('manage-iqi_designation');
    Route::get('/manage-film', [FilmController::class, 'manage'])->name('manage-film');
    Route::get('/manage-iqi_sensitivity', [IqiSensitivityController::class, 'manage'])->name('manage-iqi_sensitivity');
    Route::get('/manage-area_of_coverage', [AreaOfCoverageController::class, 'manage'])->name('manage-area_of_coverage');
    Route::get('/manage-film_brand', [FilmBrandController::class, 'manage'])->name('manage-film_brand');
    Route::get('/manage-film_type', [FilmTypeController::class, 'manage'])->name('manage-film_type');
    Route::get('/manage-finding_level', [FindingLevelController::class, 'manage'])->name('manage-finding_level');
    Route::get('/manage-finding', [FindingController::class, 'manage'])->name('manage-finding');
    Route::get('/manage-authority_person', [AuthorityPersonController::class, 'manage'])->name('manage-authority_person');
    Route::get('/manage-enclosure', [EnclosureController::class, 'manage'])->name('manage-enclosure');
    Route::get('/manage-aerb_documents', [AerbDocumentController::class, 'manage'])->name('manage-aerb_documents');
    Route::get('/manage-film_result', [FilmResultController::class, 'manage'])->name('manage-film_result');
    Route::get('manage-production_entry',                    [ProductionEntryController::class, 'manage'])->name('manage-production_entry');;
    Route::get('manage-technique_sheet_rt',                  [TechniqueSheetRtController::class, 'manage'])->name('manage-technique_sheet_rt');
    Route::get('manage-test_report_rt',                     [TestReportRtController::class, 'manage'])->name('manage-test_report_rt');
    Route::get('manage-rt_label_print',                     [RtLabelPrintController::class, 'manage'])->name('manage-rt_label_print');
    Route::get('manage-measurement_sheet',                  [MeasurementSheetController::class, 'manage'])->name('manage-measurement_sheet');
    Route::get('manage-test_report_ut',                     [TestReportUtController::class, 'manage'])->name('manage-test_report_ut');
    Route::get('manage-test_report_dpt',                    [TestReportDptController::class, 'manage'])->name('manage-test_report_dpt');
    Route::get('manage-test_report_mpt',                    [TestReportMptController::class, 'manage'])->name('manage-test_report_mpt');

    Route::get('manage-observation_sheet', [ObservationSheetController::class, 'manage'])->name('manage-observation_sheet');
    


    /* manage summary routes */
    Route::get('/manage-po_summary',[POSummaryController::class,'manage'])->name('manage-po_summary');
    Route::get('/manage-grn_supplier_summary',[GRNSupplierSummaryController::class,'manage'])->name('manage-grn_supplier_summary');
    Route::get('/manage-pend_inq_for_fr',[PendInqForFRController::class,'manage'])->name('manage-pend_inq_for_fr');
    Route::get('/manage-pend_inq_for_ec',[PendInqForEastCostController::class,'manage'])->name('manage-pend_inq_for_ec');
    Route::get('/manage-pend_inq_for_quotation',[PendInqForQuotationController::class,'manage'])->name('manage-pend_inq_for_quotation');
    Route::get('/manage-fr_summary',[FRSummaryController::class,'manage'])->name('manage-fr_summary');
    Route::get('/manage-inquiry_summary',[InquirySummaryController::class,'manage'])->name('manage-inquiry_summary');
    Route::get('/manage-estimation_costing_summary',[EstimationCostingSummaryController::class,'manage'])->name('manage-estimation_costing_summary');
    Route::get('/manage-quotation_summary',[QuotationSummaryController::class,'manage'])->name('manage-quotation_summary');
    Route::get('/manage-purchase_order_summary',[PurchaseOrderSummaryController::class,'manage'])->name('manage-purchase_order_summary');
    Route::get('/manage-pending_purchase_order_for_grn',[PendingPurchaseOrderforGRNController::class,'manage'])->name('manage-pending_purchase_order_for_grn');
    Route::get('/manage-inter_location_transfer_summary',[InterLocationTransferSummaryController::class,'manage'])->name('manage-inter_location_transfer_summary');
    Route::get('/manage-grn_location_summary',[GRNLocationSummaryController::class,'manage'])->name('manage-grn_location_summary');
    Route::get('/manage-item_issue_internal_summary',[ItemIssueInternalSummaryController::class,'manage'])->name('manage-item_issue_internal_summary');
    Route::get('/manage-service_po_summary',[ServicePOSummaryController::class,'manage'])->name('manage-service_po_summary');
    Route::get('/manage-supplier_dc_summary',[SupplierDCSummaryController::class,'manage'])->name('manage-supplier_dc_summary');
    Route::get('/manage-delivery_challan_customer_report',[DeliveryChallanCustomerReportController::class,'manage'])->name('manage-delivery_challan_customer_report');
    Route::get('/manage-pending_service_po_for_dc',[PendingServicePOforDCController::class,'manage'])->name('manage-pending_service_po_for_dc');
    Route::get('/manage-pending_inter_location_transfer_for_grn',[PendingInterLocationTransferForGRNController::class,'manage'])->name('manage-pending_inter_location_transfer_for_grn');
    Route::get('/manage-item_stock_summary',[ItemStockSummaryController::class,'manage'])->name('manage-item_stock_summary');
    Route::get('/manage-pending_supplier_dc_for_grn',[PendingSupplierDCForGRNController::class,'manage'])->name('manage-pending_supplier_dc_for_grn');
    Route::get('/manage-pending_item_return_from_customer',[PendingItemReturnFromCustomerController::class,'manage'])->name('manage-pending_item_return_from_customer');
    Route::get('/manage-item_return_customer_summary',[ItemReturnCustomerSummaryController::class,'manage'])->name('manage-item_return_customer_summary');
    Route::get('/manage-item_sr_no_wise_stock_summary',[ItemSrNoWiseStockSummaryController::class,'manage'])->name('manage-item_sr_no_wise_stock_summary');
    Route::get('/manage-production_summary',[ProductionSummaryController::class,'manage'])->name('manage-production_summary');
    Route::get('/manage-aerb_documents_summary',[AerbDocumentSummaryController::class,'manage'])->name('manage-aerb_documents_summary');
    Route::get('/manage-enclosure_rp_detail',[EnclosureRPDetailController::class,'manage'])->name('manage-enclosure_rp_detail');
    Route::get('/manage-camera_movement_detail',[CameraMovementDetailReportController::class,'manage'])->name('manage-camera_movement_detail');
    Route::get('/manage-camera_history_report',[CameraHistoryReportController::class,'manage'])->name('manage-camera_history_report');


    /* Listing Route */
    Route::post('/listing-user',[AdminController::class,'index'])->name('listing-user');
    Route::post('/listing-company_year',[CompanyYearController::class,'index'])->name('listing-company_year');
    Route::post('/listing-acceptance_standard',[AcceptanceStandardController::class,'index'])->name('listing-acceptance_standard');
    Route::post('/listing-operator',[OperatorController::class,'index'])->name('listing-operator');
    Route::post('/listing-country',[CountryController::class,'index'])->name('listing-country');
    Route::post('/listing-state',[StateController::class,'index'])->name('listing-state');
    Route::post('/listing-city',[CityController::class,'index'])->name('listing-city');
    Route::post('/listing-material',[MaterialController::class,'index'])->name('listing-material');
    Route::post('/listing-evaluation_as_per',[EvaluationAsPerController::class,'index'])->name('listing-evaluation_as_per');
    Route::post('/listing-sensitivity',[SensitivityController::class,'index'])->name('listing-sensitivity');
    Route::post('/listing-procedure_reference',[ProcedureReferenceController::class,'index'])->name('listing-procedure_reference');
    Route::post('/listing-gst_configuration',[GstConfigurationController::class,'index'])->name('listing-gst_configuration');
    Route::post('/listing-customer',[CustomerController::class,'index'])->name('listing-customer');
    Route::post('/listing-supplier',[SupplierController::class,'index'])->name('listing-supplier');
    Route::post('/listing-inquiry',[InquiryController::class,'index'])->name('listing-inquiry');
    Route::post('/listing-type_of_job',[TypeOfJobController::class,'index'])->name('listing-type_of_job');
    Route::post('/listing-unit',[UnitController::class,'index'])->name('listing-unit');
    Route::post('/listing-shift',[ShiftController::class,'index'])->name('listing-shift');
    Route::post('/listing-quotation',[QuotationController::class,'index'])->name('listing-quotation');
    Route::post('/listing-estimation_costing',[EstimationCostingController::class,'index'])->name('listing-estimation_costing');
    Route::post('/listing-inquiry_short_close',[InquiryShortCloseController::class,'index'])->name('listing-inquiry_short_close');
    Route::post('/listing-job_description',[JobDescriptionController::class,'index'])->name('listing-job_description');
    Route::post('/listing-item_group',[ItemGroupController::class,'index'])->name('listing-item_group');
    Route::post('/listing-item',[ItemController::class,'index'])->name('listing-item');
    Route::post('/listing-part',[PartController::class,'index'])->name('listing-part');
    Route::post('/listing-feasibility_review',[FeasibilityReviewController::class,'index'])->name('listing-feasibility_review');
    Route::post('/listing-reason',[ReasonController::class,'index'])->name('listing-reason');
    Route::post('/listing-rt_camera',[RTCameraController::class,'index'])->name('listing-rt_camera');
    Route::post('/listing-grn_supplier',[GRNSupplierController::class,'index'])->name('listing-grn_supplier');
    Route::post('/listing-purchase_order',[PurchaseOrderController::class,'index'])->name('listing-purchase_order');
    Route::post('/listing-equipment_ut',[EquipmentUTController::class,'index'])->name('listing-equipment_ut');
    Route::post('/listing-equipment_mpt',[EquipmentMPTController::class,'index'])->name('listing-equipment_mpt');
    Route::post('/listing-lpt_chemical',[LPTChemicalController::class,'index'])->name('listing-lpt_chemical');
    Route::post('/listing-probe_ut',[ProbeUTController::class,'index'])->name('listing-probe_ut');
    Route::post('/listing-material_mpt',[MaterialMptController::class,'index'])->name('listing-material_mpt');
    Route::post('/listing-po_short_close',[POShortCloseController::class,'index'])->name('listing-po_short_close');
    Route::post('/listing-item_return_slip',[ItemReturnSlipController::class,'index'])->name('listing-item_return_slip');
    Route::post('/listing-location',[LocationController::class,'index'])->name('listing-location');
    Route::post('/listing-nabl_configuration',[NABLConfigurationController::class,'index'])->name('listing-nabl_configuration');
    Route::post('/listing-smtp_configuration',[SMTPConfigurationController::class,'index'])->name('listing-smtp_configuration');
    Route::post('/listing-chemical_dpt',[DPTChemicalController::class,'index'])->name('listing-chemical_dpt');
    Route::post('/listing-instrument',[InstrumentController::class,'index'])->name('listing-instrument');
    Route::post('/listing-purchase_indent',[PurchaseIndentController::class,'index'])->name('listing-purchase_indent');
    Route::post('/listing-purchase_indent_short_close',[PurchaseIndentShortCloseController::class,'index'])->name('listing-purchase_indent_short_close');
    
    Route::post('/listing-material_inward',[MaterialInwardController::class,'index'])->name('listing-material_inward');
    Route::post('/listing-offer',[OfferController::class,'index'])->name('listing-offer');
    Route::post('/listing-offer_authorized',[OfferAuthorizedController::class,'index'])->name('listing-offer_authorized');
    Route::post('/listing-material_inspection',[MaterialInspectionController::class,'index'])->name('listing-material_inspection');
    Route::post('/listing-order_acceptance',[OrderAcceptanceController::class,'index'])->name('listing-order_acceptance');
    Route::post('/listing-non_returnable_material_challan',[NonRetMatChallanController::class,'index'])->name('listing-non_returnable_material_challan');
    Route::post('/listing-planning_management',[PlanningManagementController::class,'index'])->name('listing-planning_management');
    Route::post('/listing-po_mapping_process',[POMappingProcessController::class,'index'])->name('listing-po_mapping_process');
    Route::post('/listing-inter_location_transfer',[InterLocationTransferController::class,'index'])->name('listing-inter_location_transfer');
    Route::post('/listing-service_po',[ServicePOController::class,'index'])->name('listing-service_po');
    Route::post('/listing-item_issue',[ItemIssueController::class,'index'])->name('listing-item_issue');
    Route::post('/listing-service_po_short_close',[ServicePOShortCloseController::class,'index'])->name('listing-service_po_short_close');
    Route::post('/listing-supplier_dc',[SupplierDCController::class,'index'])->name('listing-supplier_dc');
    Route::post('/listing-delivery_challan_customer',[DeliveryChallanCustomerController::class,'index'])->name('listing-supplier_dc');
    Route::post('/listing-customer_dc_non_returnable',[CustomerDCNonReturnableController::class,'index'])->name('listing-customer_dc_non_returnable');
    Route::post('/listing-item_issue_internal_summary',[ItemIssueInternalSummaryController::class,'index'])->name('listing-item_issue_internal_summary');
    Route::post('/listing-item_return_customer',[ItemReturnCustomerController::class,'index'])->name('listing-item_return_customer');
    Route::post('/listing-grn_location',[GRNLocationController::class,'index'])->name('listing-grn_location');
    Route::post('/listing-iqi_designation', [IqiDesignationController::class, 'index'])->name('listing-iqi_designation');
    Route::post('/listing-film', [FilmController::class, 'index'])->name('listing-film');
    Route::post('/listing-iqi_sensitivity', [IqiSensitivityController::class, 'index'])->name('listing-iqi_sensitivity');
    Route::post('/listing-area_of_coverage', [AreaOfCoverageController::class, 'index'])->name('listing-area_of_coverage');
    Route::post('/listing-film_brand', [FilmBrandController::class, 'index'])->name('listing-film_brand');
    Route::post('/listing-film_type', [FilmTypeController::class, 'index'])->name('listing-film_type');
    Route::post('/listing-finding_level', [FindingLevelController::class, 'index'])->name('listing-finding_level');
    Route::post('/listing-finding', [FindingController::class, 'index'])->name('listing-finding');
    Route::post('/listing-authority_person', [AuthorityPersonController::class, 'index'])->name('listing-authority_person');
    Route::post('/listing-enclosure', [EnclosureController::class, 'index'])->name('listing-enclosure');
    Route::post('/listing-aerb_documents', [AerbDocumentController::class, 'index'])->name('listing-aerb_documents');
    Route::post('/listing-film_result', [FilmResultController::class, 'index'])->name('listing-film_result');
    Route::post('listing-production_entry',                  [ProductionEntryController::class, 'index'])->name('listing-production_entry');
    Route::post('listing-technique_sheet_rt',                [TechniqueSheetRtController::class, 'index'])->name('listing-technique_sheet_rt');
    Route::post('listing-test_report_rt',                    [TestReportRtController::class, 'index'])->name('listing-test_report_rt');
    Route::post('listing-measurement_sheet',                 [MeasurementSheetController::class, 'index'])->name('listing-measurement_sheet');
    Route::post('listing-test_report_ut',                    [TestReportUtController::class, 'index'])->name('listing-test_report_ut');
    Route::post('listing-test_report_dpt',                   [TestReportDptController::class, 'index'])->name('listing-test_report_dpt');
    Route::post('listing-test_report_mpt',                   [TestReportMptController::class, 'index'])->name('listing-test_report_mpt');
     Route::post('listing-observation_sheet', [ObservationSheetController::class, 'index'])->name('listing-observation_sheet');



    /* Reports Listing Routes*/
    Route::post('/listing-po_summary',[POSummaryController::class,'index'])->name('listing-po_summary');
    Route::post('/listing-grn_supplier_summary',[GRNSupplierSummaryController::class,'index'])->name('listing-grn_supplier_summary');
    Route::post('/listing-pend_inq_for_fr',[PendInqForFRController::class,'index'])->name('listing-pend_inq_for_fr');
    Route::post('/listing-pend_inq_for_ec',[PendInqForEastCostController::class,'index'])->name('listing-pend_inq_for_ec');
    Route::post('/listing-pend_inq_for_quotation',[PendInqForQuotationController::class,'index'])->name('listing-pend_inq_for_quotation');
    Route::post('/listing-fr_summary',[FRSummaryController::class,'index'])->name('listing-fr_summary');
    Route::post('/listing-inquiry_summary',[InquirySummaryController::class,'index'])->name('listing-inquiry_summary');
    Route::post('/listing-estimation_costing_summary',[EstimationCostingSummaryController::class,'index'])->name('listing-estimation_costing_summary');
    Route::post('/listing-quotation_summary',[QuotationSummaryController::class,'index'])->name('listing-quotation_summary');
    Route::post('/listing-pend_purchase_indent_for_po',[PendingPurchaseIndentforPOController::class,'index'])->name('listing-pend_purchase_indent_for_po');
    Route::post('/listing-purchase_indent_summary',[PurchaseIndentSummaryController::class,'index'])->name('listing-purchase_indent_summary');
    Route::post('/listing-purchase_order_summary',[PurchaseOrderSummaryController::class,'index'])->name('listing-purchase_order_summary');
    Route::post('/listing-pending_purchase_order_for_grn',[PendingPurchaseOrderForGRNController::class,'index'])->name('listing-pending_purchase_order_for_grn');
    Route::post('/listing-grn_location_summary',[GRNLocationSummaryController::class,'index'])->name('listing-grn_location_summary');
    Route::post('/listing-inter_location_transfer_summary',[InterLocationTransferSummaryController::class,'index'])->name('listing-inter_location_transfer_summary');
    Route::post('/listing-service_po_summary',[ServicePOSummaryController::class,'index'])->name('listing-service_po_summary');
    Route::post('/listing-supplier_dc_summary',[SupplierDCSummaryController::class,'index'])->name('listing-supplier_dc_summary');
    Route::post('/listing-delivery_challan_customer_report',[DeliveryChallanCustomerReportController::class,'index'])->name('listing-delivery_challan_customer_report');
    Route::post('/listing-pending_service_po_for_dc',[PendingServicePOforDCController::class,'index'])->name('listing-pending_service_po_for_dc');
    Route::post('/listing-pending_inter_location_transfer_for_grn',[PendingInterLocationTransferForGRNController::class,'index'])->name('listing-pending_inter_location_transfer_for_grn');
    Route::post('/listing-item_stock_summary',[ItemStockSummaryController::class,'index'])->name('listing-item_stock_summary');
    Route::post('/listing-pending_supplier_dc_for_grn',[PendingSupplierDCForGRNController::class,'index'])->name('listing-pending_supplier_dc_for_grn');
    Route::post('/listing-pending_item_return_from_customer',[PendingItemReturnFromCustomerController::class,'index'])->name('listing-pending_item_return_from_customer');
    Route::post('/listing-item_return_customer_summary',[ItemReturnCustomerSummaryController::class,'index'])->name('listing-item_return_customer_summary');
    Route::post('/listing-item_sr_no_wise_stock_summary',[ItemSrNoWiseStockSummaryController::class,'index'])->name('listing-item_sr_no_wise_stock_summary');
    Route::post('/listing-production_summary',[ProductionSummaryController::class,'index'])->name('listing-production_summary');
    Route::post('/listing-aerb_documents_summary',[AerbDocumentSummaryController::class,'index'])->name('listing-aerb_documents_summary');
     Route::post('listing-enclosure_rp_detail',                    [EnclosureRPDetailController::class, 'index'])->name('listing-enclosure_rp_detail');
    Route::post('/listing-camera_movement_detail',[CameraMovementDetailReportController::class,'index'])->name('listing-camera_movement_detail');
    Route::post('/listing-camera_history_report',[CameraHistoryReportController::class,'index'])->name('listing-camera_history_report');
    

    /* Store Route */
    Route::post('/store-company_year',[CompanyYearController::class,'store'])->name('store-company_year');
    Route::post('/store-user',[AdminController::class,'store'])->name('store-user');
    Route::post('/store-acceptance_standard',[AcceptanceStandardController::class,'store'])->name('store-acceptance_standard');
    Route::post('/store-operator',[OperatorController::class,'store'])->name('store-operator');
    Route::post('/store-country',[CountryController::class,'store'])->name('store-country');
    Route::post('/store-state',[StateController::class,'store'])->name('store-state');
    Route::post('/store-city',[CityController::class,'store'])->name('store-city');
    Route::post('/store-material',[MaterialController::class,'store'])->name('store-material');
    Route::post('/store-evaluation_as_per',[EvaluationAsPerController::class,'store'])->name('store-evaluation_as_per');
    Route::post('/store-sensitivity',[SensitivityController::class,'store'])->name('store-sensitivity');
    Route::post('/store-procedure_reference',[ProcedureReferenceController::class,'store'])->name('store-procedure_reference');
    Route::post('/store-gst_configuration',[GstConfigurationController::class,'store'])->name('store-gst_configuration');
    Route::post('/store-customer',[CustomerController::class,'store'])->name('store-customer');
    Route::post('/store-supplier',[SupplierController::class,'store'])->name('store-supplier');
    Route::post('/store-inquiry',[InquiryController::class,'store'])->name('store-inquiry');
    Route::post('/store-type_of_job',[TypeOfJobController::class,'store'])->name('store-type_of_job');
    Route::post('/store-unit',[UnitController::class,'store'])->name('store-unit');
    Route::post('/store-shift',[ShiftController::class,'store'])->name('store-shift');
    Route::post('/store-estimation_costing',[EstimationCostingController::class,'store'])->name('store-estimation_costing');
    Route::post('/store-inquiry_short_close',[InquiryShortCloseController::class,'store'])->name('store-inquiry_short_close');
    Route::post('/store-job_description',[JobDescriptionController::class,'store'])->name('store-job_description');
    Route::post('/store-item_group',[ItemGroupController::class,'store'])->name('store-item_group');
    Route::post('/store-item',[ItemController::class,'store'])->name('store-item');
    Route::post('/store-part',[PartController::class,'store'])->name('store-part');
    Route::post('/store-feasibility_review',[FeasibilityReviewController::class,'store'])->name('store-feasibility_review');
    Route::post('/store-quotation',[QuotationController::class,'store'])->name('store-quotation');
    Route::post('/store-reason',[ReasonController::class,'store'])->name('store-reason');
    Route::post('/store-rt_camera',[RTCameraController::class,'store'])->name('store-rt_camera');
    Route::post('/store-inquiry_short_close',[InquiryShortCloseController::class,'store'])->name('store-inquiry_short_close');
    Route::post('/store-grn_supplier',[GRNSupplierController::class,'store'])->name('store-grn_supplier');
    Route::post('/store-purchase_order',[PurchaseOrderController::class,'store'])->name('store-purchase_order');
    Route::post('/store-equipment_ut',[EquipmentUTController::class,'store'])->name('store-equipment_ut');
    Route::post('/store-equipment_mpt',[EquipmentMPTController::class,'store'])->name('store-equipment_mpt');
    Route::post('/store-lpt_chemical',[LPTChemicalController::class,'store'])->name('store-lpt_chemical');
    Route::post('/store-probe_ut',[ProbeUTController::class,'store'])->name('store-probe_ut');
    Route::post('/store-material_mpt',[MaterialMptController::class,'store'])->name('store-material_mpt');
    Route::post('/store-po_short_close',[POShortCloseController::class,'store'])->name('store-po_short_close');
    Route::post('/store-item_opening',[ItemOpeningController::class,'store'])->name('store-item_opening');
    Route::post('/store-item_opening_prod_area',[ItemOpeningProdAreaController::class,'store'])->name('store-item_opening_prod_area');
    Route::post('/store-item_return_slip',[ItemReturnSlipController::class,'store'])->name('store-item_return_slip');
    Route::post('/store-material_inward',[MaterialInwardController::class,'store'])->name('store-material_inward');
    Route::post('/store-offer',[OfferController::class,'store'])->name('store-offer');
    Route::post('/store-material_inspection',[MaterialInspectionController::class,'store'])->name('store-material_inspection');
    Route::post('/store-order_acceptance',[OrderAcceptanceController::class,'store'])->name('store-order_acceptance');
    Route::post('/store-non_returnable_material_challan',[NonRetMatChallanController::class,'store'])->name('store-non_returnable_material_challan');
    Route::post('/store-planning_management',[PlanningManagementController::class,'store'])->name('store-planning_management');
    Route::post('/store-po_mapping_process',[POMappingProcessController::class,'store'])->name('store-po_mapping_process');
    Route::post('/store-location',[LocationController::class,'store'])->name('store-location');
    Route::post('/store-nabl_configuration',[NABLConfigurationController::class,'store'])->name('store-nabl_configuration');
    Route::post('/store-smtp_configuration',[SMTPConfigurationController::class,'store'])->name('store-smtp_configuration');
    Route::post('/store-chemical_dpt',[DPTChemicalController::class,'store'])->name('store-chemical_dpt');
    Route::post('/store-instrument',[InstrumentController::class,'store'])->name('store-instrument');
    Route::post('/store-purchase_indent',[PurchaseIndentController::class,'store'])->name('store-purchase_indent');
    Route::post('/store-purchase_indent_short_close',[PurchaseIndentShortCloseController::class,'store'])->name('store-purchase_indent_short_close');
    Route::post('/store-assign_format_no',[AssignFormatNoController::class,'store'])->name('store-assign_format_no');
    Route::post('/store-inter_location_transfer',[InterLocationTransferController::class,'store'])->name('store-inter_location_transfer');
    Route::post('/store-item_issue',[ItemIssueController::class,'store'])->name('store-item_issue');
    Route::post('/store-service_po',[ServicePOController::class,'store'])->name('store-service_po');
    Route::post('/store-service_po_short_close',[ServicePOShortCloseController::class,'store'])->name('store-service_po_short_close');
    Route::post('/store-supplier_dc',[SupplierDCController::class,'store'])->name('store-supplier_dc');
    Route::post('/store-delivery_challan_customer',[DeliveryChallanCustomerController::class,'store'])->name('store-delivery_challan_customer');
    Route::post('/store-customer_dc_non_returnable',[CustomerDCNonReturnableController::class,'store'])->name('store-customer_dc_non_returnable');
    Route::post('/store-item_return_customer ',[ItemReturnCustomerController::class,'store'])->name('store-item_return_customer ');
    Route::post('/store-grn_location',[GRNLocationController::class,'store'])->name('store-grn_location');
    Route::post('/store-iqi_designation', [IqiDesignationController::class, 'store'])->name('store-iqi_designation');
    Route::post('/store-film', [FilmController::class, 'store'])->name('store-film');
    Route::post('/store-iqi_sensitivity', [IqiSensitivityController::class, 'store'])->name('store-iqi_sensitivity');
    Route::post('/store-area_of_coverage', [AreaOfCoverageController::class, 'store'])->name('store-area_of_coverage');
    Route::post('/store-film_brand', [FilmBrandController::class, 'store'])->name('store-film_brand');
    Route::post('/store-film_type', [FilmTypeController::class, 'store'])->name('store-film_type');
    Route::post('/store-finding_level', [FindingLevelController::class, 'store'])->name('store-finding_level');
    Route::post('/store-finding', [FindingController::class, 'store'])->name('store-finding');
    Route::post('/store-authority_person', [AuthorityPersonController::class, 'store'])->name('store-authority_person');
    Route::post('/store-enclosure', [EnclosureController::class, 'store'])->name('store-enclosure');
    Route::post('/store-aerb_documents', [AerbDocumentController::class, 'store'])->name('store-aerb_documents');
    Route::post('/store-film_result', [FilmResultController::class, 'store'])->name('store-film_result');
    Route::post('store-production_entry',                    [ProductionEntryController::class, 'store'])->name('store-production_entry');
    Route::post('store-technique_sheet_rt',                  [TechniqueSheetRtController::class, 'store'])->name('store-technique_sheet_rt');
    Route::post('store-test_report_rt',                      [TestReportRtController::class, 'store'])->name('store-test_report_rt');
    Route::post('store-measurement_sheet',                   [MeasurementSheetController::class, 'store'])->name('store-measurement_sheet');
    Route::post('store-test_report_ut',                      [TestReportUtController::class, 'store'])->name('store-test_report_ut');
    Route::post('store-test_report_dpt',                     [TestReportDptController::class, 'store'])->name('store-test_report_dpt');
    Route::post('store-test_report_mpt',                     [TestReportMptController::class, 'store'])->name('store-test_report_mpt');
    Route::post('store-observation_sheet', [ObservationSheetController::class, 'store'])->name('store-observation_sheet');
    

    /* Edit Route */
    Route::get('/edit-user',[AdminController::class,'edit'])->name('edit-user');
    Route::get('/edit-user_access',[AdminController::class,'showUserAccess'])->name('edit-user_access');
    Route::get('/edit-acceptance_standard',[AcceptanceStandardController::class,'edit'])->name('edit-acceptance_standard');
    Route::get('/edit-operator',[OperatorController::class,'edit'])->name('edit-operator');
    Route::get('/edit-country',[CountryController::class,'edit'])->name('edit-country');
    Route::get('/edit-state',[StateController::class,'edit'])->name('edit-state');
    Route::get('/edit-city',[CityController::class,'edit'])->name('edit-city');
    Route::get('/edit-material',[MaterialController::class,'edit'])->name('edit-material');
    Route::get('/edit-evaluation_as_per',[EvaluationAsPerController::class,'edit'])->name('edit-evaluation_as_per');
    Route::get('/edit-sensitivity',[SensitivityController::class,'edit'])->name('edit-sensitivity');
    Route::get('/edit-procedure_reference',[ProcedureReferenceController::class,'edit'])->name('edit-procedure_reference');
    Route::get('/edit-gst_configuration',[GstConfigurationController::class,'edit'])->name('edit-gst_configuration');
    Route::get('/edit-customer',[CustomerController::class,'edit'])->name('edit-customer');
    Route::get('/edit-supplier',[SupplierController::class,'edit'])->name('edit-supplier');
    Route::get('/edit-inquiry',[InquiryController::class,'edit'])->name('edit-inquiry');
    Route::get('/edit-type_of_job',[TypeOfJobController::class,'edit'])->name('edit-type_of_job');
    Route::get('/edit-unit',[UnitController::class,'edit'])->name('edit-unit');
    Route::get('/edit-shift',[ShiftController::class,'edit'])->name('edit-shift');
    Route::get('/edit-estimation_costing',[EstimationCostingController::class,'edit'])->name('edit-estimation_costing');
    Route::get('/edit-job_description',[JobDescriptionController::class,'edit'])->name('edit-job_description');
    Route::get('/edit-item',[ItemController::class,'edit'])->name('edit-item');
    Route::get('/edit-part',[PartController::class,'edit'])->name('edit-part');
    Route::get('/edit-item_group',[ItemGroupController::class,'edit'])->name('edit-item_group');
    Route::get('/edit-feasibility_review',[FeasibilityReviewController::class,'edit'])->name('edit-feasibility_review');
    Route::get('/edit-quotation',[QuotationController::class,'edit'])->name('edit-quotation');
    Route::get('/edit-reason',[ReasonController::class,'edit'])->name('edit-reason');
    Route::get('/edit-rt_camera',[RTCameraController::class,'edit'])->name('edit-rt_camera');
    Route::get('/edit-grn_supplier',[GRNSupplierController::class,'edit'])->name('edit-grn_supplier');
    Route::get('/edit-purchase_order',[PurchaseOrderController::class,'edit'])->name('edit-purchase_order');
    Route::get('/edit-equipment_ut',[EquipmentUTController::class,'edit'])->name('edit-equipment_ut');
    Route::get('/edit-equipment_mpt',[EquipmentMPTController::class,'edit'])->name('edit-equipment_mpt');
    Route::get('/edit-lpt_chemical',[LPTChemicalController::class,'edit'])->name('edit-lpt_chemical');
    Route::get('/edit-probe_ut',[ProbeUTController::class,'edit'])->name('edit-probe_ut');
    Route::get('/edit-material_mpt',[MaterialMptController::class,'edit'])->name('edit-material_mpt');
    Route::get('/edit-item_return_slip',[ItemReturnSlipController::class,'edit'])->name('edit-item_return_slip');
    Route::get('/edit-material_inward',[MaterialInwardController::class,'edit'])->name('edit-material_inward');
    Route::get('/edit-offer',[OfferController::class,'edit'])->name('edit-offer');
    Route::get('/edit-material_inspection',[MaterialInspectionController::class,'edit'])->name('edit-material_inspection');
    Route::get('/edit-order_acceptance',[OrderAcceptanceController::class,'edit'])->name('edit-order_acceptance');
    Route::get('/edit-non_returnable_material_challan',[NonRetMatChallanController::class,'edit'])->name('edit-non_returnable_material_challan');
    Route::get('/edit-planning_management',[PlanningManagementController::class,'edit'])->name('edit-planning_management');
    Route::get('/edit-po_mapping_process',[POMappingProcessController::class,'edit'])->name('edit-po_mapping_process');
    Route::get('/edit-location',[LocationController::class,'edit'])->name('edit-location');
    Route::get('/edit-nabl_configuration',[NABLConfigurationController::class,'edit'])->name('edit-nabl_configuration');
    Route::get('/edit-smtp_configuration',[SMTPConfigurationController::class,'edit'])->name('edit-smtp_configuration');
    Route::get('/edit-chemical_dpt',[DPTChemicalController::class,'edit'])->name('edit-chemical_dpt');
    Route::get('/edit-instrument',[InstrumentController::class,'edit'])->name('edit-instrument');
    Route::get('/edit-purchase_indent',[PurchaseIndentController::class,'edit'])->name('edit-purchase_indent');
    Route::get('/edit-assign_format_no',[AssignFormatNoController::class,'edit'])->name('edit-assign_format_no');
    Route::get('/edit-inter_location_transfer',[InterLocationTransferController::class,'edit'])->name('edit-inter_location_transfer');
    Route::get('/edit-service_po',[ServicePOController::class,'edit'])->name('edit-service_po');
    Route::get('/edit-item_issue',[ItemIssueController::class,'edit'])->name('edit-item_issue');
    Route::get('/edit-supplier_dc',[SupplierDCController::class,'edit'])->name('edit-supplier_dc');
    Route::get('/edit-delivery_challan_customer',[DeliveryChallanCustomerController::class,'edit'])->name('edit-delivery_challan_customer');
    Route::get('/edit-customer_dc_non_returnable',[CustomerDCNonReturnableController::class,'edit'])->name('edit-customer_dc_non_returnable');
    Route::get('/edit-item_return_customer ',[ItemReturnCustomerController::class,'edit'])->name('edit-item_return_customer ');
    Route::get('/edit-grn_location ',[GRNLocationController::class,'edit'])->name('edit-grn_location ');
    Route::get('/edit-iqi_designation', [IqiDesignationController::class, 'edit'])->name('edit-iqi_designation');
    Route::get('/edit-film', [FilmController::class, 'edit'])->name('edit-film');
    Route::get('/edit-iqi_sensitivity', [IqiSensitivityController::class, 'edit'])->name('edit-iqi_sensitivity');
    Route::get('/edit-area_of_coverage', [AreaOfCoverageController::class, 'edit'])->name('edit-area_of_coverage');
    Route::get('/edit-film_brand', [FilmBrandController::class, 'edit'])->name('edit-film_brand');
    Route::get('/edit-film_type', [FilmTypeController::class, 'edit'])->name('edit-film_type');
    Route::get('/edit-finding_level', [FindingLevelController::class, 'edit'])->name('edit-finding_level');
    Route::get('/edit-finding', [FindingController::class, 'edit'])->name('edit-finding');
    Route::get('/edit-authority_person', [AuthorityPersonController::class, 'edit'])->name('edit-authority_person');
    Route::get('/edit-enclosure', [EnclosureController::class, 'edit'])->name('edit-enclosure');
    Route::get('/edit-aerb_documents', [AerbDocumentController::class, 'edit'])->name('edit-aerb_documents');
    Route::get('/edit-film_result', [FilmResultController::class, 'edit'])->name('edit-film_result');
    Route::get('edit-production_entry',                      [ProductionEntryController::class, 'edit'])->name('edit-production_entry');
    Route::get('edit-technique_sheet_rt',                    [TechniqueSheetRtController::class, 'edit'])->name('edit-technique_sheet_rt');
    Route::get('edit-test_report_rt',                        [TestReportRtController::class, 'edit'])->name('edit-test_report_rt');
    Route::get('edit-measurement_sheet',                     [MeasurementSheetController::class, 'edit'])->name('edit-measurement_sheet');
    Route::get('edit-test_report_ut',                        [TestReportUtController::class, 'edit'])->name('edit-test_report_ut');
    Route::get('edit-test_report_dpt',                       [TestReportDptController::class, 'edit'])->name('edit-test_report_dpt');
    Route::get('edit-test_report_mpt',                       [TestReportMptController::class, 'edit'])->name('edit-test_report_mpt');

     Route::get('edit-observation_sheet', [ObservationSheetController::class, 'edit'])->name('edit-observation_sheet');
    


    /* Update Route */
    Route::post('/update-user',[AdminController::class,'update'])->name('update-user');
    Route::post('/update-acceptance_standard',[AcceptanceStandardController::class,'update'])->name('update-acceptance_standard');
    Route::post('/update-operator',[OperatorController::class,'update'])->name('update-operator');
    Route::post('/update-country',[CountryController::class,'update'])->name('update-country');
    Route::post('/update-state',[StateController::class,'update'])->name('update-state');
    Route::post('/update-city',[CityController::class,'update'])->name('update-city');
    Route::post('/update-material',[MaterialController::class,'update'])->name('update-material');
    Route::post('/update-evaluation_as_per',[EvaluationAsPerController::class,'update'])->name('update-evaluation_as_per');
    Route::post('/update-sensitivity',[SensitivityController::class,'update'])->name('update-sensitivity');
    Route::post('/update-procedure_reference',[ProcedureReferenceController::class,'update'])->name('update-procedure_reference');
    Route::post('/update-gst_configuration',[GstConfigurationController::class,'update'])->name('update-gst_configuration');
    Route::post('/update-customer',[CustomerController::class,'update'])->name('update-customer');
    Route::post('/update-supplier',[SupplierController::class,'update'])->name('update-supplier');
    Route::post('/update-inquiry',[InquiryController::class,'update'])->name('update-inquiry');
    Route::post('/update-type_of_job',[TypeOfJobController::class,'update'])->name('update-type_of_job');
    Route::post('/update-unit',[UnitController::class,'update'])->name('update-unit');
    Route::post('/update-shift',[ShiftController::class,'update'])->name('update-shift');
    Route::post('/update-estimation_costing',[EstimationCostingController::class,'update'])->name('update-estimation_costing');
    Route::post('/update-job_description',[JobDescriptionController::class,'update'])->name('update-job_description');
    Route::post('/update-item_group',[ItemGroupController::class,'update'])->name('update-item_group');
    Route::post('/update-item',[ItemController::class,'update'])->name('update-item');
    Route::post('/update-part',[PartController::class,'update'])->name('update-part');
    Route::post('/update-feasibility_review',[FeasibilityReviewController::class,'update'])->name('update-feasibility_review');
    Route::post('/update-quotation',[QuotationController::class,'update'])->name('update-quotation');
    Route::post('/update-reason',[ReasonController::class,'update'])->name('update-reason');
    Route::post('/update-rt_camera',[RTCameraController::class,'update'])->name('update-rt_camera');
    Route::post('/update-grn_supplier',[GRNSupplierController::class,'update'])->name('update-grn_supplier');
    Route::post('/update-purchase_order',[PurchaseOrderController::class,'update'])->name('update-purchase_order');
    Route::post('/update-equipment_ut',[EquipmentUTController::class,'update'])->name('update-equipment_ut');
    Route::post('/update-equipment_mpt',[EquipmentMPTController::class,'update'])->name('update-equipment_mpt');
    Route::post('/update-lpt_chemical',[LPTChemicalController::class,'update'])->name('update-lpt_chemical');
    Route::post('/update-probe_ut',[ProbeUTController::class,'update'])->name('update-probe_ut');
    Route::post('/update-material_mpt',[MaterialMptController::class,'update'])->name('update-material_mpt');
    Route::post('/update-item_return_slip',[ItemReturnSlipController::class,'update'])->name('update-item_return_slip');
    Route::post('/update-material_inward',[MaterialInwardController::class,'update'])->name('update-material_inward');
    Route::post('/update-offer',[OfferController::class,'update'])->name('update-offer');
    Route::post('/update-material_inspection',[MaterialInspectionController::class,'update'])->name('update-material_inspection');
    Route::post('/update-order_acceptance',[OrderAcceptanceController::class,'update'])->name('update-order_acceptance');
    Route::post('/update-non_returnable_material_challan',[NonRetMatChallanController::class,'update'])->name('update-non_returnable_material_challan');
    Route::post('/update-planning_management',[PlanningManagementController::class,'update'])->name('update-planning_management');
    Route::post('/update-po_mapping_process',[POMappingProcessController::class,'update'])->name('update-po_mapping_process');
    Route::post('/update-location',[LocationController::class,'update'])->name('update-location');
    Route::post('/update-nabl_configuration',[NABLConfigurationController::class,'update'])->name('update-nabl_configuration');
    Route::post('/update-smtp_configuration',[SMTPConfigurationController::class,'update'])->name('update-smtp_configuration');
    Route::post('/update-chemical_dpt',[DPTChemicalController::class,'update'])->name('update-chemical_dpt');
    Route::post('/update-instrument',[InstrumentController::class,'update'])->name('update-instrument');
    Route::post('/update-purchase_indent',[PurchaseIndentController::class,'update'])->name('update-purchase_indent');
    Route::post('/update-purchase_indent_short_close',[PurchaseIndentShortCloseController::class,'update'])->name('update-purchase_indent_short_close');
    Route::post('/update-assign_format_no',[AssignFormatNoController::class,'update'])->name('update-assign_format_no');
    Route::post('/update-inter_location_transfer',[InterLocationTransferController::class,'update'])->name('update-inter_location_transfer');
    Route::post('/update-service_po',[ServicePOController::class,'update'])->name('update-service_po');
    Route::post('/update-supplier_dc',[SupplierDCController::class,'update'])->name('update-supplier_dc');
    Route::post('/update-item_issue',[ItemIssueController::class,'update'])->name('update-item_issue');
    Route::post('/update-delivery_challan_customer',[DeliveryChallanCustomerController::class,'update'])->name('update-delivery_challan_customer');
    Route::post('/update-customer_dc_non_returnable',[CustomerDCNonReturnableController::class,'update'])->name('update-customer_dc_non_returnable');
    Route::post('/update-item_return_customer ',[ItemReturnCustomerController::class,'update'])->name('update-item_return_customer ');
    Route::post('/update-grn_location ',[GRNLocationController::class,'update'])->name('update-grn_location ');
    Route::post('/update-iqi_designation', [IqiDesignationController::class, 'update'])->name('update-iqi_designation');
    Route::post('/update-film', [FilmController::class, 'update'])->name('update-film');
    Route::post('/update-iqi_sensitivity', [IqiSensitivityController::class, 'update'])->name('update-iqi_sensitivity');
    Route::post('/update-area_of_coverage', [AreaOfCoverageController::class, 'update'])->name('update-area_of_coverage');
    Route::post('/update-film_brand', [FilmBrandController::class, 'update'])->name('update-film_brand');
    Route::post('/update-film_type', [FilmTypeController::class, 'update'])->name('update-film_type');
    Route::post('/update-finding_level', [FindingLevelController::class, 'update'])->name('update-finding_level');
    Route::post('/update-finding', [FindingController::class, 'update'])->name('update-finding');
    Route::post('/update-authority_person', [AuthorityPersonController::class, 'update'])->name('update-authority_person');
    Route::post('/update-enclosure', [EnclosureController::class, 'update'])->name('update-enclosure');
    Route::post('/update-aerb_documents', [AerbDocumentController::class, 'update'])->name('update-aerb_documents');
    Route::post('/update-film_result', [FilmResultController::class, 'update'])->name('update-film_result');
    Route::post('update-production_entry',                   [ProductionEntryController::class, 'update'])->name('update-production_entry');
    Route::post('update-technique_sheet_rt',                 [TechniqueSheetRtController::class, 'update'])->name('update-technique_sheet_rt');
    Route::post('update-test_report_rt',                     [TestReportRtController::class, 'update'])->name('update-test_report_rt');
    Route::post('update-measurement_sheet',                  [MeasurementSheetController::class, 'update'])->name('update-measurement_sheet');
    Route::post('update-test_report_ut',                     [TestReportUtController::class, 'update'])->name('update-test_report_ut');
    Route::post('update-test_report_dpt',                    [TestReportDptController::class, 'update'])->name('update-test_report_dpt');
    Route::post('update-test_report_mpt',                    [TestReportMptController::class, 'update'])->name('update-test_report_mpt');
     Route::post('update-observation_sheet', [ObservationSheetController::class, 'update'])->name('update-observation_sheet');
    
    

    /* Delete Route */
    Route::get('/delete-user',[AdminController::class,'destroy'])->name('delete-user');
    Route::get('/delete-company_year',[CompanyYearController::class,'destroy'])->name('delete-company_year');
    Route::get('/delete-acceptance_standard',[AcceptanceStandardController::class,'destroy'])->name('delete-acceptance_standard');
    Route::get('/delete-operator',[OperatorController::class,'destroy'])->name('delete-operator');
    Route::get('/delete-country',[CountryController::class,'destroy'])->name('delete-country');
    Route::get('/delete-state',[StateController::class,'destroy'])->name('remove-state');
    Route::get('/delete-city',[CityController::class,'destroy'])->name('remove-city');
    Route::get('/delete-material',[MaterialController::class,'destroy'])->name('remove-material');
    Route::get('/delete-evaluation_as_per',[EvaluationAsPerController::class,'destroy'])->name('remove-evaluation_as_per');
    Route::get('/delete-sensitivity',[SensitivityController::class,'destroy'])->name('remove-sensitivity');
    Route::get('/delete-procedure_reference',[ProcedureReferenceController::class,'destroy'])->name('remove-procedure_reference');
    Route::get('/delete-gst_configuration',[GstConfigurationController::class,'destroy'])->name('remove-gst_configuration');
    Route::get('/delete-customer',[CustomerController::class,'destroy'])->name('remove-customer');
    Route::get('/delete-supplier',[SupplierController::class,'destroy'])->name('remove-supplier');
    Route::get('/delete-inquiry',[InquiryController::class,'destroy'])->name('remove-inquiry');
    Route::get('/delete-type_of_job',[TypeOfJobController::class,'destroy'])->name('remove-type_of_job');
    Route::get('/delete-unit',[UnitController::class,'destroy'])->name('remove-unit');
    Route::get('/delete-shift',[ShiftController::class,'destroy'])->name('remove-shift');
    Route::get('/delete-estimation_costing',[EstimationCostingController::class,'destroy'])->name('remove-estimation_costing');
    Route::get('/delete-inquiry_short_close',[InquiryShortCloseController::class,'destroy'])->name('remove-inquiry_short_close');
    Route::get('/delete-job_description',[JobDescriptionController::class,'destroy'])->name('remove-job_description');
    Route::get('/delete-item',[ItemController::class,'destroy'])->name('remove-item');
    Route::get('/delete-part',[PartController::class,'destroy'])->name('remove-part');
    Route::get('/delete-item_group',[ItemGroupController::class,'destroy'])->name('remove-item_group');
    Route::get('/delete-feasibility_review',[FeasibilityReviewController::class,'destroy'])->name('remove-feasibility_review');
    Route::get('/delete-quotation',[QuotationController::class,'destroy'])->name('remove-quotation');
    Route::get('/delete-reason',[ReasonController::class,'destroy'])->name('remove-reason');
    Route::get('/delete-rt_camera',[RTCameraController::class,'destroy'])->name('remove-rt_camera');
    Route::get('/delete-grn_supplier',[GRNSupplierController::class,'destroy'])->name('remove-grn_supplier');
    Route::get('/delete-purchase_order',[PurchaseOrderController::class,'destroy'])->name('remove-purchase_order');
    Route::get('/delete-equipment_ut',[EquipmentUTController::class,'destroy'])->name('remove-equipment_ut');
    Route::get('/delete-equipment_mpt',[EquipmentMPTController::class,'destroy'])->name('remove-equipment_mpt');
    Route::get('/delete-lpt_chemical',[LPTChemicalController::class,'destroy'])->name('remove-lpt_chemical');
    Route::get('/delete-probe_ut',[ProbeUTController::class,'destroy'])->name('remove-probe_ut');
    Route::get('/delete-material_mpt',[MaterialMptController::class,'destroy'])->name('remove-material_mpt');
    Route::get('/delete-po_short_close',[POShortCloseController::class,'destroy'])->name('remove-po_short_close');
    Route::get('/delete-item_return_slip',[ItemReturnSlipController::class,'destroy'])->name('remove-item_return_slip');
    Route::get('/delete-material_inward',[MaterialInwardController::class,'destroy'])->name('remove-material_inward');
    Route::get('/delete-offer',[OfferController::class,'destroy'])->name('remove-offer');
    Route::get('/delete-material_inspection',[MaterialInspectionController::class,'destroy'])->name('remove-material_inspection');
    Route::get('/delete-order_acceptance',[OrderAcceptanceController::class,'destroy'])->name('remove-order_acceptance');
    Route::get('/delete-non_returnable_material_challan',[NonRetMatChallanController::class,'destroy'])->name('remove-non_returnable_material_challan');
    Route::get('/delete-planning_management',[PlanningManagementController::class,'destroy'])->name('remove-planning_management');
    Route::get('/delete-po_mapping_process',[POMappingProcessController::class,'destroy'])->name('remove-po_mapping_process');
    Route::get('/delete-location',[LocationController::class,'destroy'])->name('remove-location');
    Route::get('/delete-nabl_configuration',[NABLConfigurationController::class,'destroy'])->name('delete-nabl_configuration');
    Route::post('/destroy-smtp_configuration',[SMTPConfigurationController::class,'destroy'])->name('destroy-smtp_configuration');
    Route::get('/delete-chemical_dpt',[DPTChemicalController::class,'destroy'])->name('delete-chemical_dpt');
    Route::get('/delete-instrument',[InstrumentController::class,'destroy'])->name('delete-instrument');
    Route::get('/delete-purchase_indent',[PurchaseIndentController::class,'destroy'])->name('delete-purchase_indent');
    Route::get('/delete-purchase_indent_short_close',[PurchaseIndentShortCloseController::class,'destroy'])->name('delete-purchase_indent_short_close');
    Route::get('/delete-assign_format_no',[AssignFormatNoController::class,'destroy'])->name('delete-assign_format_no');
    Route::get('/delete-inter_location_transfer',[InterLocationTransferController::class,'destroy'])->name('delete-inter_location_transfer');
    Route::get('/delete-service_po',[ServicePOController::class,'destroy'])->name('delete-service_po');
    Route::get('/delete-service_po_short_close',[ServicePOShortCloseController::class,'destroy'])->name('delete-service_po_short_close');
    Route::get('/delete-supplier_dc',[SupplierDCController::class,'destroy'])->name('delete-supplier_dc');
    Route::post('/delete-customer_dc_non_returnable',[CustomerDCNonReturnableController::class,'destroy'])->name('delete-customer_dc_non_returnable');
    Route::get('/delete-item_issue',[ItemIssueController::class,'destroy'])->name('delete-item_issue');
    Route::get('/delete-delivery_challan_customer',[DeliveryChallanCustomerController::class,'destroy'])->name('delete-delivery_challan_customer');
    Route::get('/delete-item_return_customer ',[ItemReturnCustomerController::class,'destroy'])->name('delete-item_return_customer ');
    Route::get('/delete-grn_location',[GRNLocationController::class,'destroy'])->name('delete-grn_location');
    Route::get('/delete-iqi_designation', [IqiDesignationController::class, 'destroy'])->name('delete-iqi_designation');
    Route::get('/delete-film', [FilmController::class, 'destroy'])->name('delete-film');
    Route::get('/delete-iqi_sensitivity', [IqiSensitivityController::class, 'destroy'])->name('delete-iqi_sensitivity');Route::get('/delete-area_of_coverage', [AreaOfCoverageController::class, 'destroy'])->name('delete-area_of_coverage');
    Route::get('/delete-film_brand', [FilmBrandController::class, 'destroy'])->name('delete-film_brand');
    Route::get('/delete-film_type', [FilmTypeController::class, 'destroy'])->name('delete-film_type');
    Route::get('/delete-finding_level', [FindingLevelController::class, 'destroy'])->name('delete-finding_level');
    Route::get('/delete-finding', [FindingController::class, 'destroy'])->name('delete-finding');
    Route::get('/delete-authority_person', [AuthorityPersonController::class, 'destroy'])->name('delete-authority_person');
    Route::get('/delete-enclosure', [EnclosureController::class, 'destroy'])->name('delete-enclosure');
    Route::get('/delete-aerb_documents', [AerbDocumentController::class, 'destroy'])->name('delete-aerb_documents');
    Route::get('/delete-film_result', [FilmResultController::class, 'destroy'])->name('delete-film_result');
    Route::get('delete-production_entry',                    [ProductionEntryController::class, 'destroy'])->name('delete-production_entry');
    Route::get('delete-technique_sheet_rt',                  [TechniqueSheetRtController::class, 'destroy'])->name('delete-technique_sheet_rt');
    Route::get('delete-test_report_rt',                      [TestReportRtController::class, 'destroy'])->name('delete-test_report_rt');
    Route::get('delete-measurement_sheet',                   [MeasurementSheetController::class, 'destroy'])->name('delete-measurement_sheet');
    Route::get('delete-test_report_ut',                      [TestReportUtController::class, 'destroy'])->name('delete-test_report_ut');
    Route::get('delete-test_report_dpt',                     [TestReportDptController::class, 'destroy'])->name('delete-test_report_dpt');
    Route::get('delete-test_report_mpt',                     [TestReportMptController::class, 'destroy'])->name('delete-test_report_mpt');
     Route::get('delete-observation_sheet', [ObservationSheetController::class, 'destroy'])->name('delete-observation_sheet');
     Route::get('view-pdf-observation_sheet', [ObservationSheetController::class, 'viewPdf'])->name('view-pdf-observation_sheet');
    

    /* Suggestion Route */
    Route::get('/user_name-list',[AdminController::class,'existsUserName'])->name('user_name-list-list');
    Route::get('/admin_designation-list',[AdminController::class,'existsDesignation'])->name('admin_designation-list');
    Route::get('/acceptance_standard-list',[AcceptanceStandardController::class,'existsAcceptanceStandard'])->name('acceptance_standard-list');
    Route::get('/operator-list',[OperatorController::class,'existsOperator'])->name('operator-list');
    Route::get('/operator-designation-list',[OperatorController::class,'existsDesignation'])->name('operator-designation-list');
    Route::get('/country-list',[CountryController::class,'existsCountry'])->name('country-list');
    Route::get('/state-list',[StateController::class,'existsState'])->name('state-list');
    Route::get('/city-list',[CityController::class,'existsCity'])->name('city-list');
    Route::get('/material-list',[MaterialController::class,'existsMaterial'])->name('material-list');
    Route::get('/evaluation_as_per-list',[EvaluationAsPerController::class,'existsEvaluationAsPer'])->name('evaluation_as_per-list');
    Route::get('/sensitivity-list',[SensitivityController::class,'existsSensitivity'])->name('sensitivity-list');
    Route::get('/procedure_reference-list',[ProcedureReferenceController::class,'existsProcedureReference'])->name('procedure_reference-list');
    Route::get('/customer-list',[CustomerController::class,'existsCustomer'])->name('customer-list');
    Route::get('/payment_terms-list',[CustomerController::class,'existsPaymentTerms'])->name('payment_terms-list');
    Route::get('/designation-list',[CustomerController::class,'existsDesignation'])->name('designation-list');
    Route::get('/supplier_name-list',[SupplierController::class,'existsSupplier'])->name('supplier_name-list');
    Route::get('/type_of_job-list',[TypeOfJobController::class,'existsTypeofJob'])->name('type_of_job-list');
    Route::get('/unit-list',[UnitController::class,'existsUnit'])->name('unit-list');
    Route::get('/shift-list',[ShiftController::class,'existsShift'])->name('shift-list');
    Route::get('/job_description-list',[JobDescriptionController::class,'existsJobDescription'])->name('job_description-list');
    Route::get('/item_group-list',[ItemGroupController::class,'existsItemGroup'])->name('item_group-list');
    Route::get('/item-list',[ItemController::class,'existsItem'])->name('item-list');
    Route::get('/part-list',[PartController::class,'existsPart'])->name('part-list');
    Route::get('/reason-list',[ReasonController::class,'existsReason'])->name('reason-list');
    Route::get('/location_name-list',[LocationController::class,'existsLocationName'])->name('location_name-list');
    Route::get('/location_code-list',[LocationController::class,'existsLocationCode'])->name('location_code-list');
    Route::get('/make-list',[EquipmentUTController::class,'existsMake'])->name('make-list');
    Route::get('/display-list',[EquipmentUTController::class,'existsDisplay'])->name('display-list');
    Route::get('/calibration_standard-list',[EquipmentUTController::class,'existsCalibrationStandard'])->name('calibration_standard-list');
    Route::get('/calibration_technique-list',[EquipmentUTController::class,'existsCalibrationTechnique'])->name('calibration_technique-list');
    Route::get('/lpt_chemical_designation-list',[LPTChemicalController::class,'existsLPTChemicalDesignation'])->name('lpt_chemical_designation-list');
    Route::get('/lpt_make-list',[LPTChemicalController::class,'existsLPTChemicalMake'])->name('lpt_make-list');
    Route::get('/pu_size_of_probe_list',[ProbeUTController::class,'existsSizeOfProbe'])->name('pu_size_of_probe_list');
    Route::get('/material_mpt_make_list',[MaterialMptController::class,'existsMake'])->name('material_mpt_make_list');
    Route::get('/grn_transporter_list',[GRNSupplierController::class,'existsTrasnporter'])->name('grn_transporter_list');
    Route::get('/mi_transporter_list',[MaterialInwardController::class,'existsTrasnporter'])->name('mi_transporter_list');
    Route::get('/item_issue_to-list',[ItemIssueController::class,'existsIssueTo'])->name('item_issue_to-list');
    Route::get('/iqi_designation-list', [IqiDesignationController::class, 'existsIqiDesignation'])->name('iqi_designation-list');
    Route::get('/film-list', [FilmController::class, 'existsFilm'])->name('film-list');
    Route::get('/iqi_sensitivity-list', [IqiSensitivityController::class, 'existsIqiSensitivity'])->name('iqi_sensitivity-list');
    Route::get('/area_of_coverage-list', [AreaOfCoverageController::class, 'existsAreaOfCoverage'])->name('area_of_coverage-list');
    Route::get('/film_brand-list', [FilmBrandController::class, 'existsFilmBrand'])->name('film_brand-list');
    Route::get('/film_type-list', [FilmTypeController::class, 'existsFilmType'])->name('film_type-list');
    Route::get('/technique_sheet_rt_iqi-list', [TechniqueSheetRtController::class, 'existsIqi'])->name('technique_sheet_rt_iqi-list');
    Route::get('/technique_sheet_rt_film_processing-list', [TechniqueSheetRtController::class, 'existsFilmProcessing'])->name('technique_sheet_rt_film_processing-list');
    Route::get('/technique_sheet_rt_part_no-list', [TechniqueSheetRtController::class, 'existsPart'])->name('technique_sheet_rt_part_no-list');
    Route::get('/technique_sheet_rt_drg_no-list', [TechniqueSheetRtController::class, 'existsDrgNo'])->name('technique_sheet_rt_drg_no-list');
    Route::get('/technique_sheet_rt_test_technique-list', [TechniqueSheetRtController::class, 'existsTestTechnique'])->name('technique_sheet_rt_test_technique-list');
    Route::get('/finding_level-list', [FindingLevelController::class, 'existsFindingLevel'])->name('finding_level-list');
    Route::get('/finding-list', [FindingController::class, 'existsFinding'])->name('finding-list');
    Route::get('/authority_person-list', [AuthorityPersonController::class, 'existsAuthorityPerson'])->name('authority_person-list');
    Route::get('/enclosure_name-list', [EnclosureController::class, 'existsEnclosureName'])->name('enclosure_name-list');
    Route::get('/enclosure_no-list', [EnclosureController::class, 'existsEnclosureNo'])->name('enclosure_no-list');
    Route::get('/aerb_documents_name-list', [AerbDocumentController::class, 'existsAerbDocuments'])->name('aerb_documents_name-list');
    Route::get('/film_result_name-list', [FilmResultController::class, 'existsFilmResult'])->name('film_result_name-list');
      Route::get('/test_report_rt_customer_client-list',        [TestReportRtController::class, 'existsCustomerClient'])->name('test_report_rt_customer_client-list');
    Route::get('/test_report_rt_welding_process-list',        [TestReportRtController::class, 'existsWeldingProcess'])->name('test_report_rt_welding_process-list');
    Route::get('/test_report_rt_joint_type-list',             [TestReportRtController::class, 'existsJointType'])->name('test_report_rt_joint_type-list');
    Route::get('/test_report_rt_welder_name-list',           [TestReportRtController::class, 'existsWelderName'])->name('test_report_rt_welder_name-list');
    Route::get('/test_report_rt_welder_id-list',             [TestReportRtController::class, 'existsWelderId'])->name('test_report_rt_welder_id-list');
    Route::get('/test_report_rt_position-list',              [TestReportRtController::class, 'existsPosition'])->name('test_report_rt_position-list');
    Route::get('/test_report_rt_purpose_of_testing-list',    [TestReportRtController::class, 'existsPurposeOfTesting'])->name('test_report_rt_purpose_of_testing-list');
    Route::get('/test_report_rt_iqi-list',                    [TestReportRtController::class, 'existsIqi'])->name('test_report_rt_iqi-list');
    Route::get('/test_report_rt_film_processing-list',        [TestReportRtController::class, 'existsFilmProcessing'])->name('test_report_rt_film_processing-list');
    Route::get('/test_report_rt_test_technique-list',         [TestReportRtController::class, 'existsTestTechnique'])->name('test_report_rt_test_technique-list');
    Route::get('/test_report_rt_part_no-list',                [TestReportRtController::class, 'existsPartNo'])->name('test_report_rt_part_no-list');
    Route::get('/test_report_rt_drg_no-list',                 [TestReportRtController::class, 'existsDrgNo'])->name('test_report_rt_drg_no-list');
    Route::get('/test_report_rt_product_code-list',           [TestReportRtController::class, 'existsProductCode'])->name('test_report_rt_product_code-list');
    Route::get('/test_report_rt_finding-list',                [TestReportRtController::class, 'existsFinding'])->name('test_report_rt_finding-list');

    Route::get('/test_report_ut_customer_client-list',        [TestReportUtController::class, 'existsCustomerClient'])->name('test_report_ut_customer_client-list');
    Route::get('/test_report_ut_part_no-list',                [TestReportUtController::class, 'existsPartNo'])->name('test_report_ut_part_no-list');
    Route::get('/test_report_ut_drg_no-list',                 [TestReportUtController::class, 'existsDrgNo'])->name('test_report_ut_drg_no-list');
    Route::get('/test_report_ut_surface_condition-list',      [TestReportUtController::class, 'existsSurfaceCondition'])->name('test_report_ut_surface_condition-list');
    Route::get('/test_report_ut_couplant-list',               [TestReportUtController::class, 'existsCouplant'])->name('test_report_ut_couplant-list');
    Route::get('/test_report_ut_test_technique-list',         [TestReportUtController::class, 'existsTestTechnique'])->name('test_report_ut_test_technique-list');
    Route::get('/test_report_ut_ref_block_used-list',         [TestReportUtController::class, 'existsRefBlockUsed'])->name('test_report_ut_ref_block_used-list');
    Route::get('/test_report_ut_scanning_area-list',          [TestReportUtController::class, 'existsScanningArea'])->name('test_report_ut_scanning_area-list');
    Route::get('/test_report_ut_scan_plan_no-list',           [TestReportUtController::class, 'existsScanPlanNo'])->name('test_report_ut_scan_plan_no-list');
    Route::get('/test_report_ut_test_carried_out_at-list',    [TestReportUtController::class, 'existsTestCarriedOutAt'])->name('test_report_ut_test_carried_out_at-list');
    Route::get('/test_report_ut_surface_temp-list',           [TestReportUtController::class, 'existsSurfaceTemp'])->name('test_report_ut_surface_temp-list');
    Route::get('/test_report_ut_thickness-list',              [TestReportUtController::class, 'existsThickness'])->name('test_report_ut_thickness-list');
    Route::get('/test_report_ut_product_code-list',           [TestReportUtController::class, 'existsProductCode'])->name('test_report_ut_product_code-list');

    Route::get('/test_report_dpt_customer_client-list',        [TestReportDptController::class, 'existsCustomerClient'])->name('test_report_dpt_customer_client-list');
    Route::get('/test_report_dpt_part_no-list',                [TestReportDptController::class, 'existsPartNo'])->name('test_report_dpt_part_no-list');
    Route::get('/test_report_dpt_drg_no-list',                 [TestReportDptController::class, 'existsDrgNo'])->name('test_report_dpt_drg_no-list');
    Route::get('/test_report_dpt_surface_condition-list',      [TestReportDptController::class, 'existsSurfaceCondition'])->name('test_report_dpt_surface_condition-list');
    Route::get('/test_report_dpt_test_technique-list',         [TestReportDptController::class, 'existsTestTechnique'])->name('test_report_dpt_test_technique-list');
    Route::get('/test_report_dpt_test_carried_out_at-list',    [TestReportDptController::class, 'existsTestCarriedOutAt'])->name('test_report_dpt_test_carried_out_at-list');
    Route::get('/test_report_dpt_surface_temp-list',           [TestReportDptController::class, 'existsSurfaceTemp'])->name('test_report_dpt_surface_temp-list');
    Route::get('/test_report_dpt_thickness-list',              [TestReportDptController::class, 'existsThickness'])->name('test_report_dpt_thickness-list');
    Route::get('/test_report_dpt_product_code-list',           [TestReportDptController::class, 'existsProductCode'])->name('test_report_dpt_product_code-list');
    Route::get('/test_report_dpt_pre_cleaning-list',           [TestReportDptController::class, 'existsPreCleaning'])->name('test_report_dpt_pre_cleaning-list');
    Route::get('/test_report_dpt_penetrant_application-list',  [TestReportDptController::class, 'existsPenetrantApplication'])->name('test_report_dpt_penetrant_application-list');
    Route::get('/test_report_dpt_dwell_time-list',              [TestReportDptController::class, 'existsDwellTime'])->name('test_report_dpt_dwell_time-list');
    Route::get('/test_report_dpt_penetrant_remover-list',      [TestReportDptController::class, 'existsPenetrantRemover'])->name('test_report_dpt_penetrant_remover-list');
    Route::get('/test_report_dpt_developer-list',              [TestReportDptController::class, 'existsDeveloper'])->name('test_report_dpt_developer-list');
    Route::get('/test_report_dpt_developer_application-list',  [TestReportDptController::class, 'existsDeveloperApplication'])->name('test_report_dpt_developer_application-list');
    Route::get('/test_report_dpt_lighting-list',              [TestReportDptController::class, 'existsLighting'])->name('test_report_dpt_lighting-list');
    Route::get('/test_report_dpt_background_light-list',       [TestReportDptController::class, 'existsBackgroundLight'])->name('test_report_dpt_background_light-list');


    /* Duplication Verification Route */
    Route::controller(DuplicationVerificationController::class)->group(function(){
      Route::get('/verify-user_name', 'verifyUserName')->name('verify-user_name');
      Route::get('/verify-acceptance_standard', 'verifyAcceptanceStandard')->name('verify-acceptance_standard');
      Route::get('/verify-operator', 'verifyOperator')->name('verify-operator');
      Route::get('/verify-country', 'verifyCountry')->name('verify-country');
      Route::get('/verify-state-data', 'verifyState')->name('verify-state-data');
      Route::get('/verify-state_code', 'verifyStateCode')->name('verify-state_code');
      Route::get('/verify-city-data', 'verifyCityData')->name('verify-city-data');
      Route::get('/verify-material', 'verifyMaterial')->name('verify-material');
      Route::get('/verify-evaluation_as_per', 'verifyEvaluation')->name('verify-evaluation_as_per');
      Route::get('/verify-sensitivity', 'verifySensitivity')->name('verify-sensitivity');
      Route::get('/verify-procedure_reference', 'verifyProcedureReference')->name('verify-procedure_reference');
      Route::get('/verify-sac', 'verifySAC')->name('verify-sac');
      Route::get('/verify-customer', 'verifyCustomer')->name('verify-customer');
      Route::get('/verify-customer_code', 'verifyCustomerCode')->name('verify-customer_code');
      Route::get('/verify-supplier', 'verifySupplier')->name('verify-supplier');
      Route::get('/verify-type_of_job', 'verifyTypeofJob')->name('verify-type_of_job');
      Route::get('/verify-unit', 'verifyUnit')->name('verify-unit');
      Route::get('/verify-shift', 'verifyShift')->name('verify-shift');
      Route::get('/verify-job_description', 'verifyJobDescription')->name('verify-job_description');
      Route::get('/verify-item_group', 'verifyItemGroup')->name('verify-item_group');
      Route::get('/verify-item', 'verifyItem')->name('verify-item');
      Route::get('/verify-item_code', 'verifyItemCode')->name('verify-item_code');
      Route::get('/verify-part', 'verifyPart')->name('verify-part');
      Route::get('/verify-reason', 'verifyReason')->name('verify-reason');
      Route::get('/verify-location_name', 'verifyLocationName')->name('verify-location_name');
      Route::get('/verify-nabl_location', 'verifyNABLLocation')->name('verify-nabl_location');
      Route::get('/verify-rt_camera', 'verifyRTCamera')->name('verify-rt_camera');
      Route::get('/verify-probe_ut', 'verifyProbeUT')->name('verify-probe_ut');
      Route::get('/verify-equipment_mpt', 'verifyEquipmentMPT')->name('verify-equipment_mpt');
      Route::get('/verify-material_mpt', 'verifyMaterialMPT')->name('verify-material_mpt');
      Route::get('/verify-chemical_dpt', 'verifyDPTChemical')->name('verify-chemical_dpt');
      Route::get('/verify-equipment_ut', 'verifyEquipmentUT')->name('verify-equipment_ut');
      Route::get('/verify-instrument', 'verifyInstrument')->name('verify-instrument');
      Route::get('/verify-assign_format_no', 'verifyAssignFormatNo')->name('verify-assign_format_no');
      Route::get('/verify-iqi_designation',  'verifyIqiDesignation')->name('verify-iqi_designation');
      Route::get('/verify-film', 'verifyFilm')->name('verify-film');
      Route::get('/verify-iqi_sensitivity', 'verifyIqiSensitivity')->name('verify-iqi_sensitivity');
      Route::get('/verify-area_of_coverage', 'verifyAreaOfCoverage')->name('verify-area_of_coverage');
      Route::get('/verify-film_brand', 'verifyFilmBrand')->name('verify-film_brand');
      Route::get('/verify-film_type', 'verifyFilmType')->name('verify-film_type');
      Route::get('/verify-finding_level', 'verifyFindingLevel')->name('verify-finding_level');
      Route::get('/verify-finding', 'verifyFinding')->name('verify-finding');
      Route::get('/verify-authority_person', 'verifyAuthorityPerson')->name('verify-authority_person');
      Route::get('/verify-enclosure', 'verifyEnclosure')->name('verify-enclosure');
      Route::get('/verify-aerb_documents', 'verifyAerbDocuments')->name('verify-aerb_documents');
      Route::get('/verify-film_result', 'verifyFilmResult')->name('verify-film_result');
      


      Route::get('/check-inq_number_duplication', 'checkInquirySequnceDuplication')->name('check-inq_number_duplication');
      Route::get('/check-quotation_number_duplication', 'checkQuotationSequnceDuplication')->name('check-quotation_number_duplication');
      Route::get('/check-grn_supplier_number_duplication', 'checkGrnSupplierSequnceDuplication')->name('check-grn_supplier_number_duplication');
      Route::get('/check-purchase_order_number_duplication', 'checkPurchaseOrderSequnceDuplication')->name('check-purchase_order_number_duplication');
      Route::get('/check-irs_number_duplication', 'checkIrsSequnceDuplication')->name('check-irs_number_duplication');
      Route::get('/check-material_inward_number_duplication', 'checkMaterialInwardSequnceDuplication')->name('check-material_inward_number_duplication');
      Route::get('/check-offer_number_duplication', 'checkOfferSequnceDuplication')->name('check-offer_number_duplication');
      Route::get('/check-mins_number_duplication', 'checkMaterialInspectionSequnceDuplication')->name('check-mins_number_duplication');
      Route::get('/check-oa_number_duplication', 'checkOASequnceDuplication')->name('check-oa_number_duplication');
      Route::get('/check-nrmc_number_duplication', 'checkNRMCDCSequnceDuplication')->name('check-nrmc_number_duplication');
      Route::get('/check-pm_number_duplication', 'checkPMSequnceDuplication')->name('check-pm_number_duplication');
      Route::get('/check-purchase_indent_number_duplication', 'checkPISequnceDuplication')->name('check-purchase_indent_number_duplication');
      Route::get('/check-inter_location_transfer_number_duplication', 'checkInterLocationTransferSequnceDuplication')->name('check-inter_location_transfer_number_duplication');
      Route::get('/check-service_po_number_duplication', 'checkServicePOSequnceDuplication')->name('check-service_po_number_duplication');
      Route::get('/check-item_issue_number_duplication', 'checkItemIssueSequnceDuplication')->name('check-item_issue_number_duplication');
      Route::get('/check-grn_location_number_duplication', 'checkGRNLocationSequnceDuplication')->name('check-grn_location_number_duplication');
      Route::get('/check-delivery_challan_customer_number_duplication', 'checkDeliveryChallanCustomerSequnceDuplication')->name('check-delivery_challan_customer_number_duplication');
      Route::get('/check-item_return_customer_number_duplication', 'checkItemReturnCustomerSequnceDuplication')->name('check-item_return_customer_number_duplication');
      Route::get('/check-supplier_dc_number_duplication ', 'checkSupplierDCSequnceDuplication')->name('check-supplier_dc_number_duplication');
      Route::get('check-production_entry_number_duplication', 'checkProductionNumberDuplication')->name('check-production_entry_number_duplication');
      Route::get('check-technique_sheet_number_duplication',  'checkTechniqueSheetNumberDuplication')->name('check-technique_sheet_number_duplication');
      Route::get('check-test_report_rt_number_duplication',   'checkTestReportRtNumberDuplication')->name('check-test_report_rt_number_duplication');
      Route::get('check-test_report_ut_number_duplication',   'checkTestReportUTNumberDuplication')->name('check-test_report_ut_number_duplication');
      Route::get('check-test_report_dpt_number_duplication',   'checkTestReportDPTNumberDuplication')->name('check-test_report_dpt_number_duplication');
      Route::get('check-test_report_mpt_number_duplication',   'checkTestReportMPTNumberDuplication')->name('check-test_report_mpt_number_duplication');
      Route::get('check-measurement_sheet_number_duplication',   'checkMeasurementSheetNumberDuplication')->name('check-measurement_sheet_number_duplication');
      Route::get('check-customer_dc_non_returnable_number_duplication', 'checkCustomerDCNonReturnableSequenceDuplication')->name('check-customer_dc_non_returnable_number_duplication');
      Route::get('check-observation_sheet_number_duplication', 'checkObservationSheetSequenceDuplication')->name('check-observation_sheet_number_duplication');
    });

    /**
    * Pending Route
     */
    Route::get('/get-inquiry_list_for_feasibility_review',[FeasibilityReviewController::class,'getInquiryListForFeasibility'])->name('get-inquiry_list_for_feasibility_review');
    Route::get('/get-inquiry_part_data_feasibility_review',[FeasibilityReviewController::class,'getInquiryPartDataForFeasibility'])->name('get-inquiry_part_data_feasibility_review');
    
    Route::get('/get-pending_inquiry_list_for_estimation_costing',[EstimationCostingController::class,'getPendingInquiryListForEstimationCosting'])->name('get-pending_inquiry_list_for_estimation_costing');
    Route::get('/get-inquiry_part_data_estimation_costing',[EstimationCostingController::class,'getInquiryPartDataForEstimationCosting'])->name('get-inquiry_part_data_estimation_costing');
    
    Route::get('/get-pending_customer_for_quotation',[QuotationController::class,'getPendingQuotCustomers'])->name('get-pending_customer_for_quotation');
    Route::get('/get-pending_inquiry_estimation_for_quotation',[QuotationController::class,'getPendingInqEstimationList'])->name('get-pending_inquiry_estimation_for_quotation');
    Route::get('/get-pending_inquiry_for_quotation',[QuotationController::class,'getPendingInqEstimationData'])->name('get-pending_inquiry_for_quotation');
    Route::get('/get-pending_inq_sc',[InquiryShortCloseController::class,'getPendingInqSC'])->name('get-pending_inq_sc');

    Route::get('/get-pending_supplier_for_grn',[GRNSupplierController::class,'getPendingSupplierForGRN'])->name('get-pending_supplier_for_grn');
    Route::get('/get-pending_po_list_for_grn',[GRNSupplierController::class,'getPendingPoListForGrn'])->name('get-pending_po_list_for_grn');
    Route::get('/get-pending_po_for_grn',[GRNSupplierController::class,'getPendingPoForGrn'])->name('get-pending_po_for_grn');
    Route::get('/get-pending_dc_list_for_grn',[GRNSupplierController::class,'getSupplierDCListForGrn'])->name('get-pending_dc_list_for_grn');
    Route::get('/get-pending_dc_for_grn',[GRNSupplierController::class,'getPendingSupplierDCForGrn'])->name('get-pending_dc_for_grn');

    Route::get('/get-pending_grn_list_for_equipment_ut',[EquipmentUTController::class,'getPendingGrnListForEquipmentUt'])->name('get-pending_grn_list_for_equipment_ut');
    Route::get('/get-pending_grn_for_equipment_ut',[EquipmentUTController::class,'getPendingGrnForEquipmentUt'])->name('get-pending_grn_for_equipment_ut');

    Route::get('/get-pending_grn_list_for_equipment_mpt',[EquipmentMPTController::class,'getPendingGrnListForEquipmentMPT'])->name('get-pending_grn_list_for_equipment_mpt');
    Route::get('/get-pending_grn_for_equipment_mpt',[EquipmentMPTController::class,'getPendingGrnForEquipmentMPT'])->name('get-pending_grn_for_equipment_mpt');

    Route::get('/get-grn_list_for_rt_camera',[RTCameraController::class,'getGRNListForRTCamera'])->name('get-grn_list_for_rt_camera');
    Route::get('/get-grn_part_data_rt_camera',[RTCameraController::class,'getGRNPartDataForRTCamera'])->name('get-grn_part_data_rt_camera');

    Route::get('/get-grn_list_for_lpt_chemical',[LPTChemicalController::class,'getGRNListForLPTChemical'])->name('get-grn_list_for_lpt_chemical');
    Route::get('/get-grn_part_data_lpt_chemical',[LPTChemicalController::class,'getGRNPartDataForLPTChemical'])->name('get-grn_part_data_lpt_chemical');

    Route::get('/get-pending_grn_list_for_probe_ut',[ProbeUTController::class,'getPendingGrnListForProbeUt'])->name('get-pending_grn_list_for_probe_ut');
    Route::get('/get-pending_grn_for_probe_ut',[ProbeUTController::class,'getPendingGrnForProbeUt'])->name('get-pending_grn_for_probe_ut');
    Route::get('/get-pending_grn_list_for_material_mpt',[MaterialMptController::class,'getPendingGrnListForMaterialMpt'])->name('get-pending_grn_list_for_material_mpt');
    Route::get('/get-pending_grn_for_material_mpt',[MaterialMptController::class,'getPendingGrnForMaterialMpt'])->name('get-pending_grn_for_material_mpt');

    Route::get('/get-pending_grn_list_for_chemical_dpt',[DPTChemicalController::class,'getPendingGrnListForChemicalDPT'])->name('get-pending_grn_list_for_chemical_dpt');
    Route::get('/get-pending_grn_for_chemical_dpt',[DPTChemicalController::class,'getPendingGrnForChemicalDPT'])->name('get-pending_grn_for_chemical_dpt');

    Route::get('/get-pending_grn_list_for_instrument',[InstrumentController::class,'getPendingGrnListForInstrument'])->name('get-pending_grn_list_for_instrument');
    Route::get('/get-pending_grn_for_instrument',[InstrumentController::class,'getPendingGrnForInstrument'])->name('get-pending_grn_for_instrument');
    
    Route::get('/get-pending_po_sc',[POShortCloseController::class,'getPendingPoSC'])->name('get-pending_po_sc');
    Route::get('/get-pending_purchase_indent_sc',[PurchaseIndentShortCloseController::class,'getPendingPurchaseIndentSC'])->name('get-pending_purchase_indent_sc');

    Route::get('/get-pending_employee_for_irs',[ItemReturnSlipController::class,'getPendingSupplierForIRS'])->name('get-pending_employee_for_irs');

    Route::get('/get-material_inward_list_for_material_inspection',[MaterialInspectionController::class,'getMaterialInwardListForMaterialInspection'])->name('get-material_inward_list_for_material_inspection');
    Route::get('/get-material_inward_part_data_material_inspection',[MaterialInspectionController::class,'getMaterialInwardPartDataForMaterialInspection'])->name('get-material_inward_part_data_material_inspection');

    Route::get('/get-pending_customer_for_oa',[OrderAcceptanceController::class,'getPendingCustomerForOA'])->name('get-pending_customer_for_oa');
    Route::get('/get-pending_quot_list_for_oa',[OrderAcceptanceController::class,'getPendingQuotListForOA'])->name('get-pending_quot_list_for_oa');
    Route::get('/get-pending_quotation_for_oa',[OrderAcceptanceController::class,'getPendingQuotationForOA'])->name('get-pending_quotation_for_oa');

    Route::get('/get-pending_customer_for_dc',[NonRetMatChallanController::class,'getPendingCustomerForDC'])->name('get-pending_customer_for_dc');
    Route::get('/get-pending_inward_list_for_dc',[NonRetMatChallanController::class,'getPendingInwardListForDC'])->name('get-pending_inward_list_for_dc');
    Route::get('/get-pending_inwad_for_dc',[NonRetMatChallanController::class,'getPendingInwardForDC'])->name('get-pending_inwad_for_dc');

    Route::get('/get-pending_oa_list_for_pm',[PlanningManagementController::class,'getPendingOAListForPM'])->name('get-pending_oa_list_for_pm');
    Route::get('/get-pending_oa_for_pm',[PlanningManagementController::class,'getPendingOAForPM'])->name('get-pending_oa_for_pm');

    Route::get('/get-material_insp_list_for_pomp',[POMappingProcessController::class,'getMaterialInspListForPOMP'])->name('get-material_insp_list_for_pomp');
    Route::get('/get-material_insp_part_data_poms',[POMappingProcessController::class,'getMaterialInspForPOMP'])->name('get-material_insp_part_data_poms');

    Route::get('/get-purchase_indent_list_for_purchase_order',[PurchaseOrderController::class,'getPurchaseIndentListForPurchaseOrder'])->name('get-purchase_indent_list_for_purchase_order');
    Route::get('/get-purchase_indent_part_data_purchase_order',[PurchaseOrderController::class,'getPurchaseIndentPartDataForPurchaseOrder'])->name('get-purchase_indent_part_data_purchase_order');

   
    Route::get('/get-pi_list_for_inter_location_transfer',[InterLocationTransferController::class,'getPendingPIListForILT'])->name('get-pi_list_for_inter_location_transfer');
    Route::get('/get-pi_part_data_inter_location_transfer',[InterLocationTransferController::class,'getPendingPIPartDataForILT'])->name('get-pi_part_data_inter_location_transfer');    
    
    Route::get('/get-pending_location_transfer_list_for_grn_location',[GRNLocationController::class,'getPendingLocationTransferDataForGRNLocation'])->name('get-pending_location_transfer_list_for_grn_location');
    Route::get('/get-inter_location_transfer_part_data_grn_location',[GRNLocationController::class,'getInterLocationTransferPartDataForGRNLocation'])->name('get-inter_location_transfer_part_data_grn_location');

    Route::get('/get-pending_service_po_sc',[ServicePOShortCloseController::class,'getPendingServicePOSC'])->name('get-pending_service_po_sc');

    Route::get('/get-pending_supplier_for_supplier_dc',[SupplierDCController::class,'getPendingSupplierForSupplierDC'])->name('get-pending_supplier_for_supplier_dc');
    Route::get('/get-pending_service_po_list_for_supplier_dc',[SupplierDCController::class,'getPendingServicePOListForSupplierDC'])->name('get-pending_service_po_list_for_supplier_dc');
    Route::get('/get-pending_service_po_for_supplier_dc',[SupplierDCController::class,'getPendingServicePOForSupplierDC'])->name('get-pending_service_po_for_supplier_dc');

     Route::get('/get-pending_customer_for_item_return_customer',[ItemReturnCustomerController::class,'getPendingCustomerForItemReturn'])->name('get-pending_customer_for_item_return_customer');
     Route::get('/get-pending_dc_customer_list_for_item_return_customer',[ItemReturnCustomerController::class,'getPendingDCCustomerListForItemReturn'])->name('get-pending_dc_customer_list_for_item_return_customer');

     Route::get('/get-pending_dc_for_item_return_customer',[ItemReturnCustomerController::class,'getPendingDCCustomerDataForItemReturn'])->name('gget-pending_dc_for_item_return_customer');


    Route::get('get-technique-sheets-for-customer',[TestReportRtController::class, 'getTechniqueSheetsForCustomer'])->name('get-technique-sheets-for-customer');
    Route::get('get-technique-sheet-details',[TestReportRtController::class, 'getTechniqueSheetDetails'])->name('get-technique-sheet-details');
    Route::get('/get-pending-customers-customer_dc_non_returnable', [CustomerDCNonReturnableController::class, 'getPendingCustomers'])->name('get-pending-customers-customer_dc_non_returnable');
    Route::get('/get-pending-customer_dc_non_returnable', [CustomerDCNonReturnableController::class, 'getPendingInwardList'])->name('get-pending-customer_dc_non_returnable');
     /* Test Report RT Pending For Technique Sheet RT */
     Route::get('get-customer_for_rt_report',[TechniqueSheetRtController::class,'getPendingCustomers'])->name('get-customer_for_rt_report');
     Route::get('get-customer_pending_test_report_list',[TechniqueSheetRtController::class,'pendingRtReportPendingList'])->name('get-customer_pending_test_report_list');
     Route::get('get-pending_test_report_rt_data',[TechniqueSheetRtController::class,'getpendingRtReportData'])->name('get-pending_test_report_rt_data');

    /**
    * Latest Number Route
     */
    Route::get('/get-latest_inquiry_number',[InquiryController::class,'getLatestInquiryNumber'])->name('get-latest_inquiry_number');
    Route::get('/get-inquiry_part_no_list',[InquiryController::class,'getInquiryPartNoList'])->name('get-inquiry_part_no_list');
    Route::get('/get-quotation_part_no_list',[QuotationController::class,'getQuotationPartNoList'])->name('get-quotation_part_no_list');
    Route::get('/get-latest_quotation_number',[QuotationController::class,'getLatestQuotationNumber'])->name('get-latest_quotation_number');
    Route::get('/get-latest_feasibility_number',[FeasibilityReviewController::class,'getLatestFeasibilityNumber'])->name('get-latest_feasibility_number');
    Route::get('/get-latest_estimation_costing_number',[EstimationCostingController::class,'getLatestEstimationCostingNumber'])->name('get-latest_estimation_costing_number');
    Route::get('/get-latest_grn_supplier_number',[GRNSupplierController::class,'getLatestGrnSupplierNumber'])->name('get-latest_grn_supplier_number');
    Route::get('/get-latest_purchase_order_number',[PurchaseOrderController::class,'getLatestPurchaseOrderNumber'])->name('get-latest_purchase_order_number');
    Route::get('/get-latest_item_return_slip_number',[ItemReturnSlipController::class,'getLatestItemReturnSlipNumber'])->name('get-latest_item_return_slip_number');
    Route::get('/get-latest_material_inward_number',[MaterialInwardController::class,'getLatestInwardNumber'])->name('get-latest_material_inward_number');
    Route::get('/get-latest_material_inspection_number',[MaterialInspectionController::class,'getLatestMaterialInspectionNumber'])->name('get-latest_material_inspection_number');
    Route::get('/get-latest_oa_number',[OrderAcceptanceController::class,'getLatestOANumber'])->name('get-latest_oa_number');
    Route::get('/get-latest_nrmc_number',[NonRetMatChallanController::class,'getLatestNRMCDCNumber'])->name('get-latest_nrmc_number');
    Route::get('/get-latest_pm_number',[PlanningManagementController::class,'getLatestPMNumber'])->name('get-latest_pm_number');
    Route::get('/get-latest_po_mapping_process_number',[POMappingProcessController::class,'getLatestPOMappingProcessNumber'])->name('get-latest_po_mapping_process_number');
    Route::get('/get-latest_purchase_indent_number',[PurchaseIndentController::class,'getLatestPurchaseIndentNumber'])->name('get-latest_purchase_indent_number');
    Route::get('/get-latest_inter_location_transfer_number',[InterLocationTransferController::class,'getLatestInterLocationTransferNumber'])->name('get-latest_inter_location_transfer_number');
    Route::get('/get-latest_service_po_number',[ServicePOController::class,'getLatestServicePONumber'])->name('get-latest_service_po_number');
    Route::get('/get-latest_item_issue_number',[ItemIssueController::class,'getLatestItemIssueNumber'])->name('get-latest_item_issue_number');
    Route::get('/get-latest_grn_location_number',[GRNLocationController::class,'getLatestGRNLocationNumber'])->name('get-latest_grn_location_number');
    Route::get('/get-latest_supplier_dc_number',[SupplierDCController::class,'getLatestSupplierDCNumber'])->name('get-latest_supplier_dc_number');
    Route::get('/get-latest_delivery_challan_customer_number',[DeliveryChallanCustomerController::class,'getLatestCustomerDCNumber'])->name('get-latest_delivery_challan_customer_number');
    Route::get('/get-latest-dc-sequence-customer_dc_non_returnable', [CustomerDCNonReturnableController::class, 'getLatestDCSequence'])->name('get-latest-dc-sequence-customer_dc_non_returnable');
    Route::get('/get-latest_item_return_customer_number',[ItemReturnCustomerController::class,'getLatestItemReturnCustomerNumber'])->name('get-latest_item_return_customer_number');
    Route::get('/get-latest_material_inward_number', [MaterialInwardController::class, 'getLatestMaterialInwardNumber']);

    Route::get('get-latest-production_entry_number',         [ProductionEntryController::class, 'getLatestProductionEntryNumber'])->name('get-latest-production_entry_number');
    Route::get('get-latest-technique_sheet_number',          [TechniqueSheetRtController::class, 'getLatestTechniqueSheetNumber'])->name('get-latest-technique_sheet_number');
    Route::get('get-technique-sheet-copy-list',              [TechniqueSheetRtController::class, 'getCopyList'])->name('get-technique-sheet-copy-list');
    Route::get('get-latest-test_report_rt_number',           [TestReportRtController::class, 'getLatestTestReportRtNumber'])->name('get-latest-test_report_rt_number');
    Route::get('get-pending-customer-rt-data',               [TestReportRtController::class, 'getPendingCustomerData'])->name('get-pending-customer-rt-data');
    Route::get('get-pending-rt-reports-for-measurement',     [MeasurementSheetController::class, 'getPendingRtReports'])->name('get-pending-rt-reports-for-measurement');
    Route::get('get-pending-customers-for-measurement',     [MeasurementSheetController::class, 'getPendingCustomers'])->name('get-pending-customers-for-measurement');
    Route::get('get-latest-measurement_sheet-number',        [MeasurementSheetController::class, 'getLatestMeasurementSheetNumber'])->name('get-latest-measurement_sheet-number');
    Route::get('get-latest-observation_sheet-number', [ObservationSheetController::class, 'getLatestObservationSheetNumber'])->name('get-latest-observation_sheet-number');
    Route::get('get-pending-customers-for-observation_sheet', [ObservationSheetController::class, 'getPendingCustomers'])->name('get-pending-customers-for-observation_sheet');
    Route::get('get-pending-inward-for-observation_sheet', [ObservationSheetController::class, 'getPendingInwardData'])->name('get-pending-inward-for-observation_sheet');
    Route::get('get-pending-technique-sheet-for-observation_sheet', [ObservationSheetController::class, 'getPendingTechniqueSheetList'])->name('get-pending-technique-sheet-for-observation_sheet');
    Route::get('get-old-rt-reports-list-for-observation-sheet', [ObservationSheetController::class, 'getOldRtReportsList'])->name('get-old-rt-reports-list-for-observation-sheet');
    Route::get('get-old-rt-report-details-for-repair', [ObservationSheetController::class, 'getOldRtReportDetailsForRepair'])->name('get-old-rt-report-details-for-repair');
    Route::get('get-pending-customers-for-rt',               [TestReportRtController::class, 'getPendingCustomersForRt'])->name('get-pending-customers-for-rt');
    Route::get('get-latest_url_no',                          [TestReportRtController::class, 'getLatestULRNo'])->name('get-latest_url_no');
    Route::get('check-ulr_no',                               [TestReportRtController::class, 'checkUlrNo'])->name('check-ulr_no');
    Route::get('get-test_report_rt_copy_list',               [TestReportRtController::class, 'getCopyReportsList'])->name('get-test_report_rt_copy_list');
    Route::get('get-test_report_rt_details_for_copy',        [TestReportRtController::class, 'getReportDetailsForCopy'])->name('get-test_report_rt_details_for_copy');
    Route::get('get-pending-report-revision-data',           [TestReportRtController::class, 'getPendingReportRevisionData'])->name('get-pending-report-revision-data');
    Route::get('get-observation-sub-details',                [TestReportRtController::class, 'getObservationSubDetails'])->name('get-observation-sub-details');
    Route::get('get-rt-cameras-dropdown',                    [TestReportRtController::class, 'getRTCamerasDropdown'])->name('get-rt-cameras-dropdown');

    Route::get('get-latest-test_report_ut_number',           [TestReportUtController::class, 'getLatestTestReportUtNumber'])->name('get-latest-test_report_ut_number');
    Route::get('get-pending-customer-ut-data',               [TestReportUtController::class, 'getPendingCustomerData'])->name('get-pending-customer-ut-data');
    Route::get('get-pending-customers-for-ut',               [TestReportUtController::class, 'getPendingCustomersForUt'])->name('get-pending-customers-for-ut');
    Route::get('get-latest_ut_url_no',                       [TestReportUtController::class, 'getLatestULRNo'])->name('get-latest_ut_url_no');
    Route::get('check-ut-ulr_no',                            [TestReportUtController::class, 'checkUlrNo'])->name('check-ut-ulr_no');
    Route::get('get-test_report_ut_copy_list',               [TestReportUtController::class, 'getCopyReportsList'])->name('get-test_report_ut_copy_list');
    Route::get('get-test_report_ut_details_for_copy',        [TestReportUtController::class, 'getReportDetailsForCopy'])->name('get-test_report_ut_details_for_copy');
    Route::get('get-last-ut-details',                        [TestReportUtController::class, 'getLastReportEquipmentAndProbes'])->name('get-last-ut-details');
    Route::get('get-last-mpt-details',                       [TestReportMptController::class, 'getLastReportMptDetails'])->name('get-last-mpt-details');

    Route::get('get-latest-test_report_dpt_number',          [TestReportDptController::class, 'getLatestTestReportDptNumber'])->name('get-latest-test_report_dpt_number');
    Route::get('get-pending-customer-dpt-data',              [TestReportDptController::class, 'getPendingCustomerData'])->name('get-pending-customer-dpt-data');
    Route::get('get-pending-customers-for-dpt',              [TestReportDptController::class, 'getPendingCustomersForDpt'])->name('get-pending-customers-for-dpt');
    Route::get('get-latest_dpt_url_no',                      [TestReportDptController::class, 'getLatestULRNo'])->name('get-latest_dpt_url_no');
    Route::get('check-dpt-ulr_no',                           [TestReportDptController::class, 'checkUlrNo'])->name('check-dpt-ulr_no');
    Route::get('get-test_report_dpt_copy_list',              [TestReportDptController::class, 'getCopyReportsList'])->name('get-test_report_dpt_copy_list');
    Route::get('get-test_report_dpt_details_for_copy',       [TestReportDptController::class, 'getReportDetailsForCopy'])->name('get-test_report_dpt_details_for_copy');
    Route::get('get-last-dpt-details',                       [TestReportDptController::class, 'getLastReportDptDetails'])->name('get-last-dpt-details');
    Route::get('get-latest-test_report_mpt_number',          [TestReportMptController::class, 'getLatestTestReportMptNumber'])->name('get-latest-test_report_mpt_number');
    Route::get('get-pending-customers-for-mpt',              [TestReportMptController::class, 'getPendingCustomersForMpt'])->name('get-pending-customers-for-mpt');
    Route::get('get-pending-customer-mpt-data',               [TestReportMptController::class, 'getPendingCustomerData'])->name('get-pending-customer-mpt-data');
    Route::get('get-latest_mpt_url_no',                       [TestReportMptController::class, 'getLatestULRNo'])->name('get-latest_mpt_url_no');
    Route::get('check-mpt-ulr_no',                            [TestReportMptController::class, 'checkUlrNo'])->name('check-mpt-ulr_no');
    Route::get('get-test_report_mpt_copy_list',               [TestReportMptController::class, 'getCopyReportsList'])->name('get-test_report_mpt_copy_list');
    Route::get('get-test_report_mpt_details_for_copy',        [TestReportMptController::class, 'getReportDetailsForCopy'])->name('get-test_report_mpt_details_for_copy');
    Route::get('/test_report_mpt_customer_client-list',       [TestReportMptController::class, 'existsCustomerClient'])->name('test_report_mpt_customer_client-list');
    Route::get('/test_report_mpt_part_no-list',               [TestReportMptController::class, 'existsPartNo'])->name('test_report_mpt_part_no-list');
    Route::get('/test_report_mpt_drg_no-list',                [TestReportMptController::class, 'existsDrgNo'])->name('test_report_mpt_drg_no-list');
    Route::get('/test_report_mpt_test_carried_out_at-list',  [TestReportMptController::class, 'existsTestCarriedOutAt'])->name('test_report_mpt_test_carried_out_at-list');
    Route::get('/test_report_mpt_stage_of_test-list',        [TestReportMptController::class, 'existsStageOfTest'])->name('test_report_mpt_stage_of_test-list');
    Route::get('/test_report_mpt_surface_condition-list',    [TestReportMptController::class, 'existsSurfaceCondition'])->name('test_report_mpt_surface_condition-list');
    Route::get('/test_report_mpt_surface_temp-list',         [TestReportMptController::class, 'existsSurfaceTemp'])->name('test_report_mpt_surface_temp-list');
    Route::get('/test_report_mpt_thickness-list',            [TestReportMptController::class, 'existsThickness'])->name('test_report_mpt_thickness-list');
    Route::get('/test_report_mpt_test_technique-list',       [TestReportMptController::class, 'existsTestTechnique'])->name('test_report_mpt_test_technique-list');
    Route::get('/test_report_mpt_type_of_magnetization-list',[TestReportMptController::class, 'existsTypeOfMagnetization'])->name('test_report_mpt_type_of_magnetization-list');
    Route::get('/test_report_mpt_type_of_current-list',      [TestReportMptController::class, 'existsTypeOfCurrent'])->name('test_report_mpt_type_of_current-list');
    Route::get('/test_report_mpt_prod_pole_spacing-list',    [TestReportMptController::class, 'existsProdPoleSpacing'])->name('test_report_mpt_prod_pole_spacing-list');
    Route::get('/test_report_mpt_performance_verification-list', [TestReportMptController::class, 'existsPerformanceVerification'])->name('test_report_mpt_performance_verification-list');
    Route::get('/test_report_mpt_lighting-list',              [TestReportMptController::class, 'existsLighting'])->name('test_report_mpt_lighting-list');
    Route::get('/test_report_mpt_light_intensity-list',       [TestReportMptController::class, 'existsLightIntensity'])->name('test_report_mpt_light_intensity-list');
    Route::get('/test_report_mpt_background_light-list',      [TestReportMptController::class, 'existsBackgroundLight'])->name('test_report_mpt_background_light-list');
    Route::get('/test_report_mpt_uva_light_intensity-list',   [TestReportMptController::class, 'existsUvaLightIntensity'])->name('test_report_mpt_uva_light_intensity-list');
    Route::get('/test_report_mpt_yoke_wt_lift_check-list',    [TestReportMptController::class, 'existsYokeWtLiftCheck'])->name('test_report_mpt_yoke_wt_lift_check-list');
    Route::get('/test_report_mpt_discontinuity_evaluation-list', [TestReportMptController::class, 'existsDiscontinuityEvaluation'])->name('test_report_mpt_discontinuity_evaluation-list');
    Route::get('/test_report_mpt_product_code-list',           [TestReportMptController::class, 'existsProductCode'])->name('test_report_mpt_product_code-list');
    Route::get('/customer_dc_non_returnable_vehicle_no-list', [CustomerDCNonReturnableController::class, 'getVehicleNoList'])->name('customer_dc_non_returnable_vehicle_no-list');
    Route::get('/customer_dc_non_returnable_mode_of_transport-list', [CustomerDCNonReturnableController::class, 'getModeOfTransportList'])->name('customer_dc_non_returnable_mode_of_transport-list');
    Route::get('/customer_dc_non_returnable_sp_note-list', [CustomerDCNonReturnableController::class, 'getSpNoteList'])->name('customer_dc_non_returnable_sp_note-list');
    Route::get('/customer_dc_non_returnable_transporter-list', [CustomerDCNonReturnableController::class, 'getTransporterList'])->name('customer_dc_non_returnable_transporter-list');


 

    /**
      *LNR Route
    */
    Route::get('get-equipment_ut_lnr_data',[EquipmentUTController::class,'equipmentLNRData'])->name('get-equipment_ut_lnr_data');
    Route::get('get-equipment_mpt_lnr_data',[EquipmentMPTController::class,'equipmentLNRData'])->name('get-equipment_mpt_lnr_data');

    Route::get('get-camera_rt_lnr_data',[RTCameraController::class,'cameraLNRData'])->name('get-camera_rt_lnr_data');
    Route::get('get-chemical_lpt_lnr_data',[LPTChemicalController::class,'chemicalLNRData'])->name('get-chemical_lpt_lnr_data');

    Route::get('get-probe_ut_lnr_data',[ProbeUTController::class,'probeUTLNRData'])->name('get-probe_ut_lnr_data');  
    Route::get('get-material_mpt_lnr_data',[MaterialMptController::class,'materialMptLNRData'])->name('get-material_mpt_lnr_data');  
    Route::get('get-grn_lnr_data',[GRNSupplierController::class,'grnLNRData'])->name('get-grn_lnr_data');
    
    Route::get('get-material_inspection_lnr_data',[MaterialInspectionController::class,'materialInspectionLNRData'])->name('get-material_inspection_lnr_data');  
    Route::get('get-material_inward_lnr_data',[MaterialInwardController::class,'materialInwardLNRData'])->name('get-material_inward_lnr_data');  
    Route::get('get-offer_lnr_data',[OfferController::class,'offerLNRData'])->name('get-offer_lnr_data');  
    Route::get('get-latest_offer_number',[OfferController::class,'getLatestOfferNumber'])->name('get-latest_offer_number');
    Route::get('get-offer_parts_by_job_desc',[OfferController::class,'getPartsByJobDesc'])->name('get-offer_parts_by_job_desc');
    Route::get('/get-material_inward_part_no_list', [MaterialInwardController::class, 'getInwardPartNoList'])->name('get-material_inward_part_no_list');
    Route::get('/get-material_inward_drg_no_list', [MaterialInwardController::class, 'getInwardDrgNoList'])->name('get-material_inward_drg_no_list');
    Route::get('/get-material_inward_product_code_list', [MaterialInwardController::class, 'getInwardProductCodeList'])->name('get-material_inward_product_code_list');
    Route::get('get-technique_sheet_rt_lnr_data',[TechniqueSheetRtController::class,'techniqueSheetRtLNRData'])->name('get-technique_sheet_rt_lnr_data');
    Route::get('get-test_report_rt_lnr_data',[TestReportRtController::class,'testReportRtLNRData'])->name('get-test_report_rt_lnr_data');
    Route::get('get-test_report_dpt_lnr_data',[TestReportDptController::class,'testReportDptLNRData'])->name('get-test_report_dpt_lnr_data');
    Route::get('get-test_report_mpt_lnr_data',[TestReportMptController::class,'testReportMptLNRData'])->name('get-test_report_mpt_lnr_data');
    Route::get('get-test_report_ut_lnr_data',[TestReportUtController::class,'testReportUtLNRData'])->name('get-test_report_ut_lnr_data');
    Route::get('/test_report_rt_lead_screen_thick-list', [TestReportRtController::class, 'existsLeadScreenThick'])->name('test_report_rt_lead_screen_thick-list');
    Route::get('/test_report_rt_test_carried_out_at-list', [TestReportRtController::class, 'existsTestCarriedOutAt'])->name('test_report_rt_test_carried_out_at-list');
    Route::get('get-camera_detail', [TestReportRtController::class, 'getCameraDetail'])->name('get-camera_detail');

    Route::get('get-po_mapping_process_lnr_data',[POMappingProcessController::class,'pomappingprocessLNRData'])->name('get-po_mapping_process_lnr_data');
    Route::get('get-location_lnr_data',[LocationController::class,'locationLNRData'])->name('get-location_lnr_data');
    Route::get('get-customer_dc_non_returnable_lnr_data', [CustomerDCNonReturnableController::class, 'getLNRData'])->name('get-customer_dc_non_returnable_lnr_data');
 
    Route::get('get-rt_reports_list', [RtLabelPrintController::class, 'RtReportsList'])->name('get-rt_reports_list');
    Route::get('get-rt_report_details_for_print', [RtLabelPrintController::class, 'getRtReportDetailsForPrint'])->name('get-rt_report_details_for_print');
    Route::post('print-rt_label_print', [RtLabelPrintController::class, 'printRtLabel'])->name('print-rt_label_print');
    Route::get('print-test_report_rt', [TestReportRtController::class, 'printReport'])->name('print-test_report_rt');


    /**
      *Upload Route
    */
    Route::post('/upload-docs',[FileController::class,'upload'])->name('upload-docs');
    Route::post('/remove-docs',[FileController::class,'removeTempUpload'])->name('remove-docs');
    Route::post('/copy-docs',[FileController::class,'copyFiles'])->name('copy-docs');
    Route::post('/change-company_year',[CompanyYearController::class,'change'])->name('change-company_year'); 
    Route::get('/switch-company_year',[CompanyYearController::class,'switchYear'])->name('switch-company_year');
    Route::get('/get-company_year',[CompanyYearController::class,'makeYear'])->name('get-company_year');
    Route::get('/get-company_years',[CompanyYearController::class,'companyYearData'])->name('get-company_years');

    /* Import Route */
    Route::get('importview', [CountryController::class, 'importview']);
    Route::post('import', [CountryController::class, 'importCountry'])->name('import');
    Route::get('importview_customer', [CustomerController::class, 'importviewCustomer']);
    Route::post('import_customer', [CustomerController::class, 'importCustomer'])->name('import_customer');
    Route::get('importview_supplier', [SupplierController::class, 'importviewSupplier']);
    Route::post('import_supplier', [SupplierController::class, 'importSupplier'])->name('import_supplier');

    /* Other Route */
    Route::get('/get-operators',[OperatorController::class,'operatorData'])->name('get-operators');
    Route::get('/manage-ilac_logo',[ILacLogoController::class,'index'])->name('manage-ilac_logo');
    Route::post('/update-logo/{company}', [ILacLogoController::class, 'updateLogo'])->name('companies.updateLogo');
    Route::get('/get-countries',[CountryController::class,'getCountryData'])->name('get-countries');
    Route::get('/get-states',[StateController::class,'getStateData'])->name('get-states');
    Route::get('/get-cities',[CityController::class,'getCityData'])->name('get-cities');
    Route::get('/city-relation-field',[CityController::class,'getRelationValues'])->name('city-relation-field');
    Route::get('/get-gst_data',[GstConfigurationController::class,'gstData'])->name('get-gst_data');
    Route::get('/get-customer_code',[CustomerController::class,'getCustomerCode'])->name('get-customer_code');
    Route::get('/customer-relation-field',[CustomerController::class,'getRelationValues'])->name('customer-relation-field');
    Route::get('/get-customers',[CustomerController::class,'getCustomerData'])->name('get-customers');
    Route::get('/get-type_of_job',[TypeOfJobController::class,'getTypeOfJobData'])->name('get-type_of_job');
    Route::get('/get-job_description',[JobDescriptionController::class,'getJobDescriptionData'])->name('get-job_description');
    Route::get('/get-part',[PartController::class,'getPartData'])->name('get-part');
    Route::get('/inquiry_relation_field',[InquiryController::class ,'CustomerContactPersonData'])->name('inquiry_relation_field');
    Route::get('/get-item_code',[ItemController::class,'getItemCode'])->name('get-item_code');
    Route::get('/quotation_kind_attn',[QuotationController::class ,'CustomerContactPersonData'])->name('quotation_kind_attn');
    Route::get('/get_all_quotation_no',[QuotationController::class ,'getAllQuotationNo'])->name('get_all_quotation_no');
    Route::get('/get_quotation_terms_and_conditions',[QuotationController::class ,'getQuotTermsConditions'])->name('get_quotation_terms_and_conditions');
    Route::get('/get_all_po_no',[PurchaseOrderController::class ,'getAllPONo'])->name('get_all_po_no');
    Route::get('/get_purchase_order_terms_and_conditions',[PurchaseOrderController::class ,'getPOTermsConditions'])->name('get_purchase_order_terms_and_conditions');
    Route::get('/get-item_opening',[ItemOpeningController::class ,'getItemOpening'])->name('get-item_opening');
    Route::get('/get-item_opening_prod_area',[ItemOpeningProdAreaController::class ,'getItemOpening'])->name('get-item_opening_prod_area');
    Route::get('/get_batch_no_and_sr_no_return_slip',[ItemReturnSlipController::class ,'getBatchNoAndSrNoReturnSlipData'])->name('get_batch_no_and_sr_no_return_slip');
    Route::get('/get_all_oa_no',[OrderAcceptanceController::class ,'getAllOANo'])->name('get_all_oa_no');
    Route::get('/get_oa_terms_and_conditions',[OrderAcceptanceController::class ,'getOATermsConditions'])->name('get_oa_terms_and_conditions');
    Route::get('/get-location-states',[LocationController::class,'getLocationState'])->name('get-location-states');
    Route::get('/get-city',[LocationController::class,'getLocationCity'])->name('get-city');
    Route::get('/get-units',[UnitController::class,'getUnits'])->name('get-units');
    Route::get('/get-shifts',[ShiftController::class,'getShifts'])->name('get-shifts');
    Route::get('/get-suppliers',[SupplierController::class,'getSupplierData'])->name('get-suppliers');
    Route::get('/get-location_data',[AdminController::class,'getLocationData'])->name('get-location_data');
    Route::post('/getUserPremissionData',[UserAccessController::class,'getUserPremissionData'])->name('getUserPremission');
    Route::get('/location-relation-field',[LocationController::class,'getLocationRelationValues'])->name('location-relation-field');
    Route::post('/get-supplier_kind_attn', [PurchaseOrderController::class, 'getSupplierKindAttn'])->name('get-supplier_kind_attn');
    Route::get('/get-assign-data',[AssignFormatNoController::class,'getAssignDataBasedOnPageId'])->name('get-assign-data');
    Route::get('/get-assign-data-based-on-location',[AssignFormatNoController::class,'getAssignDataBasedOnLocation'])->name('get-assign-data-based-on-location');
    Route::get('/get-sr_no',[InterLocationTransferController::class,'getSrNo'])->name('get-sr_no');
    Route::post('get-pod_grn_details', [GRNSupplierController::class, 'getPODetailsForGRN'])->name('get-pod_grn_details');
    Route::post('get-service_po_grn_details', [GRNSupplierController::class, 'getServicePODetailsForGRN'])->name('get-service_po_grn_details');
    Route::post('/get-service_po_supplier_kind_attn', [ServicePOController::class, 'getServicePOSupplierKindAttn'])->name('get-service_po_supplier_kind_attn');
    Route::get('/get-iqi_designations', [IqiDesignationController::class, 'getIqiDesignations'])->name('get-iqi_designations');
    Route::get('/get-films', [FilmController::class, 'getFilms'])->name('get-films');
    Route::get('/get-iqi_sensitivities', [IqiSensitivityController::class, 'getIqiSensitivities'])->name('get-iqi_sensitivities');
    Route::get('/get-procedure_references', [ProcedureReferenceController::class, 'getProcedureReferences']);
    Route::get('/get-evaluation_as_pers', [EvaluationAsPerController::class, 'getEvaluationAsPers']);
    Route::get('/get-area_of_coverages', [AreaOfCoverageController::class, 'getAreaOfCoverages'])->name('get-area_of_coverages');
    Route::get('/get-materials', [MaterialController::class, 'materialData'])->name('get-materials');
    Route::get('/get-acceptance_standards', [AcceptanceStandardController::class, 'getAcceptanceStandards'])->name('get-acceptance_standards');
    Route::get('/get-film_brands', [FilmBrandController::class, 'getFilmBrands'])->name('get-film_brands');
    Route::get('/get-film_types', [FilmTypeController::class, 'getFilmTypes'])->name('get-film_types');
    Route::get('/get-finding_levels', [FindingLevelController::class, 'getFindingLevels'])->name('get-finding_levels');
    Route::get('/get-findings', [FindingController::class, 'getFindings'])->name('get-findings');
    Route::get('/get-authority_persons', [AuthorityPersonController::class, 'getAuthorityPersons'])->name('get-authority_persons');
    Route::get('/get-enclosures', [EnclosureController::class, 'getEnclosures'])->name('get-enclosures');
    Route::get('/get-aerb_documents', [AerbDocumentController::class, 'getAerbDocuments'])->name('get-aerb_documents');
    Route::get('/get-film_results', [FilmResultController::class, 'getFilmResults'])->name('get-film_results');
    Route::get('/get-parts-by-job_desc', [MaterialInwardController::class, 'getPartsByJobDesc'])->name('get-parts-by-job_desc');
    Route::get('/get-item_groups',[ItemGroupController::class,'getItemGroups'])->name('get-item_groups');
    Route::post('/email_purchase_orders', [EmailController::class, 'EmailPurchaseOrder'])->name('email_purchase_orders');
    Route::post('/email_service_pos', [EmailController::class, 'EmailServicePO'])->name('email_service_pos');
    Route::post('/email_single_purchase_order', [EmailController::class, 'EmailSinglePurchaseOrder'])->name('email_single_purchase_order');
    Route::post('/email_single_service_po', [EmailController::class, 'EmailSingleServicePO'])->name('email_single_service_po');
    Route::post('/get-old-inward-details', [MaterialInwardController::class, 'getOldInwardDetails'])->name('get-old-inward-details');
    Route::post('/get-rt-reports-list-for-inward', [MaterialInwardController::class, 'getRtReportsListForInward'])->name('get-rt-reports-list-for-inward');

    /* Offer Routes */
    Route::get('/get-offer_authorized_details',[OfferAuthorizedController::class,'getOfferDetails'])->name('get-offer_authorized_details');
    Route::post('/authorize-offer',[OfferAuthorizedController::class,'authorizeOffer'])->name('authorize-offer');
    Route::post('/unauthorize-offer',[OfferAuthorizedController::class,'unauthorizeOffer'])->name('unauthorize-offer');
    Route::get('/offer',[OfferController::class,'manage'])->name('offer');
  });

  Route::prefix('customer')->middleware(['login_customer', 'customer_redirect_auth'])->group( function () {
    Route::get('/dashboard',[AuthController::class,'customer_dashboard'])->middleware('clear.dashboard.cache')->name('customer_dashboard'); // add middleware on 16-02-2026
  });


  // PDF Generate Route
  Route::get('/check-file_exists',[ReportController::class,'checkReportExists'])->name('check-file_exists');

  /**
    *Link Storage Route
  */
  Route::get('/linkstorage', function () {
    dd(Artisan::call('storage:link'));
  });

  Route::get('/clear-cache', function() {
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('config:cache');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    return "Done";
  });
