<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\User;

class CartRepository
{
    public function getOrCreateCart(User $user): Cart
    {
        return Cart::firstOrCreate(
            ['user_id' => $user->id],
            ['user_id' => $user->id]
        );
    }

    public function addItem(Cart $cart, int $productId, int $quantity): CartItem
    {
        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->first();

        if ($item) {
            $item->quantity += $quantity;
            $item->save();
            return $item;
        }

        return CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $productId,
            'quantity' => $quantity,
        ]);
    }

    public function updateItem(Cart $cart, int $itemId, int $quantity): ?CartItem
    {
        $item = CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->first();

        if (! $item) {
            return null;
        }

        $item->quantity = $quantity;
        $item->save();

        return $item;
    }

    public function removeItem(Cart $cart, int $itemId): bool
    {
        return CartItem::where('cart_id', $cart->id)
            ->where('id', $itemId)
            ->delete() > 0;
    }

    public function clearCart(Cart $cart): void
    {
        CartItem::where('cart_id', $cart->id)->delete();
    }

    public function getCartWithItems(Cart $cart): Cart
    {
        return Cart::with(['items.product'])->find($cart->id);
    }
}
