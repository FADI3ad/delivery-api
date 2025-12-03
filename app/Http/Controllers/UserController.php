<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
}
