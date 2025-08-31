<?php

namespace Modules\DownloadCenter\Http\Controllers;

use App\Role;
use App\SmClassSection;
use App\SmStudent;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Routing\Controller;
use Modules\DownloadCenter\Entities\Content;
use Modules\DownloadCenter\Entities\ContentShareList;
use Modules\DownloadCenter\Http\Requests\ContentShareRequest;

class ContentShareListController extends Controller
{
    public function contentShareList()
    {
        try {
            if (auth()->user()->role_id === 2) {
                $student = SmStudent::where('user_id', auth()->user()->id)->with('studentRecord')->first();
                $allSharedContents = ContentShareList::get();
                $sharedContents = [];
                foreach ($allSharedContents as $allSharedContent) {
                    if (
                        $allSharedContent->send_type === 'G'
                        && in_array(2, $allSharedContent->gr_role_ids ?? [])
                    ) {
                        $sharedContents[] = $allSharedContent;
                    }

                    if (
                        $allSharedContent->send_type === 'I'
                        && in_array(auth()->user()->id, $allSharedContent->ind_user_ids ?? [])
                    ) {
                        $sharedContents[] = $allSharedContent;
                    }

                    if (
                        $allSharedContent->send_type === 'C'
                        && $allSharedContent->class_id === $student->studentRecord->class_id
                        && in_array($student->studentRecord->section_id, $allSharedContent->section_ids ?? [])
                    ) {
                        $sharedContents[] = $allSharedContent;
                    }
                }
            } else {
                $sharedContents = ContentShareList::with('user')->get();
            }

            return view('downloadcenter::contentShareList.contentShareList', ['sharedContents' => $sharedContents]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function contentShareListSave(ContentShareRequest $contentShareRequest)
    {
        try {
            $contentShareList = new ContentShareList();
            $contentShareList->title = $contentShareRequest->title;
            $contentShareList->share_date = date('Y-m-d', strtotime($contentShareRequest->shareDate));
            $contentShareList->valid_upto = date('Y-m-d', strtotime($contentShareRequest->validUpto));
            $contentShareList->description = $contentShareRequest->description;
            $contentShareList->send_type = $contentShareRequest->selectTab;
            $contentShareList->content_ids = $contentShareRequest->content_ids;
            if ($contentShareRequest->selectTab === 'G') {
                $contentShareList->gr_role_ids = $contentShareRequest->role;
            }

            if ($contentShareRequest->selectTab === 'I') {
                $contentShareList->ind_user_ids = $contentShareRequest->individual_content_user;
            }

            if ($contentShareRequest->selectTab === 'C') {
                $contentShareList->class_id = $contentShareRequest->class_id;
                if ($contentShareRequest->section_ids) {
                    $contentShareList->section_ids = $contentShareRequest->section_ids;
                }
            }

            $contentShareList->shared_by = auth()->user()->id;
            $contentShareList->save();

            return response()->success(['success' => true]);
        } catch (Exception $exception) {
            return response()->json(['error' => $exception]);
        }
    }

    public function contentGenarteUrlSave(ContentShareRequest $contentShareRequest)
    {
        try {
            $contentShareList = new ContentShareList();
            $contentShareList->title = $contentShareRequest->title;
            $contentShareList->share_date = date('Y-m-d', strtotime($contentShareRequest->shareDate));
            $contentShareList->valid_upto = date('Y-m-d', strtotime($contentShareRequest->validUpto));
            $contentShareList->content_ids = $contentShareRequest->content_ids;
            $contentShareList->send_type = 'P';
            $contentShareList->url = generateRandomString(30);
            $contentShareList->shared_by = auth()->user()->id;
            $contentShareList->save();

            return response()->success(['success' => true]);
        } catch (Exception $exception) {
            return response()->json(['error' => $exception]);
        }
    }

    public function contentShareListDelete($id)
    {
        try {
            $content = ContentShareList::where('id', $id)->first();
            $content->delete();
            Toastr::success('Deleted successfully', 'Success');

            return redirect()->route('download-center.content-share-list');
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function contentShareLinkModal($url)
    {
        try {
            $sharedLink = ContentShareList::find($url);

            return view('downloadcenter::contentShareList.shared_content_modal', ['sharedLink' => $sharedLink]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function contentViewLinkModal($id)
    {
        try {
            $viewContent = ContentShareList::find($id);
            $contents = Content::whereIn('id', $viewContent->content_ids)->get();
            $roles = ($viewContent->gr_role_ids) ? Role::whereIn('id', $viewContent->gr_role_ids)->get() : null;
            $individuals = ($viewContent->ind_user_ids) ? User::whereIn('id', $viewContent->ind_user_ids)->get() : null;
            $classSections = ($viewContent->class_id) ? SmClassSection::where('class_id', $viewContent->class_id)
                ->when($viewContent->section_ids, function ($q) use ($viewContent): void {
                    $q->whereIn('section_id', $viewContent->section_ids);
                })
                ->with('className', 'sectionName')
                ->get() : null;

            return view('downloadcenter::contentShareList.view_content_modal', ['viewContent' => $viewContent, 'contents' => $contents, 'roles' => $roles, 'classSections' => $classSections, 'individuals' => $individuals]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function contentShareLink($url)
    {
        try {
            $sharedContent = ContentShareList::where('url', $url)->first();
            $contents = Content::whereIn('id', $sharedContent->content_ids)->get();

            return view('downloadcenter::contentShareList.sharedFilePage', ['sharedContent' => $sharedContent, 'contents' => $contents]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function parentContentShareList($id)
    {
        try {
            $student_detail = SmStudent::where('id', $id)->with('studentRecord')->first();
            $records = studentRecords(null, $student_detail->id)->get();
            $allSharedContents = ContentShareList::get();
            $sharedContents = [];
            foreach ($allSharedContents as $allSharedContent) {
                if (
                    $allSharedContent->send_type === 'G'
                    && in_array(2, $allSharedContent->gr_role_ids ?? [])
                ) {
                    $sharedContents[] = $allSharedContent;
                }

                if (
                    $allSharedContent->send_type === 'I'
                    && in_array($student_detail->user_id, $allSharedContent->ind_user_ids ?? [])
                ) {
                    $sharedContents[] = $allSharedContent;
                }

                if (
                    $allSharedContent->send_type === 'C'
                    && $allSharedContent->class_id === $student_detail->studentRecord->class_id
                    && in_array($student_detail->studentRecord->section_id, $allSharedContent->section_ids ?? [])
                ) {
                    $sharedContents[] = $allSharedContent;
                }
            }

            return view('downloadcenter::contentShareList.parentContentShareList', ['sharedContents' => $sharedContents, 'student_detail' => $student_detail, 'records' => $records]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
}
