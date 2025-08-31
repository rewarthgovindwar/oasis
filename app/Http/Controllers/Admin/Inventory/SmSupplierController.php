<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\SmSupplierRequest;
use App\SmSupplier;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmSupplierController extends Controller
{


    public function index(Request $request)
    {
        /*
        try {
        */
            $suppliers = SmSupplier::where('school_id', Auth::user()->school_id)->select([
                'id', 'company_name', 'company_address', 'contact_person_name', 'contact_person_mobile', 'contact_person_email', 'cotact_person_address', 'description', 'active_status',
            ])->get();

            return view('backEnd.inventory.supplierList', ['suppliers' => $suppliers]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmSupplierRequest $smSupplierRequest)
    {
        /*
        try {
        */
            $smSupplier = new SmSupplier();
            $smSupplier->company_name = $smSupplierRequest->company_name;
            $smSupplier->company_address = $smSupplierRequest->company_address;
            $smSupplier->contact_person_name = $smSupplierRequest->contact_person_name;
            $smSupplier->contact_person_mobile = $smSupplierRequest->contact_person_mobile;
            $smSupplier->contact_person_email = $smSupplierRequest->contact_person_email;
            $smSupplier->description = $smSupplierRequest->description;
            $smSupplier->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smSupplier->un_academic_id = getAcademicId();
            } else {
                $smSupplier->academic_id = getAcademicId();
            }

            $smSupplier->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function edit(Request $request, $id)
    {
        /*
        try {
        */
            $editData = SmSupplier::find($id);
            $suppliers = SmSupplier::where('school_id', Auth::user()->school_id)->select([
                'id', 'company_name', 'company_address', 'contact_person_name', 'contact_person_mobile', 'contact_person_email', 'cotact_person_address', 'description', 'active_status',
            ])->get();

            return view('backEnd.inventory.supplierList', ['editData' => $editData, 'suppliers' => $suppliers]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmSupplierRequest $smSupplierRequest, $id)
    {
        /*
        try {
        */
            $suppliers = SmSupplier::find($id);
            $suppliers->company_name = $smSupplierRequest->company_name;
            $suppliers->company_address = $smSupplierRequest->company_address;
            $suppliers->contact_person_name = $smSupplierRequest->contact_person_name;
            $suppliers->contact_person_mobile = $smSupplierRequest->contact_person_mobile;
            $suppliers->contact_person_email = $smSupplierRequest->contact_person_email;
            $suppliers->description = $smSupplierRequest->description;
            $suppliers->updated_by = Auth()->user()->id;
            if (moduleStatusCheck('University')) {
                $suppliers->un_academic_id = getAcademicId();
            }

            $suppliers->update();

            Toastr::success('Operation successful', 'Success');

            return redirect('suppliers');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteSupplierView(Request $request, $id)
    {
        /*
        try {
        */
            $title = __('common.are_you_sure_to_detete_this_item');
            $url = route('delete-supplier', $id);

            return view('backEnd.modal.delete', ['id' => $id, 'title' => $title, 'url' => $url]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteSupplier(Request $request, $id)
    {
        /*
        try {
        */
            $tables = \App\tableList::getTableList('supplier_id', $id);
            /*
            try {
            */
                if ($tables == null) {
                    $result = SmSupplier::destroy($id);

                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                } else {
                    $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                    Toastr::error($msg, 'Failed');
                    return redirect()->back();
                }
            /*
            } catch (\Illuminate\Database\QueryException $e) {
                $msg = 'This data already used in  : '.$tables.' Please remove those data first';
                Toastr::error($msg, 'Failed');

                return redirect()->back();
            }
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
