<?php

namespace App\Http\Controllers;

use App\Models\UserDevice;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeviceController extends Controller
{
    /**
     * Return Server VAPID Public Key for Web / PWA Push Subscription
     */
    public function getVapidPublicKey()
    {
        $keys = PushNotificationService::getVapidKeys();

        return response()->json([
            'ok' => true,
            'publicKey' => $keys['publicKey']
        ]);
    }

    /**
     * Register or Update a User's Device Push Token
     */
    public function registerPush(Request $request)
    {
        $request->validate([
            'device_uuid' => 'required|string|max:100',
            'device_name' => 'nullable|string|max:150',
            'platform'    => 'nullable|string|max:30',
            'push_type'   => 'required|string|in:fcm,vapid',
        ]);

        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['ok' => false, 'error' => 'Unauthenticated'], 401);
        }

        $device = UserDevice::updateOrCreate(
            [
                'user_id'     => $userId,
                'device_uuid' => $request->device_uuid,
            ],
            [
                'device_name'    => $request->device_name ?: 'Unknown Device',
                'platform'       => $request->platform ?: 'web',
                'push_type'      => $request->push_type,
                'push_token'     => $request->push_token,
                'endpoint'       => $request->endpoint,
                'public_key'     => $request->public_key,
                'auth_token'     => $request->auth_token,
                'is_active'      => true,
                'last_active_at' => now(),
            ]
        );

        return response()->json([
            'ok' => true,
            'device_id' => $device->id,
            'message' => 'Device push credentials registered successfully'
        ]);
    }

    /**
     * Deactivate Push for a Device
     */
    public function unregisterPush(Request $request)
    {
        $request->validate([
            'device_uuid' => 'required|string|max:100'
        ]);

        $userId = Auth::id();
        if ($userId) {
            UserDevice::where('user_id', $userId)
                ->where('device_uuid', $request->device_uuid)
                ->update(['is_active' => false]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Dispatch Test Push to Current User's Registered Devices (or a specific device)
     */
    public function sendTestPush(Request $request)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['ok' => false, 'error' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();
        $isSuperAdmin = $user && ($user->role === 'super_admin' || $user->account_type === '1');

        $payload = [
            'title' => $request->input('title', '🛡️ FileFusion Cloud Push'),
            'body'  => $request->input('body', 'This is a live server push notification dispatched via VAPID/FCM.'),
            'url'   => $request->input('url', route('panel.dashboard')),
            'tag'   => 'filefusion_test_push'
        ];

        $deviceId = $request->input('device_id');
        if ($deviceId) {
            $device = $isSuperAdmin ? UserDevice::find($deviceId) : UserDevice::where('user_id', $userId)->where('id', $deviceId)->first();
            if (!$device) {
                return response()->json(['ok' => false, 'error' => 'Device not found.'], 404);
            }
            $result = PushNotificationService::sendToDevice($device, $payload);
            return response()->json([
                'ok' => $result['success'],
                'results' => [
                    'total' => 1,
                    'sent' => $result['success'] ? 1 : 0,
                    'failed' => $result['success'] ? 0 : 1,
                    'details' => [$result]
                ]
            ]);
        }

        $devices = UserDevice::where('user_id', $userId)->where('is_active', true)->get();
        if ($devices->count() === 0 && $isSuperAdmin) {
            $devices = UserDevice::where('is_active', true)->get();
        }

        $results = [
            'total' => $devices->count(),
            'sent' => 0,
            'failed' => 0,
            'details' => []
        ];

        foreach ($devices as $dev) {
            $status = PushNotificationService::sendToDevice($dev, $payload);
            if ($status['success']) {
                $results['sent']++;
            } else {
                $results['failed']++;
            }
            $results['details'][] = $status;
        }

        return response()->json([
            'ok' => $results['sent'] > 0,
            'results' => $results
        ]);
    }

    /**
     * Delete a Device Registration
     */
    public function deleteDevice(int $id)
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['ok' => false, 'error' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();
        $isSuperAdmin = $user && ($user->role === 'super_admin' || $user->account_type === '1');

        $device = $isSuperAdmin ? UserDevice::find($id) : UserDevice::where('user_id', $userId)->where('id', $id)->first();
        if ($device) {
            $device->delete();
            return response()->json(['ok' => true, 'message' => 'Device deregistered successfully.']);
        }

        return response()->json(['ok' => false, 'error' => 'Device not found.'], 404);
    }
}
