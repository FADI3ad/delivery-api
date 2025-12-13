<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{

    public function store(Request $request)
    {
        $request->validate([
            'from_address' => 'required|string',
            'to_address'   => 'required|string',
            'price'        => 'required|numeric',
            'vechicle_type' => 'required|string |in:سيارة,توك توك,موتوسيكل,تروسيكل,دراجة',
        ], [
            'from_address.required' => 'العنوان المرسل مطلوب',
            'to_address.required'   => 'العنوان المستلم مطلوب',
            'price.required'        => 'السعر مطلوب',
            'price.numeric'         => 'السعر يجب أن يكون رقم',
            'vechicle_type.required' => 'نوع المركبة مطلوب',
            'vechicle_type.in'      => 'نوع المركبة غير صحيح',
        ]);

        $order = Order::create([
            'user_id'      => auth('sanctum')->id(),
            'from_address' => $request->from_address,
            'to_address'   => $request->to_address,
            'price'        => $request->price,
            'vechicle_type' => $request->vechicle_type,
            'driver_id'    => null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم إنشاء الطلب بنجاح',
            'order' => [
                'id'     => $order->id,
                'from'   => $order->from_address,
                'to'     => $order->to_address,
                'price'  => $order->price,
                'vechicle_type' => $order->vechicle_type,
            ]
        ], 201);
    }



    public function deleteOrder($id)
    {
        $order = Order::findOrFail($id);


        if ($order->user_id != auth('sanctum')->id()) {
            return response()->json([
                'status' => false,
                'message' => 'لا يمكنك حذف هذا الطلب'
            ], 403);
        }


        if ($order->driver_id !== null) {
            return response()->json([
                'status' => false,
                'message' => 'لا يمكن حذف الطلب بعد استلامه من قبل السائق'
            ], 400);
        }


        $order->delete();

        return response()->json([
            'status' => true,
            'message' => 'تم حذف الطلب بنجاح'
        ]);
    }



    public function myOrders()
    {
        $orders = Order::where('user_id', auth('sanctum')->id())->get();

        return response()->json([
            'status' => true,
            'message' => 'تم استرجاع الطلبات بنجاح',
            'data' => [
                'count' => $orders->count(),
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'from_address' => $order->from_address,
                        'to_address' => $order->to_address,
                        'price' => $order->price,
                        'vechicle_type' => $order->vechicle_type
                    ];
                })
            ]
        ]);
    }

    public function orderStatus($id)
    {
        $order = Order::with('driver:id,name,phone')->find($id);

        if (!$order) {
            return response()->json([
                'status' => false,
                'message' => 'الطلب غير موجود'
            ], 404);
        }


        if ($order->driver_id === null) {
            return response()->json([
                'status' => true,
                'accepted' => false,
                'message' => 'لم يتم قبول الطلب بعد'
            ]);
        }


        return response()->json([
            'status'   => true,
            'message'  => 'تم قبول الطلب بنجاح',
            'driver' => [
                'id'    => $order->driver->id,
                'name'  => $order->driver->name,
                'phone' => $order->driver->phone,

            ]
        ]);
    }




    public function availableOrders()
    {

        $driverVehicle = auth('sanctum')->user()->vechicle_type;


        $orders = Order::whereNull('driver_id')
            ->where('vechicle_type', $driverVehicle)
            ->with('user:id,name,phone')
            ->get()
            ->map(function ($order) {
                return [
                    'id'             => $order->id,
                    'from'           => $order->from_address,
                    'to'             => $order->to_address,
                    'price'          => $order->price,
                    'customer_name'  => $order->user->name,
                    'customer_phone' => $order->user->phone,
                    'vechicle_type'   => $order->vechicle_type,
                ];
            });

        return response()->json([
            'status' => true,
            'orders' => $orders
        ]);
    }



    public function acceptOrder($id)
    {
        $order = Order::with('user:id,name,phone')->findOrFail($id);

        if ($order->driver_id) {
            return response()->json([
                "status" => false,
                "message" => "هذا الطلب تم استلامه بالفعل"
            ], 400);
        }

        $order->driver_id = auth('sanctum')->id();
        $order->save();

        return response()->json([
            'status' => true,
            'message' => 'تم قبول الطلب بنجاح',
            'order' => [
                'customer_name'  => $order->user->name,
                'customer_phone' => $order->user->phone,
                'from'           => $order->from_address,
                'to'             => $order->to_address,
                'price'          => $order->price,
                'vechicle_type'  => $order->vechicle_type
            ]
        ]);
    }


    public function myTakenOrders()
    {
        $orders = Order::where('driver_id', auth('sanctum')->id())
            ->with('user:id,name,phone')
            ->get()
            ->map(function ($order) {
                return [
                    'id'             => $order->id,
                    'from'           => $order->from_address,
                    'to'             => $order->to_address,
                    'price'          => $order->price,
                    'customer_name'  => $order->user->name,
                    'customer_phone' => $order->user->phone,
                    'vechicle_type'  => $order->vechicle_type
                ];
            });

        return response()->json([
            'status' => true,
            'orders' => $orders
        ]);
    }
}
