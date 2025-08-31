<?php

namespace App\Http\Controllers\api\v2\Admin\Chat;

use App\Http\Controllers\Controller;
use App\Http\Resources\v2\Chat\Admin\ChatUserListResource;
use Illuminate\Http\Request;
use Modules\Chat\Entities\Status;
use Modules\Chat\Services\UserService;

class AdminUserServiceController extends Controller
{
    protected $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function search(Request $request)
    {
        $keywords = $request->keywords;
        $users = $keywords ? $this->userService->search($keywords)->load('activeStatus') : [];

        $anonymousResourceCollection = ChatUserListResource::collection($users);

        if (! $anonymousResourceCollection) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $anonymousResourceCollection,
                'message' => 'Search user successful',
            ];
        }

        return response()->json($response);
    }

    public function changeStatus(Request $request)
    {
        $type = $request->status;

        userStatusChange(auth()->id(), $type);

        $response = [
            'success' => true,
            'data' => null,
            'message' => 'Status changed successfully',
        ];

        return response()->json($response);
    }

    public function blockAction(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required',
        ]);

        $user = $request->user_id;
        $type = $request->type;

        $this->userService->blockAction($type, $user);

        $response = [
            'success' => true,
            'data' => null,
            'messege' => 'The user is blocked for you',
        ];

        return response()->json($response);
    }

    public function blockedUsers()
    {
        $data = $this->userService->allBlockedUsers()->map(function ($value): array {
            return [
                'user_id' => (int) $value->id,
                'full_name' => (string) $value->full_name,
                'image' => $value->avatar_url ? (string) asset($value->avatar_url) : null,
                'active_status' => (int) $value->active_status,
            ];
        });

        if (! $data) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => $data,
                'message' => 'Blocked user list',
            ];
        }

        return response()->json($response);
    }

    public function chatStatus()
    {
        $status = Status::where('user_id', auth()->user()->id)
            ->select('status')
            ->first();

        switch ($status->status) {
            case 0:
                $data = [
                    'status' => 'INACTIVE',
                    'color' => '0xFFE1E2EC',
                ];
                break;
            case 1:
                $data = [
                    'status' => 'ACTIVE',
                    'color' => '0xFF12AE01',
                ];
                break;
            case 2:
                $data = [
                    'status' => 'AWAY',
                    'color' => '0xFFF99F15',
                ];
                break;
            case 3:
                $data = [
                    'status' => 'BUSY',
                    'color' => '0xFFF60003',
                ];
                break;
        }

        $data['status_info'] = [
            [
                'key' => 0,
                'name' => 'INACTIVE',
                'color' => '0xFFE1E2EC',
            ],
            [
                'key' => 1,
                'name' => 'ACTIVE',
                'color' => '0xFF12AE01',
            ],
            [
                'key' => 2,
                'name' => 'AWAY',
                'color' => '0xFFF99F15',
            ],
            [
                'key' => 3,
                'name' => 'BUSY',
                'color' => '0xFFF60003',
            ],
        ];

        if ($data == []) {
            $response = [
                'success' => false,
                'data' => null,
                'message' => 'Operation failed',
            ];
        } else {
            $response = [
                'success' => true,
                'data' => [$data],
                'message' => 'Active status',
            ];
        }

        return response()->json($response);
    }
}
