<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\Http\Controllers\Controller;
use App\Models\SmCustomField;
use App\Models\SmDonor;
use App\SmBaseSetup;
use App\Traits\CustomFields;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SmDonorController extends Controller
{
    use CustomFields;

    public function index()
    {
        /*
        try {
        */
        $data['custom_fields'] = SmCustomField::where('form_name', 'donor_registration')->where('school_id', Auth::user()->school_id)->get();
        $data['religions'] = SmBaseSetup::where('base_group_id', '=', '2')->get(['id', 'base_setup_name']);
        $data['blood_groups'] = SmBaseSetup::where('base_group_id', '=', '3')->get(['id', 'base_setup_name']);
        $data['genders'] = SmBaseSetup::where('base_group_id', '=', '1')->get(['id', 'base_setup_name']);
        $data['donors'] = SmDonor::where('school_id', app('school')->id)->get();

        return view('backEnd.frontSettings.donor.donor', $data);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        /*
        try {
        */
        $destination = 'public/uploads/theme/edulia/donor/';
        $image = fileUpload($request->photo, $destination);
        $smDonor = new SmDonor();
        $smDonor->full_name = $request->name;
        $smDonor->profession = $request->profession;
        $smDonor->date_of_birth = date('Y-m-d', strtotime($request->date_of_birth));
        $smDonor->email = $request->email;
        $smDonor->mobile = $request->mobile;
        $smDonor->photo = $image;
        $smDonor->age = $request->age;
        $smDonor->show_public = $request->show_public;
        $smDonor->current_address = $request->current_address;
        $smDonor->permanent_address = $request->permanent_address;
        $smDonor->bloodgroup_id = $request->blood_group;
        $smDonor->religion_id = $request->religion;
        $smDonor->gender_id = $request->gender;
        $smDonor->school_id = app('school')->id;
        if ($request->customF) {
            $dataImage = $request->customF;
            foreach ($dataImage as $label => $field) {
                if (is_object($field) && $field !== '') {
                    $dataImage[$label] = fileUpload($field, 'public/uploads/customFields/');
                }
            }

            $smDonor->custom_field_form_name = 'donor_registration';
            $smDonor->custom_field = json_encode($dataImage, true);
        }

        $result = $smDonor->save();

        Toastr::success('Operation successful', 'Success');

        return redirect()->route('donor');
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
        $data['donors'] = SmDonor::where('school_id', app('school')->id)->get();
        $data['add_donor'] = SmDonor::find($id);
        $data['religions'] = SmBaseSetup::where('base_group_id', '=', '2')->get(['id', 'base_setup_name']);
        $data['blood_groups'] = SmBaseSetup::where('base_group_id', '=', '3')->get(['id', 'base_setup_name']);
        $data['genders'] = SmBaseSetup::where('base_group_id', '=', '1')->get(['id', 'base_setup_name']);
        $data['custom_filed_values'] = json_decode($data['add_donor']->custom_field);
        $data['custom_fields'] = SmCustomField::where('form_name', 'donor_registration')->where('school_id', Auth::user()->school_id)->get();

        return view('backEnd.frontSettings.donor.donor', $data);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(Request $request)
    {
        $add_donor = SmDonor::find($request->id);
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $validator = Validator::make($request->all(), $this->generateValidateRules('donor_registration', $add_donor));
        if ($validator->fails()) {
            $errors = $validator->errors();
            foreach ($errors->all() as $error) {
                Toastr::error(str_replace('custom f.', '', $error), 'Failed');
            }

            return redirect()->back()->withInput();
        }
        /*
        try {
        */
        $destination = 'public/uploads/theme/edulia/donor/';
        $donor = SmDonor::find($request->id);
        $donor->full_name = $request->name;
        $donor->profession = $request->profession;
        $donor->date_of_birth = date('Y-m-d', strtotime($request->date_of_birth));
        $donor->email = $request->email;
        $donor->mobile = $request->mobile;
        $donor->photo = fileUpdate($donor->photo, $request->photo, $destination);
        $donor->age = $request->age;
        $donor->show_public = $request->show_public;
        $donor->current_address = $request->current_address;
        $donor->permanent_address = $request->permanent_address;
        $donor->bloodgroup_id = $request->blood_group;
        $donor->religion_id = $request->religion;
        $donor->gender_id = $request->gender;
        if ($request->customF) {
            $dataImage = $request->customF;
            foreach ($dataImage as $label => $field) {
                if (is_object($field) && $field !== '') {
                    $key = '';
                    $maxFileSize = generalSetting()->file_size;
                    $file = $field;
                    $fileSize = filesize($file);
                    $fileSizeKb = ($fileSize / 1000000);
                    if ($fileSizeKb >= $maxFileSize) {
                        Toastr::error('Max upload file size '.$maxFileSize.' Mb is set in system', 'Failed');

                        return redirect()->back();
                    }

                    $file = $field;
                    $key = $file->getClientOriginalName();
                    $file->move('public/uploads/customFields/', $key);
                    $dataImage[$label] = 'public/uploads/customFields/'.$key;
                }
            }

            $donor->custom_field_form_name = 'donor_registration';
            $donor->custom_field = json_encode($dataImage, true);
        }

        $result = $donor->save();

        Toastr::success('Operation successful', 'Success');

        return redirect()->route('donor');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function deleteModal($id)
    {
        /*
        try {
        */
        $donor = SmDonor::find($id);

        return view('backEnd.frontSettings.donor.donor_delete_modal', ['donor' => $donor]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function delete($id)
    {
        /*
        try {
        */
        $donor = SmDonor::find($id);
        if ($donor && file_exists($donor->photo)) {
            unlink($donor->photo);
        }

        $donor->delete();
        Toastr::success('Deleted successfully', 'Success');

        return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
