<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    private string $dataFile;

    public function __construct()
    {
        $this->dataFile = storage_path('app/products.json');
    }

    public function index()
    {
        $products = $this->readProducts();
        return view('products.index', compact('products'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'price'    => 'required|numeric|min:0',
        ]);

        $products = $this->readProducts();

        $products[] = [
            'id' => (string) Str::uuid(),
            'name' => $data['name'],
            'quantity' => (int) $data['quantity'],
            'price' => (float) $data['price'],
            'created_at' => now()->format('Y-m-d H:i:s'),
        ];

        $this->writeProducts($products);

        return response()->json([
            'products' => $products,
            'total'    => $this->grandTotal($products),
        ]);
    }

    public function update(Request $request, string $id)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'price'    => 'required|numeric|min:0',
        ]);

        $products = $this->readProducts();

        foreach ($products as &$product) {
            if ($product['id'] === $id) {
                $product['name'] = $data['name'];
                $product['quantity'] = (int) $data['quantity'];
                $product['price'] = (float) $data['price'];
                break;
            }
        }

        $this->writeProducts($products);

        return response()->json([
            'products' => $products,
            'total'    => $this->grandTotal($products),
        ]);
    }

    private function readProducts(): array
    {
        if (!file_exists($this->dataFile)) {
            return [];
        }

        $json = file_get_contents($this->dataFile);
        return json_decode($json, true) ?? [];
    }

    private function writeProducts(array $products): void
    {
        file_put_contents(
            $this->dataFile,
            json_encode($products, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    private function grandTotal(array $products): float
    {
        return array_sum(array_map(
            fn($p) => $p['quantity'] * $p['price'],
            $products
        ));
    }
}
