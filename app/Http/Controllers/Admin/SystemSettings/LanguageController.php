<?php

namespace App\Http\Controllers\Admin\SystemSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GeneralSettings\SmLanguageRequest;
use App\Language;
use App\tableList;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{


    public function index()
    {
        $languages = Language::where('school_id', Auth::user()->school_id)->get();

        return view('backEnd.systemSettings.language', ['languages' => $languages]);
    }

    public function create(): void
    {
        //
    }

    public function store(SmLanguageRequest $request)
    {
        if (config('app.app_sync') == true) {
            Toastr::error('Disabled for demo mode', 'Failed');

            return redirect()->route('manage-currency');
        }
        /*
        try {
        */
            $s = new Language();
            $s->name = $request->name;
            $s->code = $request->code;
            $s->native = $request->native;
            $s->rtl = $request->rtl;
            $s->school_id = Auth::user()->school_id;
            $s->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('language-list');
        /*
        } catch (\Exception $e) {
           
            Toastr::error('Operation Failed', 'Failed');

            return redirect('language-list');
        }
        */
    }

    public function show($id)
    {
        if (config('app.app_sync') == true) {
            Toastr::error('Disabled for demo mode', 'Failed');

            return redirect()->route('manage-currency');
        }
        /*
        try {
        */
            $languages=Language::where('school_id',Auth::user()->school_id)->get();
            $editData = $languages->where('id',$id)->first();
            return view('backEnd.systemSettings.language',compact('languages','editData'));

        /*
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function edit($id): void
    {
        //
    }

    public function update(SmLanguageRequest $request)
    {
        if (config('app.app_sync')) {
            Toastr::error('Restricted in demo mode');

            return back();
        }
        /*
        try {
        */
            $s = Language::findOrfail($request->id);
            $s->name = $request->name;
            $s->code = $request->code;
            $s->native = $request->native;
            $s->rtl = $request->rtl;
            $s->school_id = Auth::user()->school_id;
            $s->update();

            Toastr::success('Operation successful', 'Success');

            return redirect('language-list');
        /*
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect('language-list');
        }
        */
    }

    public function destroy($id)
    {
        /*
        try {
        */
            $tables = tableList::getTableList('lang_id', $id);
            if ($tables == null || $tables == '' || $tables == '0') {
                $s = Language::findOrfail($id);
                $s->delete();
                Toastr::success('Operation successful', 'Success');

                return redirect('language-list');
            }

            $msg = 'This data already used in  : '.$tables.' Please remove those data first';
            Toastr::error($msg, 'Failed');

            return redirect()->back();

            
        /*
        } catch (\Exception $e) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect('language-list');
        }
        */
    }
}
