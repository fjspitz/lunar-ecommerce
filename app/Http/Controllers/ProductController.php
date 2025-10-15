<?php

namespace App\Http\Controllers;

use App\Exceptions\BrandDoesNotExistException;
use App\Exceptions\ProductTypeDoesNotExistException;
use App\Services\ProductService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Lunar\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request, ProductService $service)
    {
        $validated = $request->validate([
            'customer_group' => 'required|string',
            'page_size' => 'int|min:1|max:20'
        ]);

        $page_size = $validated['page_size'] ?? 10;

        Log::info("Consulta de productos por customer group: {$validated['customer_group']}");

        return $service->getPublishedProductsByCustomerGroup($validated['customer_group'], $page_size)
            ->appends($request->input());
    }

    public function show(Product $product, ProductService $service)
    {
        Log::info("Consulta de producto: {$product->id}");

        return $service->getSingleProduct($product);
    }

    public function categories(ProductService $service)
    {
        Log::info("Consulta de listado de categorías de producto");

        return $service->getCategories();
    }

    public function brands(ProductService $service)
    {
        Log::info("Consulta de listado de marcas de producto");

        return $service->getBrands();
    }

    public function create(Request $request, ProductService $service)
    {
        $validated = $request->validate([
            'name' => 'required',
            'description' => 'required',
            'brand_id' => 'required',
            'product_type_id' => 'required',
            'sku' => 'required|unique:lunar_product_variants,sku',
            'stock' => 'required',
            'price' => 'required|numeric|min:0',
            'image' => 'file',
        ]);

        try {
            Log::info("Creando un producto: {$validated['name']}, categoría: {$validated['product_type_id']}");
            
            $product = $service->create($validated);

            Log::info("Se creó el siguiente producto para todos los customer groups existentes.", ["product:" => $product]);

            return response($product, 201);
        } catch (ProductTypeDoesNotExistException|BrandDoesNotExistException $e) {
            Log::error("Ocurrió un error con la categoría o marca al intentar crear un producto: {$e->errorMessage()}");

            return response([
                'message' => $e->errorMessage(),
            ], 400);
        } catch (Exception $e) {
            Log::error("Ocurrió un error al intentar crear un producto: {$e->getMessage()}", ["error" => $e]);
            
            return response([
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function update(Request $request, Product $product, ProductService $service)
    {
        $product_update_info = $request->validate([
            'name' => 'sometimes|string',
            'description' => 'sometimes|string',
            'brand_id' => 'sometimes|int',
            'product_type_id' => 'sometimes|int',
            'stock' => 'sometimes|int',
            'price' => 'sometimes|numeric|min:0',
            'image' => 'sometimes|file',
        ]);

        try {
            Log::info("Actualizando el producto: {$product->id}", ["updated_info" => $product_update_info]);

            $updated_product = $service->update($product, $product_update_info);

            return response($updated_product, 200);
        } catch (Exception $e) {
            Log::error("Ocurrió un error al intentar actualizar el producto: {$product->id}", ["error" => $e]);

            return response([
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
