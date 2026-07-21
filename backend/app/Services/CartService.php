<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Repositories\CartRepository;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function __construct(private readonly CartRepository $repository) {}

    public function getCart(User $user): Cart
    {
        $cart = $this->repository->getOrCreateCart($user);
        return $this->repository->getCartWithItems($cart);
    }

    public function addItem(User $user, int $productId, int $quantity): Cart
    {
        $product = Product::find($productId);

        if (! $product) {
            throw ValidationException::withMessages([
                'product_id' => ['Product not found.'],
            ]);
        }

        if ($product->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => ['Not enough stock available. Only ' . $product->stock_quantity . ' left.'],
            ]);
        }

        $cart = $this->repository->getOrCreateCart($user);
        $this->repository->addItem($cart, $productId, $quantity);

        return $this->repository->getCartWithItems($cart);
    }

    public function updateItem(User $user, int $itemId, int $quantity): Cart
    {
        $cart = $this->repository->getOrCreateCart($user);
        $item = CartItem::where('cart_id', $cart->id)->where('id', $itemId)->first();

        if (! $item) {
            throw ValidationException::withMessages([
                'item' => ['Cart item not found.'],
            ]);
        }

        $product = $item->product;
        if ($product->stock_quantity < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => ['Not enough stock available. Only ' . $product->stock_quantity . ' left.'],
            ]);
        }

        $this->repository->updateItem($cart, $itemId, $quantity);
        return $this->repository->getCartWithItems($cart);
    }

    public function removeItem(User $user, int $itemId): Cart
    {
        $cart = $this->repository->getOrCreateCart($user);
        $this->repository->removeItem($cart, $itemId);
        return $this->repository->getCartWithItems($cart);
    }

    public function clearCart(User $user): Cart
    {
        $cart = $this->repository->getOrCreateCart($user);
        $this->repository->clearCart($cart);
        return $this->repository->getCartWithItems($cart);
    }

    public function mergeGuestCart(User $user, array $items): Cart
    {
        $cart = $this->repository->getOrCreateCart($user);

        foreach ($items as $item) {
            $productId = $item['product_id'] ?? null;
            $quantity = $item['quantity'] ?? 1;

            if (! $productId) {
                continue;
            }

            $product = Product::find($productId);
            if (! $product || $product->stock_quantity < 1) {
                continue;
            }

            $this->repository->addItem($cart, $productId, $quantity);
        }

        return $this->repository->getCartWithItems($cart);
    }
}
