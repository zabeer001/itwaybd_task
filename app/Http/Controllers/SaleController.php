<?php

namespace App\Http\Controllers;

use App\Helpers\SaleHelper;
use App\Http\Requests\Sale\StoreSaleRequest;
use App\Http\Requests\Sale\UpdateSaleRequest;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Exception;

class SaleController extends Controller
{
    /**
     * Display a listing of the sales.
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);


        $query = Sale::with(['products', 'user']) // eager load products and user
            ->where('softdelete', 0);

        // Filter by user name
        if ($request->filled('user_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->user_name . '%');
            });
        }

        // Filter by product name
        if ($request->filled('product_name')) {
            $query->whereHas('products', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->product_name . '%');
            });
        }

        if ($request->filled('created_at')) {
            $query->whereDate('created_at', $request->created_at);
        }

        $sales = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $sales
        ]);
    }


    /**
     * Store a newly created sale.
     */
    public function store(StoreSaleRequest $request)
    {
        // Automatically calculate total amount
        $totalAmount = SaleHelper::calculateTotalAmount($request->products);

        // Create Sale
        $sale = Sale::create([
            'user_id' => $request->user_id,
            'total_amount' => $totalAmount, // use calculated value
            'softdelete' => 0,
        ]);

        // Attach products with pivot data
        foreach ($request->products as $prod) {
            $sale->products()->attach($prod['product_id'], [
                'quantity' => $prod['quantity']
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $sale->load('products')
        ]);
    }

    /**
     * Display the specified sale.
     */
    public function show(Sale $sale)
    {
        if ($sale->softdelete) {
            return response()->json([
                'success' => false,
                'message' => 'Sale not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $sale->load('products')
        ]);
    }

    /**
     * Update the specified sale.
     */
    public function update(UpdateSaleRequest $request, Sale $sale): JsonResponse
    {
        try {
            // Begin database transaction
            DB::beginTransaction();

            // Update sale fields (except total_amount for now)
            $sale->update($request->only(['user_id', 'sale_date']));

            // If products are provided, sync pivot and recalculate total_amount
            if ($request->has('products')) {
                // Validate products array structure
                if (!is_array($request->products) || empty($request->products)) {
                    throw new \InvalidArgumentException('Products array cannot be empty');
                }

                $syncData = [];
                foreach ($request->products as $prod) {
                    // Validate product data
                    if (
                        !isset($prod['product_id']) || !isset($prod['quantity']) ||
                        !is_numeric($prod['product_id']) || !is_numeric($prod['quantity']) ||
                        $prod['quantity'] <= 0
                    ) {
                        throw new \InvalidArgumentException('Invalid product data format');
                    }

                    $syncData[$prod['product_id']] = ['quantity' => $prod['quantity']];
                }

                // Sync products
                $sale->products()->sync($syncData);

                // Recalculate total amount
                $totalAmount = SaleHelper::calculateTotalAmount($request->products);
                if ($totalAmount === null || $totalAmount < 0) {
                    throw new \RuntimeException('Invalid total amount calculated');
                }

                $sale->update(['total_amount' => $totalAmount]);
            }

            // Commit transaction
            DB::commit();

            // Return success response with refreshed sale data
            return response()->json([
                'success' => true,
                'data' => $sale->load('products'),
                'message' => 'Sale updated successfully'
            ], 200);
        } catch (ModelNotFoundException $e) {
            // Handle case where sale or related model not found
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Sale or related resource not found',
                'message' => $e->getMessage()
            ], 404);
        } catch (\InvalidArgumentException $e) {
            // Handle invalid input data
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Invalid input data',
                'message' => $e->getMessage()
            ], 422);
        } catch (QueryException $e) {
            // Handle database query errors
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'Database error occurred',
                'message' => config('app.debug') ? $e->getMessage() : 'Unable to process request due to database error'
            ], 500);
        } catch (Exception$e) {
            // Handle any other unexpected errors
            DB::rollBack();
            return response()->json([
                'success' => false,
                'error' => 'An unexpected error occurred',
                'message' => config('app.debug') ? $e->getMessage() : 'An unexpected error occurred while processing the request'
            ], 500);
        }
    }

    /**
     * Soft delete the specified sale.
     */
    public function destroy(Sale $sale)
    {
        $sale->update(['softdelete' => 1]);

        return response()->json([
            'success' => true,
            'message' => 'Sale deleted successfully'
        ]);
    }
}
