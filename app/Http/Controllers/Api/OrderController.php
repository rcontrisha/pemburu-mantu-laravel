<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use App\Models\Image;

class OrderController extends Controller
{
    public function orderImages()
    {
        $images = Image::all();
        return response()->json($images);
    }

    public function index()
    {
        $orders = Order::with('image')
        ->whereHas('image', function ($query) {
            $query->where('user_id', Auth::id());
        })
        ->get();

        return response()->json($orders);
    }

    public function show()
    {
        $orders = Order::with('image') // Relasi dengan tabel 'image'
            ->where('user_id', Auth::id()) // Hanya untuk user yang sedang login
            ->get();

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email|max:255',
            'customer_phone' => 'required|string|max:15',
            'alamat' => 'required|string|max:255',
            'image_id' => 'required|exists:images,id',
        ]);

        $order = new Order();
        $order->customer_name = $request->input('customer_name');
        $order->customer_email = $request->input('customer_email');
        $order->customer_phone = $request->input('customer_phone');
        $order->alamat = $request->input('alamat');
        $order->image_id = $request->input('image_id');
        $order->user_id = Auth::id();
        $order->status = 'pending';
        $order->save();
        
        return response()->json($order, 201);
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'customer_name' => 'sometimes|required|string|max:255',
            'customer_email' => 'sometimes|required|email|max:255',
            'customer_phone' => 'sometimes|required|string|max:15',
            'image_id' => 'sometimes|required|exists:images,id',
            'status' => 'sometimes|required|string|in:pending,paid,shipped,completed,canceled',
        ]);

        $order->update($request->all());
        return response()->json($order);
    }

    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json(null, 204);
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|string|in:pending,paid,shipped,completed,canceled',
        ]);

        $order->update(['status' => $request->status]);
        return response()->json($order);
    }
}
