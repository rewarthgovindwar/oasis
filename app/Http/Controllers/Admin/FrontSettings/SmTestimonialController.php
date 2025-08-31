<?php

namespace App\Http\Controllers\Admin\FrontSettings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FrontSettings\SmTestimonialRequest;
use App\SmTestimonial;
use App\User;
use Brian2694\Toastr\Facades\Toastr;
use Exception;

class SmTestimonialController extends Controller
{


    public function index()
    {
        /*
        try {
        */
            $testimonial = SmTestimonial::where('school_id', app('school')->id)->get();

            return view('backEnd.frontSettings.testimonial.testimonial_page', ['testimonial' => $testimonial]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmTestimonialRequest $smTestimonialRequest)
    {
        /*
        try {
        */
            $destination = 'public/uploads/testimonial/';
            $image = fileUpload($smTestimonialRequest->image, $destination);
            $smTestimonial = new SmTestimonial();
            $smTestimonial->name = $smTestimonialRequest->name;
            $smTestimonial->designation = $smTestimonialRequest->designation;
            $smTestimonial->institution_name = $smTestimonialRequest->institution_name;
            $smTestimonial->image = $image;
            $smTestimonial->description = $smTestimonialRequest->description;
            $smTestimonial->star_rating = $smTestimonialRequest->rating;
            $smTestimonial->school_id = app('school')->id;
            $result = $smTestimonial->save();

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
            $testimonial = SmTestimonial::where('school_id', app('school')->id)->get();
            $add_testimonial = SmTestimonial::find($id);

            return view('backEnd.frontSettings.testimonial.testimonial_page', ['add_testimonial' => $add_testimonial, 'testimonial' => $testimonial]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmTestimonialRequest $smTestimonialRequest)
    {

        /*
        try {
        */

            $destination = 'public/uploads/testimonial/';
            $testimonial = SmTestimonial::find($smTestimonialRequest->id);
            $testimonial->name = $smTestimonialRequest->name;
            $testimonial->designation = $smTestimonialRequest->designation;
            $testimonial->institution_name = $smTestimonialRequest->institution_name;
            $testimonial->school_id = app('school')->id;
            $testimonial->image = fileUpdate($testimonial->image, $smTestimonialRequest->image, $destination);
            $testimonial->description = $smTestimonialRequest->description;
            $testimonial->star_rating = $smTestimonialRequest->rating;
            $result = $testimonial->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('testimonial');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function testimonialDetails($id)
    {

        /*
        try {
        */
            $testimonial = SmTestimonial::find($id);

            return view('backEnd.frontSettings.testimonial.testimonial_details', ['testimonial' => $testimonial]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function forDeleteTestimonial($id)
    {

        /*
        try {
        */
            return view('backEnd.frontSettings.testimonial.delete_modal', ['id' => $id]);
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
            $testimonial = SmTestimonial::find($id);
            if (! empty($testimonial->image) && file_exists($testimonial->image)) {
                unlink($testimonial->image);
            }

            $testimonial->delete();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }
}
