<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\SmRoute;
use Brian2694\Toastr\Facades\Toastr;
use DB;
use Exception;
use Illuminate\Http\Request;
use Validator;

class ApiSmRouteController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        try {
            $routes = SmRoute::where('school_id', 1)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($routes, null);
            }

            return view('backEnd.transport.route', ['routes' => $routes]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): void
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'required|max:200|unique:sm_routes,title',
            'far' => 'required|integer',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendError('Validation Error.', $validator->errors());
            }

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {

            $creator_info = DB::table('sm_staffs')->where('user_id', $request->user_id)->first();

            $smRoute = new SmRoute();
            $smRoute->title = $request->title;
            $smRoute->far = $request->far;
            $smRoute->school_id = $creator_info->school_id;
            $result = $smRoute->save();

            if ($result) {
                return ApiBaseMethod::sendResponse(null, 'Route has been created successfully');
            }

            return ApiBaseMethod::sendError('Something went wrong, please try again');

        } catch (Exception $exception) {
            return ApiBaseMethod::sendError('Something went wrong, please try again');
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
            $route = SmRoute::find($id);
            $routes = SmRoute::all();

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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     */
    public function edit($id): void
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'title' => 'required|max:200|unique:sm_routes,title,'.$id,
                'far' => 'required',
                'id' => 'required',
            ]);
        } else {
            $validator = Validator::make($input, [
                'title' => 'required|max:200|unique:sm_routes,title,'.$id,
                'far' => 'required',
            ]);
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
            $route = SmRoute::find($request->id);
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
                $route = SmRoute::destroy($id);
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

            } catch (\Illuminate\Database\QueryException $e) {

                $msg = 'This data already used in  : '.$tables.' Please remove those data first';
                Toastr::error('This item already used', 'Failed');

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
