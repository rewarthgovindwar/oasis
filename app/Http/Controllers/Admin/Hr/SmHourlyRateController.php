<?php

namespace App\Http\Controllers\Admin\Hr;

use App\Http\Controllers\Controller;
use App\SmHourlyRate;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;

class SmHourlyRateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        /*
        try {
        */
            $hourly_rates = SmHourlyRate::select(['grade', 'id', 'rate'])->all();

            return view('backEnd.humanResource.hourly_rate', ['hourly_rates' => $hourly_rates]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(Request $request)
    {
        $request->validate([
            'grade' => 'required',
            'rate' => 'required',
        ]);

        /*
        try {
        */
            $smHourlyRate = new SmHourlyRate();
            $smHourlyRate->grade = $request->grade;
            $smHourlyRate->rate = $request->rate;
            $smHourlyRate->academic_id = getAcademicId();
            $result = $smHourlyRate->save();
            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
                // return redirect()->back()->with('message-success', 'Rate has been created successfully');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
            // return redirect()->back()->with('message-danger', 'Something went wrong, please try again');

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        /*
        try {
        */
            $hourly_rate = SmHourlyRate::find($id);
            $hourly_rates = SmHourlyRate::select(['grade', 'id', 'rate'])->all();

            return view('backEnd.humanResource.hourly_rate', ['hourly_rates' => $hourly_rates, 'hourly_rate' => $hourly_rate]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'grade' => 'required',
            'rate' => 'required',
        ]);
        /*
        try {
        */
            $hourly_rate = SmHourlyRate::find($request->id);
            $hourly_rate->grade = $request->grade;
            $hourly_rate->rate = $request->rate;
            $result = $hourly_rate->save();
            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect('hourly-rate');
                // return redirect('hourly-rate')->with('message-success', 'Rate has been updated successfully');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
            // return redirect()->back()->with('message-danger', 'Something went wrong, please try again');

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        /*
        try {
        */
            $hourly_rate = SmHourlyRate::destroy($id);
            if ($hourly_rate) {
                Toastr::success('Operation successful', 'Success');

                return redirect('hourly-rate');
                // return redirect('hourly-rate')->with('message-success-delete', 'Rate has been deleted successfully');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
            // return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');

        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
