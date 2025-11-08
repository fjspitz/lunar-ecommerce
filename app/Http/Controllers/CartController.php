<?php

namespace App\Http\Controllers;

use App\Http\Resources\CartResource;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Lunar\Models\Cart;
use Lunar\Models\CartLine;
use Lunar\Models\Channel;
use Lunar\Models\Country;
use Lunar\Models\Currency;
use Lunar\Models\Customer;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class CartController extends Controller
{
    public function show(Cart $cart)
    {
        Log::info("Consulta de carrito: {$cart->id}");

        return new CartResource(Cart::findOrFail($cart->id));
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            //'curp' => 'required|string|',
            //'company_name' => 'required|string|',
            'account_ref' => 'required|string|',
        ]);

        Log::info("Creando un carrito para cliente con los datos: ", $validated);

        // $customer = Customer::where('company_name', $validated['company_name'])
        //     ->whereJsonContains('meta->curp', $validated['curp'])
        //     ->first();

        $customer = Customer::where('account_ref', $validated['account_ref'])->first();

        $channel = Channel::first();
        $currency = Currency::where('default', true)->first();

        $cartExists = Cart::where('customer_id', $customer->id)
            ->where('completed_at', null)
            ->first();

        if ($cartExists) {
            Log::info("Se encontró un carrito existente para el cliente {$customer->id}", ['cart_id' => $cartExists->id]);
            return response()->json(new CartResource($cartExists), 200);
        } else {
            $cart = Cart::create([
                'currency_id' => $currency->id,
                'channel_id' => $channel->id,
                'customer_id' => $customer->id,
            ]);
            Log::info("Se creó un carrito para el cliente {$customer->id}", ['cart_id' => $cart->id]);
            return response()->json(new CartResource($cart), 200);
        }
    }

    public function addLine(Request $request, Cart $cart)
    {
        $purchasable_id = $request->input('purchasable_id');

        Log::info("Agregando producto: {$purchasable_id} al carrito: {$cart->id}");

        $validated = $request->validate([
            'purchasable_id' => 'required|numeric|',
            'quantity' => 'required|min:1|max:99|',
        ]);

        $purchasable = Product::find($purchasable_id)->variants()->first();

        $cartLine = new \Lunar\Models\CartLine([
            'cart_id' => $cart->id,
            //'purchasable_type' => ProductVariant::class,
            'purchasable_type' => $purchasable->class,
            'purchasable_id' => $validated['purchasable_id'],
            'quantity' => $validated['quantity'],
        ]);
        $cartLine->save();

        Log::info("El producto {$cartLine->purchasable_id} fue agregado al carrito: {$cart->id}.");

        return response()->json(new CartResource($cart), 201);
    }

    // public function updateLine(Request $request, Cart $cart, CartLine $cartLine)
    // {
    //     $validated = $request->validate([
    //         'quantity' => 'required|min:1|max:99|',
    //     ]);

    //     $cartLine->quantity = $validated['quantity'];
    //     $cartLine->save();

    //     return response()->json([
    //         'cart_line' => $cartLine,
    //     ]);
    // }

    public function checkout(Request $request, Cart $cart)
    {
        Log::info("Iniciando checkout para el carrito: {$cart->id}");

        $validated = $request->validate([
            'cart' => 'required|array|min:1|',
            'cart.*.purchasable_id' => 'required|integer|exists:lunar_products,id|',
            'cart.*.quantity' => 'required|integer|between:1,99|',
            'pickup_point' => 'required|integer|exists:addresses,id|',
        ]);

        $cart->lines()->truncate();

        foreach ($validated['cart'] as $cl) {
            $cart->lines()->create([
                'purchasable_type' => ProductVariant::class,
                'purchasable_id' => $cl['purchasable_id'],
                'quantity' => $cl['quantity'],
            ]);
        }

        $customer = Customer::find($cart->customer_id);

        $pickup_point = Address::find($validated['pickup_point']);

        $cart->setShippingAddress([
            'country_id' => $pickup_point->country_id,
            'title' => $pickup_point->name,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'line_one' => $pickup_point->line_one,
            'line_two' => $pickup_point->line_two,
            'line_three' => $pickup_point->line_three,
            'city' => $pickup_point->city,
            'state' => $pickup_point->state,
            'postcode' => $pickup_point->postcode,
            'contact_email' => null,
            'contact_phone' => null,
        ]);

        $cart->calculate();

        return response()->json([
            'cart' => new CartResource(Cart::findOrFail($cart->id)),
            'checkout' => [
                'fingerprint' => $cart->fingerprint(),
                'subtotal' => $cart->subTotal->value / 100.0,
                'total_taxes' => $cart->taxTotal->value / 100.0,
                'total' => $cart->total->value / 100.0,
            ],
        ]);
    }

    public function confirm(Cart $cart)
    {
        Log::info("Confirmando compra para el carrito: {$cart->id}");

        $customer = Customer::find($cart->customer_id);

        $cart->setBillingAddress([
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            // TODO: obtener en el alta la dirección
            'line_one' => $customer->meta['address']['street'],
            'line_two' => '',
            'line_three' => '',
            'city' => $customer->meta['address']['city'],
            'state' => 'Ciudad de Mexico',
            'postcode' => '03100',
            'country_id' => Country::where('iso3', 'MEX')->first()->id,
        ]);

        $cart->calculate();

        $order = $cart->createOrder(allowMultipleOrders: false, orderIdToUpdate: null);
        $order->customer_reference = $customer->meta['curp'];
        $order->save();

        $cart->order_id = $order->id;
        $cart->completed_at = now();
        $cart->save();

        // Descontar stock
        foreach ($cart->lines()->get() as $line) {
            $product = ProductVariant::find($line->purchasable_id);
            $product->stock = $product->stock - 1;
            $product->save();

            Log::info("Se ha descontado el stock para el producto {$line->purchasable_id}");
        }

        return response()->json([
            'cart' => new CartResource(Cart::findOrFail($cart->id)),
            'order' => $order,
        ]);
    }

    public function getPickUpPoints()
    {
        return response()->json([
            'adresses' => Address::all(),
        ]);
    }

    public function clear(Cart $cart)
    {
        Log::info("Vaciando carrito: {$cart->id}");

        $cart->lines()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Cart cleared.',
        ]);
    }

    public function remove(Cart $cart)
    {
        Log::info("Eliminando carrito: {$cart->id}");

        $cart->lines()->delete();
        $cart->delete();

        return response()->json([
            'status' => true,
            'message' => 'Cart deleted.',
        ]);
    }
}
