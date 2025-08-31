<?php

namespace App\Http\Controllers;

use App\ApiBaseMethod;
use App\SmRoute;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SmRouteController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        try {
            $routes = SmRoute::where('school_id', Auth::user()->school_id)->orderby('id', 'DESC')->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($routes, null);
            }

            return view('backEnd.transport.route', ['routes' => $routes]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'required|max:200',
            'far' => 'required',
        ]);

        // school wise uquine validation
        $is_duplicate = SmRoute::where('school_id', Auth::user()->school_id)->where('title', $request->title)->first();
        if ($is_duplicate) {
            Toastr::error('Duplicate name found!', 'Failed');

            return redirect()->back()->withErrors($validator)->withInput();
        }

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $smRoute = new SmRoute();
            $smRoute->title = $request->title;
            $smRoute->far = $request->far;
            $smRoute->school_id = Auth::user()->school_id;
            $smRoute->academic_id = getAcademicId();
            $result = $smRoute->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Route has been created successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect()->back();
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

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
    public function show(Request $request, $id)
    {

        try {
            // $route = SmRoute::find($id);
            if (checkAdmin() == true) {
                $route = SmRoute::find($id);
            } else {
                $route = SmRoute::where('id', $id)->where('school_id', Auth::user()->school_id)->first();
            }

            $routes = SmRoute::where('school_id', Auth::user()->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['route'] = $route->toArray();
                $data['routes'] = $routes->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.transport.route', ['route' => $route, 'routes' => $routes]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function update(Request $request, $id)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'title' => 'required|max:200',
            'far' => 'required',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // school wise uquine validation
        $is_duplicate = SmRoute::where('school_id', Auth::user()->school_id)->where('title', $request->title)->where('id', '!=', $request->id)->first();
        if ($is_duplicate) {
            Toastr::error('Duplicate name found!', 'Failed');

            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            // $route = SmRoute::find($request->id);
            if (checkAdmin() == true) {
                $route = SmRoute::find($request->id);
            } else {
                $route = SmRoute::where('id', $request->id)->where('school_id', Auth::user()->school_id)->first();
            }

            $route->title = $request->title;
            $route->far = $request->far;
            $result = $route->save();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Route has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

            }

            if ($result) {
                Toastr::success('Operation successful', 'Success');

                return redirect('transport-route');
            }

            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();

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
    public function destroy(Request $request, $id)
    {

        try {
            $tables = \App\tableList::getTableList('route_id', $id);
            try {
                if ($tables == null) {
                    // $route = SmRoute::destroy($id);
                    if (checkAdmin() == true) {
                        $route = SmRoute::destroy($id);
                    } else {
                        $route = SmRoute::where('id', $id)->where('school_id', Auth::user()->school_id)->delete();
                    }

                    if ($route) {
                        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                            if ($route) {
                                return ApiBaseMethod::sendResponse(null, 'Route has been deleted successfully');
                            }

                            return ApiBaseMethod::sendError('Something went wrong, please try again.');

                        }

                        if ($route) {
                            Toastr::success('Operation successful', 'Success');

                            return redirect()->back();
                        }

                        Toastr::error('Operation Failed', 'Failed');

                        return redirect()->back();

                    }

                    Toastr::error('Operation Failed', 'Failed');

                    return redirect()->back();

                }

                $msg = 'This data already used in  : '.$tables.' Please remove those data first';
                Toastr::error($msg, 'Failed');

                return redirect()->back();

            } catch (\Illuminate\Database\QueryException $e) {

                $msg = 'This data already used in  : '.$tables.' Please remove those data first';
                Toastr::error($msg, 'Failed');

                return redirect()->back();
            } catch (Exception $e) {
                Toastr::error('Operation Failed', 'Failed');

                return redirect()->back();
            }
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }
}
