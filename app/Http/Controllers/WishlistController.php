<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Surfsidemedia\Shoppingcart\Facades\Cart;
use App\Models\Product;
class WishlistController extends Controller
{
    public function index()
    {
        $items = cart::instance('wishlist')->content();
        return view('wishlist',compact('items'));
    }
    public function add_to_wishlist(Request $request)
    {
        Cart::instance('wishlist')->add($request->id,$request->name, $request->quantity,$request->price)->associate('App\Models\Product');
        return redirect()->back();
    }
    public function remove_item($rowId)
    {
        cart::instance('wishlist')->remove($rowId);
        return redirect()->back();
    }
    public function empty_wishlist()
    {
        cart::instance('wishlist')->destroy();
        return redirect()->back();
    }
    public function move_to_cart($rowId)
    {

            $item = cart::instance('wishlist')->get($rowId);
            cart::instance('wishlist')->remove($rowId);
            Cart::instance('cart')->add($item->id, $item->name, $item->qty, $item->price)->associate( 'App\Models\Product');
            return redirect()->back();
    }

}
