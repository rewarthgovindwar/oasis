<?php

namespace App\Http\Controllers\Admin\Communicate;

use App\GlobalVariable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Communicate\EventRequest;
use App\Models\User;
use App\SmEvent;
use App\Traits\NotificationSend;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Modules\RolePermission\Entities\InfixRole;
use Yajra\DataTables\Facades\DataTables;

class SmEventController extends Controller
{
    use NotificationSend;

    public function index()
    {
        /*
        try {
        */
        $data = $this->indexData();

        return view('backEnd.events.eventsList', $data);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(EventRequest $eventRequest)
    {
        /*
        try {
        */
        $destination = 'public/uploads/events/';

        $smEvent = new SmEvent();
        $smEvent->event_title = $eventRequest->event_title;
        $smEvent->role_ids = json_encode($eventRequest->role_ids);
        $smEvent->event_des = $eventRequest->event_des;
        $smEvent->event_location = $eventRequest->event_location;
        $smEvent->from_date = date('Y-m-d', strtotime($eventRequest->from_date));
        $smEvent->to_date = date('Y-m-d', strtotime($eventRequest->to_date));
        $smEvent->url = $eventRequest->url;
        $smEvent->created_by = auth()->user()->id;
        $smEvent->uplad_image_file = fileUpload($eventRequest->upload_file_name, $destination);
        $smEvent->school_id = auth()->user()->school_id;
        if (moduleStatusCheck('University')) {
            $smEvent->un_academic_id = getAcademicId();
        } else {
            $smEvent->academic_id = getAcademicId();
        }

        $smEvent->save();
        $data['event'] = $eventRequest->event_title;
        foreach ($eventRequest->role_ids as $role_id) {
            $userIds = User::where('role_id', $role_id)->where('active_status', 1)->pluck('id')->toArray();
            if ($role_id == 4) {
                $this->sent_notifications('Event', $userIds, $data, ['Teacher']);
            }

            if ($role_id == 3) {
                $this->sent_notifications('Event', $userIds, $data, ['Parent']);
            }

            if ($role_id == 2) {
                $this->sent_notifications('Event', $userIds, $data, ['Student']);
            }

            if ($role_id == GlobalVariable::isAlumni()) {
                $this->sent_notifications('Event', $userIds, $data, ['Alumni']);
            }
        }

        if ($eventRequest->data_type == 'ajax') {
            return response()->json($smEvent);
        }

        Toastr::success('Operation successful', 'Success');

        return redirect()->back();

        /*
        } catch (\Exception $e) {
            if($request->data_type == 'ajax'){
                return response()->json(['error' => $e]);
            }else{
                Toastr::error('Operation Failed', 'Failed');
                return redirect()->back();
            }
        }
        */
    }

    public function edit($id)
    {
        /*
        try {
        */
        $data = $this->indexData();
        $data['editData'] = SmEvent::find($id);

        return view('backEnd.events.eventsList', $data);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(EventRequest $eventRequest, $id)
    {
        /*
        try {
        */
        $destination = 'public/uploads/events/';

        $events = SmEvent::find($id);
        $events->event_title = $eventRequest->event_title;
        $events->role_ids = json_encode($eventRequest->role_ids);
        $events->event_des = $eventRequest->event_des;
        $events->event_location = $eventRequest->event_location;
        $events->from_date = date('Y-m-d', strtotime($eventRequest->from_date));
        $events->to_date = date('Y-m-d', strtotime($eventRequest->to_date));
        $events->url = $eventRequest->url;
        $events->updated_by = auth()->user()->id;
        $events->uplad_image_file = fileUpdate($events->uplad_image_file, $eventRequest->upload_file_name, $destination);
        $events->update();

        Toastr::success('Operation successful', 'Success');

        return redirect('event');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteEvent($id)
    {
        /*
        try {
        */
        $data = SmEvent::find($id);
        if ($data->uplad_image_file !== '') {
            $path = $data->uplad_image_file;
            if (file_exists($path)) {
                unlink($path);
            }
        }

        $data->delete();
        Toastr::success('Operation Successful', 'Success');

        return redirect('event');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteEventView(Request $request, $id)
    {
        /*
        try {
        */
        return view('backEnd.events.deleteEventView', ['id' => $id]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function getAllEventList()
    {
        /*
        try {
        */
        $events = SmEvent::when(auth()->user()->roles->id !== 1, function ($s): void {
            $s->whereJsonContains('role_ids', (string) auth()->user()->roles->id);
        });

        return DataTables::of($events)
            ->addIndexColumn()
            ->addColumn('title', function ($event) {
                return $event->event_title;
            })
            ->addColumn('name', function ($event) {
                $roleName = [];
                if ($event->role_ids) {
                    foreach (json_decode($event->role_ids) as $roleData) {
                        $roleName[] = $this->roleName($roleData)->name;
                    }
                }

                return $roleName;
            })
            ->filterColumn('name', function ($query, string $keyword): void {
                $query->where('event_title', 'like', '%'.$keyword.'%')
                    ->orWhere('event_location', 'like', '%'.$keyword.'%');
            })
            ->addColumn('date', function ($event): string {
                return dateConvert($event->from_date).'-'.dateConvert($event->to_date);
            })
            ->addColumn('location', function ($event) {
                return $event->event_location;
            })
            ->addColumn('action', function ($event) {
                return view('backEnd.events._eventAction', ['event' => $event]);
            })
            ->rawColumns(['action'])
            ->toJson();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function newDesign()
    {
        return view('backEnd.events.newDesign');
    }

    private function indexData()
    {
        $data['events'] = SmEvent::get();
        $data['roles'] = InfixRole::where('is_saas', 0)->where(function ($q): void {
            $q->where('school_id', auth()->user()->school_id)->orWhere('type', 'System');
        })
            ->whereNotIn('id', [1])
            ->get();

        return $data;
    }

    private function roleName($roleId)
    {
        return InfixRole::find($roleId, ['name']);
    }
}
