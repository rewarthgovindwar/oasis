<?php

namespace App\Http\Controllers\Admin\Transport;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Transport\SmRouteRequest;
use App\SmRoute;
use Brian2694\Toastr\Facades\Toastr;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmRouteController extends Controller
{


    public function index(Request $request)
    {
        /*
        try {
        */
            $routes = SmRoute::get();

            return view('backEnd.transport.route', ['routes' => $routes]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function store(SmRouteRequest $smRouteRequest)
    {
        /*
        try {
        */
            $smRoute = new SmRoute();
            $smRoute->title = $smRouteRequest->title;
            $smRoute->far = $smRouteRequest->far;
            $smRoute->school_id = Auth::user()->school_id;
            if (moduleStatusCheck('University')) {
                $smRoute->un_academic_id = getAcademicId();
            } else {
                $smRoute->academic_id = getAcademicId();
            }

            $smRoute->save();

            Toastr::success('Operation successful', 'Success');

            return redirect()->back();
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function show(Request $request, $id)
    {
        /*
        try {
        */
            $route = SmRoute::find($id);
            $routes = SmRoute::where('school_id', Auth::user()->school_id)->get();

            return view('backEnd.transport.route', ['route' => $route, 'routes' => $routes]);
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function update(SmRouteRequest $smRouteRequest, $id)
    {
        /*
        try {
        */
            $route = SmRoute::find($smRouteRequest->id);
            $route->title = $smRouteRequest->title;
            $route->far = $smRouteRequest->far;
            if (moduleStatusCheck('University')) {
                $route->un_academic_id = getAcademicId();
            }

            $route->save();

            Toastr::success('Operation successful', 'Success');

            return redirect('transport-route');
        /*
        } catch (Exception $exception) {
            Toastr::error('Operation Failed', 'Failed');

            return redirect()->back();
        }
        */
    }

    public function destroy(Request $request, $id)
    {
        /*
        try {
        */
            $tables = \App\tableList::getTableList('route_id', $id);
            /*
            try {
            */
                if ($tables == null) {
                    SmRoute::destroy($id);

                    Toastr::success('Operation successful', 'Success');

                    return redirect()->back();
                } else {
                    $msg = 'This data already used in  : ' . $tables . ' Please remove those data first';
                    Toastr::error($msg, 'Failed');
                    return redirect()->back();
                }
            /*
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
        */
    }
}
