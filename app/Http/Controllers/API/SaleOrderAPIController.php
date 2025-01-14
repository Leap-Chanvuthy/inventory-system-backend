<?php

namespace App\Http\Controllers\API;

use App\Exports\SaleOrderExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductSaleOrder;
use App\Models\SaleOrder;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class SaleOrderAPIController extends Controller
{

    private function allBuilder(): QueryBuilder
    {
        return QueryBuilder::for(SaleOrder::class)
            -> leftJoin ('customers' , 'sale_orders.customer_id', '=' , 'customers.id')
            ->select('sale_orders.*', 'customers.fullname as customer_name')
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('payment_method'),
                AllowedFilter::exact('order_status'),
                AllowedFilter::exact('payment_status'),
                AllowedFilter::exact('sale_orders.order_date'),
                AllowedFilter::exact('sale_orders.discount_percentage'),
                AllowedFilter::exact('sale_orders.tax_percentage'),
                AllowedFilter::exact('sale_orders.clearing_payable_percentage'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('sale_orders.payment_method', 'LIKE', "%{$value}%")
                              ->orWhere('sale_orders.order_status', 'LIKE', "%{$value}%")
                              ->orWhere('sale_orders.payment_status', 'LIKE', "%{$value}%")
                              ->orWhere('sale_orders.order_date', 'LIKE', "%{$value}%");
                    });
                }),
                AllowedFilter::callback('date_range', function (Builder $query, $value) {
                    if (isset($value['start_date']) && isset($value['end_date'])) {
                        $startDate = Carbon::parse($value['start_date'])->startOfDay();
                        $endDate = Carbon::parse($value['end_date'])->endOfDay();
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    }
                }),  
            ])
            ->allowedSorts('created_at', 'updated_at', 'customer_name' ,'order_date' ,'sub_total_in_usd', 'grand_total_with_tax_in_usd')
            ->defaultSort('-created_at');
    }

    private function allBuilderWithTrashed(): QueryBuilder
    {
        return QueryBuilder::for(SaleOrder::class)
            -> leftJoin ('customers' , 'sale_orders.customer_id', '=' , 'customers.id')
            ->select('sale_orders.*', 'customers.fullname as customer_name')
            ->onlyTrashed()
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::exact('payment_method'),
                AllowedFilter::exact('order_status'),
                AllowedFilter::exact('payment_status'),
                AllowedFilter::exact('sale_orders.order_date'),
                AllowedFilter::exact('sale_orders.discount_percentage'),
                AllowedFilter::exact('sale_orders.tax_percentage'),
                AllowedFilter::exact('sale_orders.clearing_payable_percentage'),
                AllowedFilter::callback('search', function (Builder $query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('sale_orders.payment_method', 'LIKE', "%{$value}%")
                              ->orWhere('sale_orders.order_status', 'LIKE', "%{$value}%")
                              ->orWhere('sale_orders.payment_status', 'LIKE', "%{$value}%")
                              ->orWhere('sale_orders.order_date', 'LIKE', "%{$value}%");
                    });
                }),
                AllowedFilter::callback('date_range', function (Builder $query, $value) {
                    if (isset($value['start_date']) && isset($value['end_date'])) {
                        $startDate = Carbon::parse($value['start_date'])->startOfDay();
                        $endDate = Carbon::parse($value['end_date'])->endOfDay();
                        $query->whereBetween('created_at', [$startDate, $endDate]);
                    }
                }),  
            ])
            ->allowedSorts('created_at', 'updated_at', 'customer_name' ,'order_date' ,'sub_total_in_usd', 'grand_total_with_tax_in_usd')
            ->defaultSort('-created_at');
    }
    

    private function validateAndExtractData(Request $request, $id = null): array
    {
        $rules = [
            'payment_method' => 'required|string|max:50',
            'order_date' => 'required|date',
            // 'payment_status' => 'required|string',
            'order_status' => 'required|string',Rule::in(['PENDING', 'PROCESSING', 'DELIVERING' ,'COMPLETED']),
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'tax_percentage' => 'nullable|numeric|min:0|max:100',
            'clearing_payable_percentage' => 'required|numeric|min:0|max:100',
            'products' => 'required|array|min:1',
            'customer_id' => 'required|exists:customers,id',
            'products.*.id' => 'required|exists:products,id',
            'products.*.quantity_sold' => 'required|integer|min:1',
        ];

        $validatedData = $request->validate($rules);

        return $validatedData;
    }


    public function generateInvoiceNumber(): string
    {
        $lastInvoice = SaleOrder::withTrashed()
            ->selectRaw('MAX(CAST(SUBSTRING(sale_invoice_number, 5) AS UNSIGNED)) AS max_code')
            ->first();

        $lastCode = $lastInvoice->max_code ?? 0;

        $newNumber = str_pad($lastCode + 1, 6, '0', STR_PAD_LEFT);
        return 'INV-' . $newNumber;
    }

    public function index () {
        try {
            $saleOrder = $this -> allBuilder() -> with( 'customer' , 'products' , 'vender') -> paginate(10);
            return response() -> json($saleOrder);
        }catch (Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }

    public function trashed()
    {
        try {
            $deletedSaleOrders = $this -> allBuilderWithTrashed() -> with( 'customer' , 'products') -> paginate(10);
            return response()->json($deletedSaleOrders, 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function show ($id) {
        try {
            $saleOrder = SaleOrder::with('customer.category' , 'products' , 'vender') ->findOrFail($id);
            return response() -> json($saleOrder);
        }catch (Exception $e){
            return response() -> json(['error' => $e -> getMessage()],400);
        }
    }



    public function store(Request $request)
    {
        try {
            $validated = $this->validateAndExtractData($request);
            $vender_id = Auth::user() -> id;

            // Step 1: Calculate total values from the product array
            $subTotalUSD = 0;
            $subTotalRiel = 0;

            foreach ($validated['products'] as $productInput) {
                $product = Product::findOrFail($productInput['id']);

                // Check if remaining quantity is sufficient
                if ($product->remaining_quantity < $productInput['quantity_sold']) {
                    throw new \Exception("Quantity of product ID {$product->id}, product code: ({$product -> product_code}) is not enough.");
                }

                // Add product's price to sub-total
                $subTotalUSD += $product->unit_price_in_usd * $productInput['quantity_sold'];
                $subTotalRiel += $product->unit_price_in_riel * $productInput['quantity_sold'];

                // Update remaining quantity
                $product->remaining_quantity -= $productInput['quantity_sold'];
                $product->save();
            }

            // Step 2: Calculate discount, tax, and indebted values
            $discountValueUSD = $subTotalUSD * ($validated['discount_percentage'] / 100);
            $discountValueRiel = $subTotalRiel * ($validated['discount_percentage'] / 100);

            $taxValueUSD = $subTotalUSD * ($validated['tax_percentage'] / 100);
            $taxValueRiel = $subTotalRiel * ($validated['tax_percentage'] / 100);

            $grandTotalUSD = $subTotalUSD - $discountValueUSD + $taxValueUSD;
            $grandTotalRiel = $subTotalRiel - $discountValueRiel + $taxValueRiel;

            $clearingPayablePercentage = $validated['clearing_payable_percentage'];
            $indebtedUSD = 0;
            $indebtedRiel = 0;

            if ($clearingPayablePercentage == 0) {
                $payment_status = 'UNPAID';
                $indebtedUSD = $grandTotalUSD;
                $indebtedRiel = $grandTotalRiel;
            } elseif ($clearingPayablePercentage < 100) {
                $payment_status = 'INDEBTED';
                $indebtedUSD = $grandTotalUSD * ((100 - $clearingPayablePercentage) / 100);
                $indebtedRiel = $grandTotalRiel * ((100 - $clearingPayablePercentage) / 100);
            } else {
                $payment_status = 'PAID';
            }

            // Step 3: Create the sale order
            $saleOrder = SaleOrder::create([
                'payment_method' => $validated['payment_method'],
                'sale_invoice_number' => $this -> generateInvoiceNumber(),
                'order_date' => $validated['order_date'],
                'payment_status' => $payment_status,
                'order_status' => $validated['order_status'],
                'discount_percentage' => $validated['discount_percentage'],
                'discount_value_in_usd' => $discountValueUSD,
                'discount_value_in_riel' => $discountValueRiel,
                'tax_percentage' => $validated['tax_percentage'],
                'tax_value_in_usd' => $taxValueUSD,
                'tax_value_in_riel' => $taxValueRiel,
                'sub_total_in_usd' => $subTotalUSD,
                'sub_total_in_riel' => $subTotalRiel,
                'grand_total_with_tax_in_usd' => $grandTotalUSD,
                'grand_total_with_tax_in_riel' => $grandTotalRiel,
                'grand_total_without_tax_in_usd' => $subTotalUSD - $discountValueUSD,
                'grand_total_without_tax_in_riel' => $subTotalRiel - $discountValueRiel,
                'clearing_payable_percentage' => $validated['clearing_payable_percentage'],
                'indebted_in_usd' => $indebtedUSD,
                'indebted_in_riel' => $indebtedRiel,
                'customer_id' => $validated['customer_id'],
                'vender_id' => $vender_id,
            ]);

            // Step 4: Create ProductSaleOrder records for each product
            foreach ($validated['products'] as $productInput) {
                ProductSaleOrder::create([
                    'sale_order_id' => $saleOrder->id,
                    'product_id' => $productInput['id'],
                    'quantity_sold' => $productInput['quantity_sold'],
                ]);
            }

            return response()->json([
                'message' => 'Sale order created successfully.',
                'sale_order' => $saleOrder,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 400);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }




    public function update(Request $request, $id)
    {
        try {
            // Step 1: Validate the incoming request
            $validated = $this->validateAndExtractData($request);
            $vender_id = Auth::user() -> id;

            // Step 2: Find the existing sale order
            $saleOrder = SaleOrder::findOrFail($id);

            // Step 3: Return the old quantities to the product's remaining_quantity
            foreach ($saleOrder->products as $product) {
                $product->remaining_quantity += $product->pivot->quantity_sold;
                $product->save();
            }

            // Step 4: Detach all product relations for this sale order
            $saleOrder->products()->detach();

            // Step 5: Calculate new quantities and validate product stock
            $subTotalUSD = 0;
            $subTotalRiel = 0;

            foreach ($validated['products'] as $productInput) {
                $product = Product::findOrFail($productInput['id']);

                // Check if remaining quantity is sufficient
                if ($product->remaining_quantity < $productInput['quantity_sold']) {
                    throw new \Exception("Quantity of product ID {$product->id} is not enough.");
                }

                // Add product's price to sub-total
                $subTotalUSD += $product->unit_price_in_usd * $productInput['quantity_sold'];
                $subTotalRiel += $product->unit_price_in_riel * $productInput['quantity_sold'];

                // Update remaining quantity
                $product->remaining_quantity -= $productInput['quantity_sold'];
                $product->save();

                // Attach product to sale order
                $saleOrder->products()->attach($product->id, ['quantity_sold' => $productInput['quantity_sold']]);
            }

            // Step 6: Calculate discount, tax, and indebted values
            $discountValueUSD = $subTotalUSD * ($validated['discount_percentage'] / 100);
            $discountValueRiel = $subTotalRiel * ($validated['discount_percentage'] / 100);

            $taxValueUSD = $subTotalUSD * ($validated['tax_percentage'] / 100);
            $taxValueRiel = $subTotalRiel * ($validated['tax_percentage'] / 100);

            $grandTotalUSD = $subTotalUSD - $discountValueUSD + $taxValueUSD;
            $grandTotalRiel = $subTotalRiel - $discountValueRiel + $taxValueRiel;

            $clearingPayablePercentage = $validated['clearing_payable_percentage'];
            $indebtedUSD = 0;
            $indebtedRiel = 0;

            if ($clearingPayablePercentage == 0) {
                $payment_status = 'UNPAID';
                $indebtedUSD = $grandTotalUSD;
                $indebtedRiel = $grandTotalRiel;
            } elseif ($clearingPayablePercentage < 100) {
                $payment_status = 'INDEBTED';
                $indebtedUSD = $grandTotalUSD * ((100 - $clearingPayablePercentage) / 100);
                $indebtedRiel = $grandTotalRiel * ((100 - $clearingPayablePercentage) / 100);
            } else {
                $payment_status = 'PAID';
            }



            $payment_status = 'PAID';
            if ($request->clearing_payable_percentage == 0) {
                $payment_status = 'UNPAID';
            } elseif ($request->clearing_payable_percentage < 100) {
                $payment_status = 'INDEBTED';
            } elseif ($request->clearing_payable_percentage > 100) {
                $payment_status = 'OVERPAID';
            }

            // Step 7: Update the sale order
            $saleOrder->update([
                'payment_method' => $validated['payment_method'],
                'order_date' => $validated['order_date'],
                'payment_status' => $payment_status,
                'order_status' => $validated['order_status'],
                'discount_percentage' => $validated['discount_percentage'],
                'discount_value_in_usd' => $discountValueUSD,
                'discount_value_in_riel' => $discountValueRiel,
                'tax_percentage' => $validated['tax_percentage'],
                'tax_value_in_usd' => $taxValueUSD,
                'tax_value_in_riel' => $taxValueRiel,
                'sub_total_in_usd' => $subTotalUSD,
                'sub_total_in_riel' => $subTotalRiel,
                'grand_total_with_tax_in_usd' => $grandTotalUSD,
                'grand_total_with_tax_in_riel' => $grandTotalRiel,
                'grand_total_without_tax_in_usd' => $subTotalUSD - $discountValueUSD,
                'grand_total_without_tax_in_riel' => $subTotalRiel - $discountValueRiel,
                'clearing_payable_percentage' => $validated['clearing_payable_percentage'],
                'indebted_in_usd' => $indebtedUSD,
                'indebted_in_riel' => $indebtedRiel,
                'customer_id' => $validated['customer_id'],
                'vender_id' => $vender_id,
            ]);

            return response()->json([
                'message' => 'Sale order updated successfully.',
                'sale_order' => $saleOrder,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 400);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 400);
        }
    }


    public function recover($id)
    {
        try {
            $sale_order = SaleOrder::onlyTrashed()->findOrFail($id);
            $sale_order->restore();
            ProductSaleOrder::withTrashed()
                ->where('sale_order_id', $sale_order->id)
                ->restore();

            return response()->json(['message' => 'Sale order recovered successfully'], 200);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    public function delete ($id){
        try {  
            $sale_order = SaleOrder::findOrFail($id);
            foreach ($sale_order->products as $product) {
                $product->remaining_quantity += $product->pivot->quantity_sold;
                $product->save();

                ProductSaleOrder::where('sale_order_id', $sale_order->id)
                ->where('product_id', $product->id)
                ->update(['quantity_sold' => 0]);

                ProductSaleOrder::where('sale_order_id', $sale_order->id)
                    ->where('product_id', $product->id)
                    ->delete();
            }

            $sale_order->delete();

            return response()->json(['message' => 'Sale order deleted successfully'], 200);

        }catch (Exception $e){
                return response() -> json(['error' => $e -> getMessage()],400);
        }
    }


    public function export(Request $request)
    {
        try {
            $filters = $request->all();

            return Excel::download(new SaleOrderExport($request), 'sale_orders.xlsx');
        }  catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            return response()->json(['errors' => $e->failures()], 422); 
        }  catch (\Exception $e) {
            return response()->json(['error' => 'Import failed: ' . $e->getMessage()], 500);
        }
    }


}
