<?php

namespace App\Http\Controllers\Admin\AdminSection;

use Exception;
use DataTables;
use App\SmClass;
use App\SmSetupAdmin;
use App\SmAdmissionQuery;
use Illuminate\Http\Request;
use App\SmAdmissionQueryFollowup;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use App\Http\Requests\Admin\AdminSection\SmAdmissionQueryRequest;
use App\Http\Requests\Admin\AdminSection\SmAdmissionQuerySearchRequest;
use App\Http\Requests\Admin\AdminSection\SmAdmissionQueryFollowUpRequest;
use Modules\University\Repositories\Interfaces\UnCommonRepositoryInterface;

class SmAdmissionQueryController extends Controller
{


    public function index()
    {
        /*
        try {
        */
            $classes = SmClass::get();
            $references = SmSetupAdmin::where('type', 4)->get();
            $sources = SmSetupAdmin::where('type', 3)->get();

            return view('backEnd.admin.admission_query', ['references' => $references, 'classes' => $classes, 'sources' => $sources]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmAdmissionQueryRequest $smAdmissionQueryRequest)
    {
        /*
        try {
        */
            $smAdmissionQuery = new SmAdmissionQuery();
            $smAdmissionQuery->name = $smAdmissionQueryRequest->name;
            $smAdmissionQuery->phone = $smAdmissionQueryRequest->phone;
            $smAdmissionQuery->email = $smAdmissionQueryRequest->email;
            $smAdmissionQuery->address = $smAdmissionQueryRequest->address;
            $smAdmissionQuery->description = $smAdmissionQueryRequest->description;
            $smAdmissionQuery->date = date('Y-m-d', strtotime($smAdmissionQueryRequest->date));
            $smAdmissionQuery->next_follow_up_date = date('Y-m-d', strtotime($smAdmissionQueryRequest->next_follow_up_date));
            $smAdmissionQuery->assigned = $smAdmissionQueryRequest->assigned;
            $smAdmissionQuery->reference = $smAdmissionQueryRequest->reference;
            $smAdmissionQuery->source = $smAdmissionQueryRequest->source;
            if (moduleStatusCheck('University')) {
                $common = App::make(UnCommonRepositoryInterface::class);
                $data = $common->storeUniversityData($smAdmissionQuery, $smAdmissionQueryRequest);
            } else {
                $smAdmissionQuery->class = $smAdmissionQueryRequest->class;
                $smAdmissionQuery->academic_id = getAcademicId();
            }

            $smAdmissionQuery->no_of_child = $smAdmissionQueryRequest->no_of_child;
            $smAdmissionQuery->created_by = Auth::user()->id;
            $smAdmissionQuery->school_id = Auth::user()->school_id;
            $smAdmissionQuery->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function edit($id)
    {
        /*
        try {
        */
            $data = [];
            $admission_query = SmAdmissionQuery::find($id);
            $classes = SmClass::get();
            $references = SmSetupAdmin::where('type', 4)->get();
            $sources = SmSetupAdmin::where('type', 3)->get();
            if (moduleStatusCheck('University')) {
                $common = App::make(UnCommonRepositoryInterface::class);
                $data = $common->getCommonData($admission_query);
            }

            return view('backEnd.admin.admission_query_edit', ['admission_query' => $admission_query, 'references' => $references, 'classes' => $classes, 'sources' => $sources])->with($data);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmAdmissionQueryRequest $smAdmissionQueryRequest)
    {
        /*
        try {
        */
            if (checkAdmin() == true) {
                $admission_query = SmAdmissionQuery::find($smAdmissionQueryRequest->id);
            } else {
                $admission_query = SmAdmissionQuery::where('created_by', auth()->user()->id)->where('id', $smAdmissionQueryRequest->id)->first();
            }

            $admission_query->name = $smAdmissionQueryRequest->name;
            $admission_query->phone = $smAdmissionQueryRequest->phone;
            $admission_query->email = $smAdmissionQueryRequest->email;
            $admission_query->address = $smAdmissionQueryRequest->address;
            $admission_query->description = $smAdmissionQueryRequest->description;
            $admission_query->date = date('Y-m-d', strtotime($smAdmissionQueryRequest->date));
            $admission_query->next_follow_up_date = date('Y-m-d', strtotime($smAdmissionQueryRequest->next_follow_up_date));
            $admission_query->assigned = $smAdmissionQueryRequest->assigned;
            if ($smAdmissionQueryRequest->reference) {
                $admission_query->reference = $smAdmissionQueryRequest->reference;
            }

            $admission_query->source = $smAdmissionQueryRequest->source;
            if (moduleStatusCheck('University')) {
                $common = App::make(UnCommonRepositoryInterface::class);
                $data = $common->storeUniversityData($admission_query, $smAdmissionQueryRequest);
            } else {
                $admission_query->class = $smAdmissionQueryRequest->class;
            }

            $admission_query->no_of_child = $smAdmissionQueryRequest->no_of_child;
            $admission_query->school_id = Auth::user()->school_id;
            $admission_query->academic_id = getAcademicId();
            $admission_query->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function addQuery($id)
    {
        /*
        try {
        */
            $admission_query = SmAdmissionQuery::where('school_id', auth()->user()->school_id)->where('id', $id)->first();
            $follow_up_lists = SmAdmissionQueryFollowup::where('academic_id', getAcademicId())->where('admission_query_id', $id)->orderby('id', 'DESC')->get();
            $classes = SmClass::where('active_status', 1)->where('academic_id', getAcademicId())->where('school_id', Auth::user()->school_id)->get();
            $references = SmSetupAdmin::where('type', 4)->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();
            $sources = SmSetupAdmin::where('type', 3)->where('active_status', 1)->where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.admin.add_query', ['admission_query' => $admission_query, 'follow_up_lists' => $follow_up_lists, 'references' => $references, 'classes' => $classes, 'sources' => $sources]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function queryFollowupStore(SmAdmissionQueryFollowUpRequest $smAdmissionQueryFollowUpRequest)
    {
        DB::beginTransaction();
        try {
            $admission_query = SmAdmissionQuery::find($smAdmissionQueryFollowUpRequest->id);
            $admission_query->follow_up_date = date('Y-m-d', strtotime($smAdmissionQueryFollowUpRequest->follow_up_date));
            $admission_query->next_follow_up_date = date('Y-m-d', strtotime($smAdmissionQueryFollowUpRequest->next_follow_up_date));
            $admission_query->active_status = $smAdmissionQueryFollowUpRequest->status;
            $admission_query->school_id = Auth::user()->school_id;
            $admission_query->academic_id = getAcademicId();
            $admission_query->save();
            $admission_query->toArray();

            $smAdmissionQueryFollowup = new SmAdmissionQueryFollowup();
            $smAdmissionQueryFollowup->admission_query_id = $admission_query->id;
            $smAdmissionQueryFollowup->response = $smAdmissionQueryFollowUpRequest->response;
            $smAdmissionQueryFollowup->note = $smAdmissionQueryFollowUpRequest->note;
            $smAdmissionQueryFollowup->created_by = Auth::user()->id;
            $smAdmissionQueryFollowup->school_id = Auth::user()->school_id;
            $smAdmissionQueryFollowup->academic_id = getAcademicId();
            $smAdmissionQueryFollowup->save();
            DB::commit();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        } catch (Exception $exception) {
            DB::rollback();
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function deleteFollowUp($id)
    {
        /*try {*/
            SmAdmissionQueryFollowup::destroy($id);

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function delete(Request $request)
    {
        DB::beginTransaction();
        try {
            $admission_query = SmAdmissionQuery::find($request->id);
            SmAdmissionQueryFollowup::where('admission_query_id', $admission_query->id)->delete();
            $admission_query->delete();
            DB::commit();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        } catch (Exception $exception) {
            DB::rollback();
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function admissionQuerySearch(SmAdmissionQuerySearchRequest $smAdmissionQuerySearchRequest)
    {
        /*
        try {
        */
            $requestData = [];
            $date_from = date('Y-m-d', strtotime($smAdmissionQuerySearchRequest->date_from));
            $date_to = date('Y-m-d', strtotime($smAdmissionQuerySearchRequest->date_to));
            $requestData['date_from'] = $smAdmissionQuerySearchRequest->date_from;
            $requestData['date_to'] = $smAdmissionQuerySearchRequest->date_to;
            $requestData['source'] = $smAdmissionQuerySearchRequest->source;
            $requestData['status'] = $smAdmissionQuerySearchRequest->status;

            $date_from = $smAdmissionQuerySearchRequest->date_from;
            $date_to = $smAdmissionQuerySearchRequest->date_to;
            $source_id = $smAdmissionQuerySearchRequest->source;
            $status_id = $smAdmissionQuerySearchRequest->status;
            $classes = SmClass::get();
            $references = SmSetupAdmin::where('type', 4)->get();
            $sources = SmSetupAdmin::where('type', 3)->get();

            return view('backEnd.admin.admission_query', ['requestData' => $requestData, 'references' => $references, 'classes' => $classes, 'sources' => $sources, 'date_from' => $date_from, 'date_to' => $date_to, 'source_id' => $source_id, 'status_id' => $status_id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function admissionQueryDatatable(Request $request)
    {
        /*
        try {
        */
            if ($request->ajax()) {
                $date_from = date('Y-m-d', strtotime($request->date_from));
                $date_to = date('Y-m-d', strtotime($request->date_to));
                $admission_queries = SmAdmissionQuery::query();
                $admission_queries->with('sourceSetup', 'className', 'user', 'referenceSetup')->orderBy('id', 'DESC');
                if ($request->date_from && $request->date_to) {
                    $admission_queries->where('date', '>=', $date_from)->where('date', '<=', $date_to);
                }

                if ($request->source) {
                    $admission_queries->where('source', $request->source);
                }

                if ($request->status) {
                    $admission_queries->where('active_status', $request->status);
                }

                return DataTables::of($admission_queries)
                    ->addIndexColumn()
                    ->addColumn('query_date', function ($row) {
                        return dateConvert(@$row->date);
                    })
                    ->addColumn('follow_up_date', function ($row) {
                        return dateConvert(@$row->follow_up_date);
                    })
                    ->addColumn('next_follow_up_date', function ($row) {
                        return dateConvert(@$row->next_follow_up_date);
                    })
                    ->addColumn('action', function ($row): string {
                        return '<div class="dropdown CRM_dropdown">
                                            <button type="button" class="btn dropdown-toggle" data-toggle="dropdown">'.app('translator')->get('common.select').'</button>
                                            
                                            <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item"
                                            href="'.route('add_query', [@$row->id]).'">'.__('admin.add_query').'</a>'.
                            (userPermission('admission_query_edit') ? '<a class="dropdown-item modalLink" data-modal-size="large-modal"
                                title="'.__('admin.edit_admission_query').'" href="'.route('admission_query_edit', [$row->id]).'">'.app('translator')->get('common.edit').'</a>' : '').

                            (userPermission('admission_query_delete') ? (Config::get('app.app_sync') ? '<span data-toggle="tooltip" title="Disabled For Demo"><a class="dropdown-item" href="#" >'.app('translator')->get('common.disable').'</a></span>' :
                                '<a onclick="deleteQueryModal('.$row->id.');"  class="dropdown-item" href="#" data-toggle="modal" data-target="#deleteAdmissionQueryModal" data-id="'.$row->id.'"  >'.app('translator')->get('common.delete').'</a>') : '').
                            '</div>
                                </div>';
                    })
                    ->rawColumns(['action', 'date'])
                    ->make(true);
            }
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
