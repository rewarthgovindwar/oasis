<?php

namespace App\Http\Controllers;

use App\SmInstruction;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmInstructionController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        try {
            $instructions = SmInstruction::where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.examination.instruction', ['instructions' => $instructions]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|unique:sm_instructions',
            'description' => 'required',
        ]);
        try {
            $smInstruction = new SmInstruction();
            $smInstruction->title = $request->title;
            $smInstruction->description = $request->description;
            $smInstruction->school_id = Auth::user()->school_id;
            $smInstruction->academic_id = getAcademicId();
            $result = $smInstruction->save();
            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
                // return redirect()->back()->with('message-success', 'Instruction has been created successfully');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
            // return redirect()->back()->with('message-danger', 'Something went wrong, please try again');

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        try {
            $instruction = SmInstruction::find($id);
            $instructions = SmInstruction::where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.examination.instruction', ['instruction' => $instruction, 'instructions' => $instructions]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|unique:sm_instructions,title,'.$request->id,
            'description' => 'required',
        ]);
        try {
            $instruction = SmInstruction::find($request->id);
            $instruction->title = $request->title;
            $instruction->description = $request->description;
            $result = $instruction->save();
            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect('instruction');
                // return redirect('instruction')->with('message-success', 'Instruction has been updated successfully');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
            // return redirect()->back()->with('message-danger', 'Something went wrong, please try again');

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        try {
            $instruction = SmInstruction::destroy($id);
            if ($instruction) {
                Toastr::success('Operation successful', 'Success');

                return redirect('assign-vehicle');
                // return redirect('assign-vehicle')->with('message-success-delete', 'Instruction has been deleted successfully');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
            // return redirect()->back()->with('message-danger-delete', 'Something went wrong, please try again');

        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
}
