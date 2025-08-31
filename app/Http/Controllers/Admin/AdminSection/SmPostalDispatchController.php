<?php

namespace App\Http\Controllers\Admin\AdminSection;

use Exception;
use DataTables;
use App\SmPostalDispatch;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Http\Requests\Admin\AdminSection\SmPostalDispatchRequest;

class SmPostalDispatchController extends Controller
{

    public function index(Request $request)
    {
        /*
        try {
        */
            return view('backEnd.admin.postal_dispatch');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmPostalDispatchRequest $smPostalDispatchRequest)
    {
        /*
        try {
        */
            $destination = 'public/uploads/postal/';
            $fileName = fileUpload($smPostalDispatchRequest->image, $destination);
            $smPostalDispatch = new SmPostalDispatch();
            $smPostalDispatch->from_title = $smPostalDispatchRequest->from_title;
            $smPostalDispatch->reference_no = $smPostalDispatchRequest->reference_no;
            $smPostalDispatch->address = $smPostalDispatchRequest->address;
            $smPostalDispatch->date = date('Y-m-d', strtotime($smPostalDispatchRequest->date));
            $smPostalDispatch->note = $smPostalDispatchRequest->note;
            $smPostalDispatch->to_title = $smPostalDispatchRequest->to_title;
            $smPostalDispatch->file = $fileName;
            $smPostalDispatch->created_by = Auth::user()->id;
            $smPostalDispatch->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smPostalDispatch->un_academic_id = getAcademicId();
            } else {
                $smPostalDispatch->academic_id = getAcademicId();
            }

            $smPostalDispatch->save();

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
            $postal_dispatchs = SmPostalDispatch::get();
            $postal_dispatch = SmPostalDispatch::find($id);

            return view('backEnd.admin.postal_dispatch', ['postal_dispatchs' => $postal_dispatchs, 'postal_dispatch' => $postal_dispatch]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmPostalDispatchRequest $smPostalDispatchRequest)
    {
        /*
        try {
        */
            $destination = 'public/uploads/postal/';
            $postal_dispatch = SmPostalDispatch::find($smPostalDispatchRequest->id);

            $postal_dispatch->from_title = $smPostalDispatchRequest->from_title;
            $postal_dispatch->reference_no = $smPostalDispatchRequest->reference_no;
            $postal_dispatch->address = $smPostalDispatchRequest->address;
            $postal_dispatch->date = date('Y-m-d', strtotime($smPostalDispatchRequest->date));
            $postal_dispatch->note = $smPostalDispatchRequest->note;
            $postal_dispatch->to_title = $smPostalDispatchRequest->to_title;
            $postal_dispatch->file = fileUpdate($postal_dispatch->file, $smPostalDispatchRequest->file, $destination);
            if (moduleStatusCheck('University')) {
                $postal_dispatch->un_academic_id = getAcademicId();
            }

            $postal_dispatch->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('postal-dispatch');
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
            $postal_dispatch = SmPostalDispatch::find($request->id);
            if ($postal_dispatch->file !== '' && file_exists($postal_dispatch->file)) {
                unlink($postal_dispatch->file);
            }

            $postal_dispatch->delete();

            Toastr::success('Operation successful', 'Success');

            return redirect('postal-dispatch');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function postalDispatchDatatable()
    {
        /*
        try {
        */
            $postal_dispatchs = SmPostalDispatch::query();

            return DataTables::of($postal_dispatchs)
                ->addIndexColumn()
                ->addColumn('query_date', function ($row) {
                    return dateConvert(@$row->date);
                })
                ->addColumn('action', function ($row): string {
                    return '<div class="dropdown CRM_dropdown">
                                <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">'.app('translator')->get('common.select').'</button>
                                        
                                <div class="dropdown-menu dropdown-menu-right">'.
                            (userPermission('postal-dispatch_edit') ? '<a class="dropdown-item" href="'.route('postal-dispatch_edit', [$row->id]).'">'.app('translator')->get('common.edit').'</a>' : '').

                            (userPermission('postal-dispatch_delete') ? (Config::get('app.app_sync') ? '<span data-toggle="tooltip" title="Disabled For Demo"><a class="dropdown-item" href="#" >'.app('translator')->get('common.disable').'</a></span>' :
                            '<a onclick="deleteQueryModal('.$row->id.');"  class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteDispatchReceiveModal" data-id="'.$row->id.'"  >'.app('translator')->get('common.delete').'</a>') : '').
                            
                            (!empty($row->file) && file_exists($row->file) && userPermission('postal-dispatch-document') ? '<a class="dropdown-item" href="'.url(@$row->file).'">'.app('translator')->get('common.download').'</a>':'').

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
