<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use AfricasTalking\SDK\AfricasTalking;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        return OrderResource::collection(
            Order::query()
                ->join('users', 'users.id', '=', 'orders.customer_id')
                ->join('products', 'products.id', '=', 'orders.product_id')
                ->select('orders.id', 'orders.quantity', 'orders.location', 'orders.status', 'orders.created_at', 'users.first_name', 'users.last_name', 'users.phone_number', 'products.name')
                ->orderBy('orders.id', 'desc')
                ->paginate(10)
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);
        $order = Order::create($data);

        return response(new OrderResource($order), 201);
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\Response
     */

    public function show(Order $order)
    {
        $order = Order::query()
            ->join('users', 'users.id', '=', 'orders.customer_id')
            ->join('products', 'products.id', '=', 'orders.product_id')
            ->where('orders.id', '=', $order->id)
            ->select('orders.id', 'orders.quantity', 'orders.location', 'orders.status', 'orders.created_at', 'users.first_name', 'users.last_name', 'users.phone_number', 'products.name')
            ->first();

        return new OrderResource($order);
    }

    /*
    public function show(Order $order)
    {
    return new OrderResource($order);
    }*/

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateUserRequest $request
     * @param \App\Models\Order                     $order
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateOrderRequest $request, Order $order)
    {
        $data = $request->validated();
        $order->update($data);

    
        // Check if phone_number exists on the form
        $phone_number = $request->input('phone_number');
        if ($phone_number) {
            // Initialize the SDK
            $username = env('AFRICASTALKING_USERNAME');
            $api_key = env('AFRICASTALKING_API_KEY');
            $AT = new AfricasTalking($username, $api_key);
    
            // Set the SMS service
            $sms = $AT->sms();
    
            // Set the message
            $message = "The status of your order has been updated. Please Check The Notification Section";
    
            // Set the recipients
            $recipients = [$phone_number];
    
            // Send the message
            $sms->send([
                'to'      => $recipients,
                'message' => $message,
                'from'    => 'HH'
            ]);
        }
    
        return new OrderResource($order);



    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Order $order
     * @return \Illuminate\Http\Response
     */
    public function destroy(Order $order)
    {
        $order->delete();

        return response("", 204);
    }
}
