<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Order;

class UserController extends Controller
{

    public function register(UserRequest $request)
    {


        $data = $request->only([
            'name',
            'email',
            'phone',
            'national_id',
            'vechicle_type',
            'role'
        ]);

        $data['vechicle_type'] = $data['vechicle_type'] ?? 'عميل';
        $data['role']          = $data['role'] ?? 'عميل';
        $data['password']      = bcrypt($request->password);

        if ($request->hasFile('image')) {
            $imageName = uniqid() . '_profile.' . $request->image->extension();
            $request->image->storeAs('users', $imageName, 'public');
            $data['image'] = $imageName;
        }

        if ($request->hasFile('national_image')) {
            $nationalImageName = uniqid() . '_nid.' . $request->national_image->extension();
            $request->national_image->storeAs('national_ids', $nationalImageName, 'public');
            $data['national_image'] = $nationalImageName;
        }

        $user = User::create($data);

        $token = $user->createToken('mobile-app-token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل المستخدم بنجاح',
            'user' => $user,
            'profile_image_url' => $user->image
                ? asset('storage/users/' . $user->image)
                : null,
            'national_image_url' => $user->national_image
                ? asset('storage/national_ids/' . $user->national_image)
                : null,
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'البريد الإلكتروني مطلوب',
            'email.email' => 'صيغة البريد الإلكتروني غير صحيحة',
            'password.required' => 'كلمة المرور مطلوبة',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة.'],
            ]);
        }

        $user->tokens()->delete();

        $token = $user->createToken('mobile-app-token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'user' => $user,
            'profile_image_url' => $user->image
                ? asset('storage/users/' . $user->image)
                : null,
            'national_image_url' => $user->national_image
                ? asset('storage/national_ids/' . $user->national_image)
                : null,
            'token' => $token,
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'status' => true,
            'user' => $request->user(),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم تسجيل الخروج بنجاح',
        ]);
    }


























    // admin functions


    private function authorizeAdmin($user)
    {
        if ($user->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'غير مصرح لك بالوصول لهذه الخاصية'
            ], 403);
        }
    }
























    public function drivers(Request $request)
    {
        $check = $this->authorizeAdmin($request->user());
        if ($check) return $check;

        $perPage = $request->query('per_page',);

        $drivers = User::select('id', 'name', 'phone', 'email', 'vechicle_type')
            ->where('role', 'سائق')
            ->paginate($perPage);

        return response()->json([
            'status' => true,


            'data' => $drivers->items(),

            'pagination' => [
                'current_page' => $drivers->currentPage(),
                'per_page' => $drivers->perPage(),
                'total' => $drivers->total(),
                'last_page' => $drivers->lastPage(),
            ]
        ]);
    }

    public function customers(Request $request)
    {
        $check = $this->authorizeAdmin($request->user());
        if ($check) return $check;

        $perPage = $request->query('per_page', 20);

        $customers = User::select('id', 'name', 'phone', 'email')
            ->where('role', 'عميل')
            ->paginate($perPage);

        return response()->json([
            'status' => true,
            'data' => $customers->items(),
            'pagination' => [
                'current_page' => $customers->currentPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'last_page' => $customers->lastPage(),
            ]
        ]);
    }
























    public function deleteUser($id, Request $request)
    {

        $check = $this->authorizeAdmin($request->user());
        if ($check) return $check;

        $user = User::find($id);


        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'اليوزر غير موجود'
            ], 404);
        }


        if ($user->role == 'عميل' || $user->role == 'سائق') {


            $user->delete();

            return response()->json([
                'status' => true,
                'message' => 'تم حذف حساب اليوزر بنجاح'
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'اليوزر ليس من نوع صالح'
        ], 400);
    }





    public function driverDailyEarnings($driverId, Request $request)
    {
        $check = $this->authorizeAdmin($request->user());
        if ($check) return $check;
        $today = Carbon::today();


        $driver = User::find($driverId);

        if (!$driver) {
            return response()->json([
                'status' => false,
                'message' => 'المستخدم غير موجود'
            ], 404);
        }


        if ($driver->role !== 'سائق') {
            return response()->json([
                'status' => false,
                'message' => 'هذا المستخدم ليس سائق'
            ], 403);
        }


        $totalTrips = Order::where('driver_id', $driverId)
            ->whereDate('created_at', $today)
            ->count();


        $totalEarnings = Order::where('driver_id', $driverId)
            ->whereDate('created_at', $today)
            ->sum('price');


        return response()->json([
            'status' => true,
            'date' => $today->toDateString(),

            'id'   => $driver->id,
            'name' => $driver->name,
            'total_trips'    => $totalTrips,
            'total_earnings' => $totalEarnings

        ]);
    }


    public function deleteOrder($id, Request $request)
    {
        $check = $this->authorizeAdmin($request->user());
        if ($check) return $check;

        $order = Order::find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'الطلب غير موجود'
            ], 404);
        }

        $order->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف الطلب بنجاح'
        ]);
    }
    public function deleteAllOrders(Request $request)
    {
        $check = $this->authorizeAdmin($request->user());
        if ($check) return $check;

        Order::truncate(); 

        return response()->json([
            'status' => true,
            'message' => 'تم حذف جميع الطلبات بنجاح'
        ]);
    }
}
