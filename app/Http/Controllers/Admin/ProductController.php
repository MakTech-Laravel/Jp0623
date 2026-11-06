<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(private ProductService $products) {}

    public function index(Request $request): Response
    {
        $search = trim((string) $request->query('search', ''));

        return Inertia::render('admin/products/index', [
            'products' => $this->products->paginate($search),
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/products/create');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $this->products->create(
            $request->safe()->except(['image']),
            $request->hasFile('image') ? $request->file('image') : null,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Product created successfully.']);

        return redirect()->route('admin.products.index');
    }

    public function show(Product $product): Response
    {
        return Inertia::render('admin/products/show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product): Response
    {
        return Inertia::render('admin/products/edit', [
            'product' => $product,
        ]);
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->products->update(
            $product,
            $request->safe()->except(['image']),
            $request->hasFile('image') ? $request->file('image') : null,
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Product updated successfully.']);

        return redirect()->route('admin.products.index');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $this->products->delete($product);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Product deleted successfully.']);

        return redirect()->back();
    }
}
