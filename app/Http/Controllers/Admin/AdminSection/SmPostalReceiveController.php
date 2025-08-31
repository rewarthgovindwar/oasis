<?php

namespace App\Http\Controllers\Admin\AdminSection;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminSection\SmPostalReceiveRequest;
use App\SmPostalReceive;
use Brian2694\Toastr\Facades\Toastr;
use DataTables;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;

class SmPostalReceiveController extends Controller
{

    public function index(Request $request)
    {
        /*
        try {
        */
            return view('backEnd.admin.postal_receive');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'from_title' => 'required',
            'reference_no' => 'required',
            'address' => 'required',
            'to_title' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' ', $errors);
            Toastr::error($errorMessage, 'Validation Failed');

            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($request->hasFile('file')) {
            $fileExtension = $request->file('file')->getClientOriginalExtension();
            $supportedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'txt'];
            if (! in_array($fileExtension, $supportedExtensions)) {
                Toastr::error('Unsupported File', 'Failed');

                return redirect()->back();
            }
        }

/*        try {*/
            $destination = 'public/uploads/postal/';
            $fileName = fileUpload($request->file, $destination);
            $smPostalReceive = new SmPostalReceive();
            $smPostalReceive->from_title = $request->from_title;
            $smPostalReceive->reference_no = $request->reference_no;
            $smPostalReceive->address = $request->address;
            $smPostalReceive->date = date('Y-m-d', strtotime($request->date));
            $smPostalReceive->note = $request->note;
            $smPostalReceive->to_title = $request->to_title;
            $smPostalReceive->file = $fileName;
            $smPostalReceive->created_by = Auth::user()->id;
            $smPostalReceive->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smPostalReceive->un_academic_id = getAcademicId();
            } else {
                $smPostalReceive->academic_id = getAcademicId();
            }

            $smPostalReceive->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function show(Request $request, $id)
    {
        /*
        try {
        */
            $postal_receives = SmPostalReceive::get();
            $postal_receive = SmPostalReceive::find($id);

            return view('backEnd.admin.postal_receive', ['postal_receives' => $postal_receives, 'postal_receive' => $postal_receive]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmPostalReceiveRequest $smPostalReceiveRequest)
    {
        /*
        try {
        */
            $destination = 'public/uploads/postal/';
            $postal_receive = SmPostalReceive::find($smPostalReceiveRequest->id);
            $postal_receive->from_title = $smPostalReceiveRequest->from_title;
            $postal_receive->reference_no = $smPostalReceiveRequest->reference_no;
            $postal_receive->address = $smPostalReceiveRequest->address;
            $postal_receive->date = date('Y-m-d', strtotime($smPostalReceiveRequest->date));
            $postal_receive->note = $smPostalReceiveRequest->note;
            $postal_receive->to_title = $smPostalReceiveRequest->to_title;
            $postal_receive->file = fileUpdate($postal_receive->file, $smPostalReceiveRequest->file, $destination);
            if (moduleStatusCheck('University')) {
                $postal_receive->un_academic_id = getAcademicId();
            }

            $postal_receive->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('postal-receive');
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
            $postal_receive = SmPostalReceive::find($request->id);
            if ($postal_receive->file !== '' && file_exists($postal_receive->file)) {
                unlink($postal_receive->file);
            }

            $postal_receive->delete();

            Toastr::success('Operation successful', 'Success');

            return redirect('postal-receive');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function postalReceiveDatatable()
    {
        /*
        try {
        */
            $postal_receives = SmPostalReceive::query();

            return DataTables::of($postal_receives)
                ->addIndexColumn()
                ->addColumn('query_date', function ($row) {
                    return dateConvert(@$row->date);
                })
                ->addColumn('action', function ($row): string {
                    return '<div class="dropdown CRM_dropdown">
                                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">'.app('translator')->get('common.select').'</button>
                                        
                                <div class="dropdown-menu dropdown-menu-right">'.
                            (userPermission('postal-receive_edit') ? '<a class="dropdown-item" href="'.route('postal-receive_edit', [$row->id]).'">'.app('translator')->get('common.edit').'</a>' : '').

                            (userPermission('postal-receive_delete') ? (Config::get('app.app_sync') ? '<span data-toggle="tooltip" title="Disabled For Demo"><a class="dropdown-item" href="#" >'.app('translator')->get('common.disable').'</a></span>' :
                            '<a onclick="deleteQueryModal('.$row->id.');"  class="dropdown-item" href="#" data-toggle="modal" data-target="#deletePostalReceiveModal" data-id="'.$row->id.'"  >'.app('translator')->get('common.delete').'</a>') : '').

                            (@$row->file !== '' ? (userPermission('postal-receive-document') ? (@file_exists($row->file) ? (Config::get('app.app_sync') ? '<span data-toggle="tooltip" title="Disabled For Demo"><a class="dropdown-item" href="#" >'.app('translator')->get('common.disable').'</a></span>' :
                            '<a class="dropdown-item" href="'.url(@$row->file).'">'.app('translator')->get('common.download').'</a>') : '') : '') : '').
                        '</div>
                            </div>';
                })
                ->rawColumns(['action', 'date'])
                ->make(true);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
