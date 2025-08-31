<?php

namespace App\Http\Controllers\Admin\OnlineExam;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OnlineExam\SmQuestionGroupRequest;
use App\SmQuestionGroup;
use App\tableList;
use Exception;
use Illuminate\Support\Facades\Auth;

class SmQuestionGroupController extends Controller
{


    public function index()
    {
        /*
        try {
        */
            $groups = SmQuestionGroup::select('id', 'title')->get();

            return view('backEnd.examination.question_group', ['groups' => $groups]);
        /*
        } catch (Exception $exception) {
            toastrError();

            return redirect()->back();
        }
        */
    }

    public function store(SmQuestionGroupRequest $smQuestionGroupRequest)
    {
        /*
        try {
        */
            $smQuestionGroup = new SmQuestionGroup();
            $smQuestionGroup->title = $smQuestionGroupRequest->title;
            $smQuestionGroup->school_id = Auth::user()->school_id;
            $smQuestionGroup->academic_id = getAcademicId();
            $smQuestionGroup->save();
            toastrSuccess();

            return redirect()->back();
/*
        } catch (Exception $exception) {
            toastrError();

            return redirect()->back();
        }
        */
    }

    public function show($id)
    {
        /*
        try {
        */
            $groups = SmQuestionGroup::select('id', 'title')->get();
            $group = $groups->firstWhere('id', $id);

            return view('backEnd.examination.question_group', ['groups' => $groups, 'group' => $group]);
        /*
        } catch (Exception $exception) {
            toastrError();

            return redirect()->back();
        }
        */
    }

    public function update(SmQuestionGroupRequest $smQuestionGroupRequest, $id)
    {
        /*
        try {
        */
            $group = SmQuestionGroup::find($smQuestionGroupRequest->id);
            $group->title = $smQuestionGroupRequest->title;
            $group->save();

            toastrSuccess();

            return redirect('question-group');
        /*
        } catch (Exception $exception) {
            toastrError();

            return redirect()->back();
        }
        */
    }

    public function destroy($id)
    {
        $tables = tableList::getTableList('q_group_id', $id);

        /*
        try {
        */
            if ($tables == null) {
                SmQuestionGroup::destroy($id);

                toastrSuccess();

                return redirect('question-group');

            }

            $msg = 'This data already used in  : '.$tables.' Please remove those data first';

            toastrError($msg, 'Failed');

            return redirect()->back();

        /*
        } catch (Exception $exception) {
            $msg = 'This data already used in  : '.$tables.' Please remove those data first';
            toastrError($msg, 'Failed');

            return redirect()->back();
        }
        */
    }
}
