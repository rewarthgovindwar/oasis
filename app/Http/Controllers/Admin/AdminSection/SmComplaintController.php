<?php

namespace App\Http\Controllers\Admin\AdminSection;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminSection\SmComplaintRequest;
use App\SmComplaint;
use App\SmSetupAdmin;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmComplaintController extends Controller
{

    public function index(Request $request)
    {
        /*
        try {
        */
            $complaints = SmComplaint::with('complaintType', 'complaintSource')->get();
            $complaint_types = SmSetupAdmin::where('type', 2)->get();
            $complaint_sources = SmSetupAdmin::where('type', 3)->get();

            return view('backEnd.admin.complaint', ['complaints' => $complaints, 'complaint_types' => $complaint_types, 'complaint_sources' => $complaint_sources]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function create(): void
    {
        //
    }

    public function store(SmComplaintRequest $smComplaintRequest)
    {
        /*
        try {
        */
            $destination = 'public/uploads/complaint/';
            $fileName = fileUpload($smComplaintRequest->file, $destination);
            $smComplaint = new SmComplaint();
            $smComplaint->complaint_by = $smComplaintRequest->complaint_by;
            $smComplaint->complaint_type = $smComplaintRequest->complaint_type;
            $smComplaint->complaint_source = $smComplaintRequest->complaint_source;
            $smComplaint->phone = $smComplaintRequest->phone;
            $smComplaint->date = date('Y-m-d', strtotime($smComplaintRequest->date));
            $smComplaint->description = $smComplaintRequest->description;
            $smComplaint->action_taken = $smComplaintRequest->action_taken;
            $smComplaint->assigned = $smComplaintRequest->assigned;
            $smComplaint->file = $fileName;
            $smComplaint->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smComplaint->un_academic_id = getAcademicId();
            } else {
                $smComplaint->academic_id = getAcademicId();
            }

            $smComplaint->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('complaint');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function show($id)
    {

        /*
        try {
        */
            $complaint = SmComplaint::find($id);

            return view('backEnd.admin.complaintDetails', ['complaint' => $complaint]);
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
            $complaints = SmComplaint::get();
            $complaint = SmComplaint::find($id);
            $complaint_types = SmSetupAdmin::where('type', 2)->get();
            $complaint_sources = SmSetupAdmin::where('type', 3)->get();

            return view('backEnd.admin.complaint', ['complaint' => $complaint, 'complaints' => $complaints, 'complaint_types' => $complaint_types, 'complaint_sources' => $complaint_sources]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmComplaintRequest $smComplaintRequest)
    {
        /*
        try {
        */
            $destination = 'public/uploads/complaint/';
            $complaint = SmComplaint::find($smComplaintRequest->id);
            $complaint->complaint_by = $smComplaintRequest->complaint_by;
            $complaint->complaint_type = $smComplaintRequest->complaint_type;
            $complaint->complaint_source = $smComplaintRequest->complaint_source;
            $complaint->phone = $smComplaintRequest->phone;
            $complaint->date = date('Y-m-d', strtotime($smComplaintRequest->date));
            $complaint->description = $smComplaintRequest->description;
            $complaint->action_taken = $smComplaintRequest->action_taken;
            $complaint->assigned = $smComplaintRequest->assigned;
            $complaint->file = fileUpdate($complaint->file, $smComplaintRequest->file, $destination);
            $complaint->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('complaint');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function destroy(Request $request)
    {
        /*
        try {
        */
            $complaint = SmComplaint::find($request->id);
            if ($complaint->file !== '' && file_exists($complaint->file)) {
                unlink($complaint->file);
            }

            $complaint->delete();

            Toastr::success('Operation successful', 'Success');

            return redirect('complaint');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function complaint()
    {
        $complaints = SmComplaint::all();

        return $this->sendResponse($complaints->toArray(), 'Complaint retrieved successfully.');
    }
}
