<?php

namespace App\Http\Controllers\api;

use App\ApiBaseMethod;
use App\Http\Controllers\Controller;
use App\SmRoute;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Validator;

class SaasRouteController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $input = $request->all();

        $validator = Validator::make($input, [
            'school_id' => 'required|integer',
        ]);
        if ($validator->fails() && ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Validation Error.', $validator->errors());

        }

        try {
            $routes = SmRoute::withoutGlobalScopes()->where('school_id', '=', $request->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                return ApiBaseMethod::sendResponse($routes, null);
            }

            return view('backEnd.transport.route', ['routes' => $routes]);
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
    }

    public function create(): void
    {
        //
    }

    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'title' => 'required|max:200|unique:sm_routes,title',
            'far' => 'required|integer',
            'school_id' => 'required|integer',
            'created_by' => 'required|integer',
        ]);

        if ($validator->fails() && ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Validation Error.', $validator->errors());

        }

        try {
            $smRoute = new SmRoute();
            $smRoute->title = $request->title;
            $smRoute->far = $request->far;
            $smRoute->school_id = $request->school_id;
            $smRoute->created_by = $request->created_by;
            $result = $smRoute->save();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Route has been created successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

            }
        } catch (Exception $exception) {

        }

        return null;
    }

    public function show(Request $request, $id)
    {

        try {
            $route = SmRoute::find($id);
            $routes = SmRoute::where('school_id', '=', $request->school_id)->get();

            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                $data = [];
                $data['route'] = $route->toArray();
                $data['routes'] = $routes->toArray();

                return ApiBaseMethod::sendResponse($data, null);
            }

            return view('backEnd.transport.route', ['route' => $route, 'routes' => $routes]);
        } catch (Exception $exception) {

        }

        return null;
    }

    public function edit($id): void
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $input = $request->all();
        if (ApiBaseMethod::checkUrl($request->fullUrl())) {
            $validator = Validator::make($input, [
                'title' => 'required|max:200|unique:sm_routes,title,'.$id,
                'far' => 'required',
                'id' => 'required',
                'updated_by' => 'required|integer',
            ]);
        }

        if ($validator->fails() && ApiBaseMethod::checkUrl($request->fullUrl())) {
            return ApiBaseMethod::sendError('Validation Error.', $validator->errors());

        }

        try {
            $route = SmRoute::find($request->id);
            $route->title = $request->title;
            $route->far = $request->far;
            $route->updated_by = $request->updated_by;
            $result = $route->save();
            if (ApiBaseMethod::checkUrl($request->fullUrl())) {
                if ($result) {
                    return ApiBaseMethod::sendResponse(null, 'Route has been updated successfully');
                }

                return ApiBaseMethod::sendError('Something went wrong, please try again');

            }
        } catch (Exception $exception) {

        }

        return null;
    }

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
                } else {
                    Toastr::error('Operation Failed', 'Failed');

                    return redirect()->back();
                }
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

        return null;
    }
}
