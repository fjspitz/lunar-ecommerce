<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrderResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Lunar\Models\Order;

class OrderController extends Controller
{
    public function show(Order $order)
    {
        Log::info("Consulta de orden: {$order->id}");

        return new OrderResource($order);
    }

    public function transaction(Request $request, Order $order)
    {
        Log::info("Generando transacción para la orden: {$order->id}");
        
        $order->transactions()->create([
            //'success' => $request->input('success'),
            'success' => true,
            'parent_transaction_id' => null,
            'order_id' => $order->id,
            'type' => $request->input('type'),
            'captured_at' => now(),
            'driver' => 'Adecash',
            'amount' => $request->input('amount'),
            'reference' => $request->input('reference'),
            'status' => 'settled',
            'notes' => $request->input('notes'),
            'card_type' => '',
            'meta' => $request->input('meta'),
        ]);

        if ($request->input('amount') >= $order->total->value) {
            $order->status = 'payment-received';
            $order->placed_at = now();
            $order->save();
        }

        return response()->json([
            'transaction' => $order->transactions()->get(),
        ], 201);
    }
}
