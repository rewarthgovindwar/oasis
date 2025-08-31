<?php

namespace Modules\BehaviourRecords\Http\Controllers;

use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\BehaviourRecords\Entities\AssignIncident;
use Modules\BehaviourRecords\Entities\AssignIncidentComment;
use Modules\BehaviourRecords\Http\Requests\IncidentCommentRequest;

class BehaviourCommentController extends Controller
{
    public function incidentComment($id)
    {
        try {
            $incident = AssignIncident::where('id', $id)->with('studentRecord.studentDetail', 'studentRecord.incidents')->first();

            return view('behaviourrecords::comment.behaviour_comment', ['incident' => $incident]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function getIncidentComment($id)
    {
        try {
            $incidentComments = AssignIncidentComment::where('incident_id', $id)->with('user', 'incident', 'user.roles')->get();

            return view('behaviourrecords::comment.behaviour_comment_list', ['incidentComments' => $incidentComments]);
        } catch (Exception $exception) {
            return response()->json(['message' => $exception->getMessage()]);
        }
    }

    public function incidentCommentSave(IncidentCommentRequest $incidentCommentRequest)
    {
        try {
            $assignIncidentComment = new AssignIncidentComment();
            $assignIncidentComment->user_id = Auth::user()->id;
            $assignIncidentComment->comment = $incidentCommentRequest->comment;
            $assignIncidentComment->incident_id = $incidentCommentRequest->incident_id;
            $assignIncidentComment->save();

            return response()->json(['message' => 'Successful']);
        } catch (Exception $exception) {
            return response()->json(['error' => $exception]);
        }
    }
}
