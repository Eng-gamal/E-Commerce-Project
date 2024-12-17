<?php

namespace App\Http\Controllers;
use App\Models\Brand;
use App\Models\category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Transaction;

use App\Models\OrderItem;
use App\Models\Slide;
use Carbon\Carbon;
use illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Intervention\Image\Laravel\Facades\Image;
use Ramsey\Uuid\Codec\OrderedTimeCodec;

class AdminController extends Controller
{

    public function index()
    {
        return view('admins.index');
    }
//brands
    public function brands()
    {
        $brands = brand::orderBy('id','DESC')->paginate(10);
        return view('admins.brands',compact('brands'));
    }

    public function add_brand()
    {

        return view('admins.brand-add');
    }

    public function brand_store(Request $request)
    {
        $request->validate([
            'name'=>'required',
            'slug'=>'required|unique:brands,slug',
            'image'=>'mimes:png,jpg,jpeg|max:2048'
        ]);

        $brand = new Brand();
        $brand->name= $request->name;
        $brand->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp .'-'.$file_extention;
        $this->GeneratBrandThumbailsImage($image,$file_name);
        $brand->image = $file_name;
        $brand->save();
        return redirect()->route('admin.brands')->with('status,Brand has been added succesfully');
    }

    public function brand_edit($id)
    {
        $brand = Brand::find($id);
        return view("admins.brand_edit",compact('brand'));
    }
    public function brand_update(Request $request)
    {
        $request->validate([
            'name'=>'required',
            'slug'=>'required|unique:brands,slug',
            'image'=>'mimes:png,jpg,jpeg|max:2048'
        ]);
        $brand = Brand::find($request->id);
        $brand->name= $request->name;
        $brand->slug = Str::slug($request->name);
        if($request->hasFile('image')){
            if(File::exists( public_path('uploads/brands').'/'.$brand->image))
            {
                File::delete( public_path('uploads/brands').'/'.$brand->image);
            }

        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp .'-'.$file_extention;
        $this->GeneratBrandThumbailsImage($image,$file_name);
        $brand->image = $file_name;
    }
        $brand->save();
        return redirect()->route('admins.brands')->with('status,Brand has been updated succesfully');

    }
    public function GeneratBrandThumbailsImage($image,$imageName)
    {
        $destinationPath = public_path('uploads/brands');
        $img = Image::read($image->path());
        $img->cover(124,124,"top");
        $img->resize(124,124,function($constraint){
            $constraint->sapectRatio();
        })->save($destinationPath.'/'.$imageName);
    }

    public function destroy($id)
    {
        $brand = Brand::find($id);
        if(File::exists( public_path('uploads/brands').'/'.$brand->image))
        {
            File::delete( public_path('uploads/brands').'/'.$brand->image);
        }
        $brand->Delete();

        return redirect()->route('admin.brands')->with('status','Brand has been deleted succesfully');
    }

//category
    public function categories()
    {
        $categories = category::orderBy('id','DESC')->paginate(10);
        return view('admins.categories',compact('categories'));
    }

    public function category_add()
    {
        return view('admins.category_add');
    }

    public function Category_store(Request $request)
    {

        $request->validate([
            'name'=>'required',
            'slug'=>'required|unique:categories,slug',
            'image'=>'mimes:png,jpg,jpeg|max:2048'
        ]);


        $category = new category();
        $category->name= $request->name;
        $category->slug = Str::slug($request->name);
        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp .'-'.$file_extention;
        $this->GeneratCategoryThumbailsImage($image,$file_name);
        $category->image = $file_name;
        $category->save();
        return redirect()->route('admin.categories')->with('status,category has been added succesfully');
    }

    public function Category_edit($id)
    {
        $category = category::find($id);
        return view('admins.category_edit',compact('category'));
    }

    public function category_update(Request $request)
    {
        $request->validate([
            'name'=>'required',
            'slug'=>'required|unique:categories,slug',
            'image'=>'mimes:png,jpg,jpeg|max:2048'
        ]);
        $category = category::find($request->id);
        $category->name= $request->name;
        $category->slug = Str::slug($request->name);
        if($request->hasFile('image')){
            if(File::exists( public_path('uploads/categories').'/'.$category->image))
            {
                File::delete( public_path('uploads/categories').'/'.$category->image);
            }

        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp .'-'.$file_extention;
        $this->GeneratcategoryThumbailsImage($image,$file_name);
        $category->image = $file_name;
    }
        $category->save();
        return redirect()->route('admin.categories')->with('status,category has been updated succesfully');
    }




    public function GeneratCategoryThumbailsImage($image,$imageName)
    {
        $destinationPath = public_path('uploads/categories');
        $img = Image::read($image->path());
        $img->cover(124,124,"top");
        $img->resize(124,124,function($constraint){
            $constraint->sapectRatio();
        })->save($destinationPath.'/'.$imageName);
    }

    public function Delete($id)
    {
        $category = category::find($id);
        if(File::exists( public_path('uploads/categories').'/'.$category->image))
        {
            File::delete( public_path('uploads/categories').'/'.$category->image);
        }
        $category->Delete();

        return redirect()->route('admin.categories')->with('status','category has been deleted succesfully');
    }

//products
    public function products()
    {
        $products = product::orderBy('created_at','desc')->paginate(10);
        return view('admins.products',compact('products'));

    }

    public function product_add()
    {

        $categories = Category::select('id', 'name')->orderBy('name', 'desc')->get();
        $brands = Brand::select('id','name')->orderBy('name')->get();

        return view('admins.product_add' ,compact('categories','brands',));
    }
    public function product_store(Request $request)
    {

        $request->validate([
            'name' =>'required',
            'slug'=>'required|unique:products,slug',
            'short_description'=>'required',
            'description'=>'required',
            'regular_price'=>'required',
            'sale_price'=>'required',
            'SKU'=>'required',
            'stock_status'=>'required',
            'featured'=>'required',
            'quantity'=>'required',
            'image'=>'required|mimes:png,jpg,jpeg|max:2048',
            'category_id'=>'required',
            'brand_id'=>'required',
        ]);
        $product = new Product();
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU  = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity= $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        $current_timestamp = Carbon::now()->timestamp;

        if($request->hasFile('image'))
        {
            $image = $request->File('image');
            $imageName =  $current_timestamp . '.' . $image->extension();
            $this->GeneratProductThumbailsImage($image , $imageName);
            $product->image = $imageName ;

        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;

        if($request->hasFile('images'))
        {
            $allowedfileExtion = ['jpg','png','jpeg'];
            $files = $request->file('images');
            foreach($files as $file)
            {
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array(  $gextension,$allowedfileExtion);
                if($gcheck)
                {
                    $gfileName = $current_timestamp . "-"."." . $gextension ;
                    $this->GeneratProductThumbailsImage($file,$gfileName);
                    array_push($gallery_arr,$gfileName);
                    $counter = $counter + 1 ;
                }

            }
            $gallery_images = implode(',',$gallery_arr);
            $product->images = $gallery_images;
        }

        $product->save();
        return redirect()->route('admin.products')->with('status','product has been added successfully');


    }


    public function product_edit($id)
    {
        $product = product::find($id);
        $categories = category::select('id','name')->orderBy('name')->get();
        $brands = Brand::select('id','name')->orderBy('name')->get();
        return view('admins.product_edit',compact('product','categories','brands'));

    }

    public function product_update(Request $request)
    {

        $request->validate([
            'name' =>'required',
            'slug'=>'required|unique:products,slug',
            'short_description'=>'required',
            'description'=>'required',
            'regular_price'=>'required',
            'sale_price'=>'required',
            'SKU'=>'required',
            'stock_status'=>'required',
            'featured'=>'required',
            'quantity'=>'required',
            'image'=>'mimes:png,jpg,jpeg|max:2048',
            'category_id'=>'required',
            'brand_id'=>'required',
        ]);
        $product = product::find($request->id);
        $product->name = $request->name;
        $product->slug = Str::slug($request->name);
        $product->short_description = $request->short_description;
        $product->description = $request->description;
        $product->regular_price = $request->regular_price;
        $product->sale_price = $request->sale_price;
        $product->SKU  = $request->SKU;
        $product->stock_status = $request->stock_status;
        $product->featured = $request->featured;
        $product->quantity= $request->quantity;
        $product->category_id = $request->category_id;
        $product->brand_id = $request->brand_id;

        $current_timestamp = Carbon::now()->timestamp;

        if($request->hasFile('image'))
        {
            if(file::exists(public_path('uploads/products').'/' . $product->image))
            {
                file::delete(public_path('uploads/products').'/' . $product->image);
            }
            if(file::exists(public_path('uploads/products/thumbnails').'/' . $product->image))
            {
                file::delete(public_path('uploads/products/thumbnails').'/' . $product->image);
            }
            $image = $request->File('image');
            $imageName =  $current_timestamp . '.' . $image->extension();
            $this->GeneratProductThumbailsImage($image , $imageName);
            $product->image = $imageName ;

        }

        $gallery_arr = array();
        $gallery_images = "";
        $counter = 1;

        if($request->hasFile('images'))
        {
            foreach(explode(',',$product->images) as $ofile)
            {
                if(file::exists(public_path('uploads/products').'/' . $ofile))
                {
                    file::delete(public_path('uploads/products').'/' . $ofile);
                }
                if(file::exists(public_path('uploads/products/thumbnails').'/' . $ofile))
                {
                    file::delete(public_path('uploads/products/thumbnails').'/' . $ofile);
                }
            }
            $allowedfileExtion = ['jpg','png','jpeg'];
            $files = $request->file('images');
            foreach($files as $file)
            {
                $gextension = $file->getClientOriginalExtension();
                $gcheck = in_array(  $gextension,$allowedfileExtion);
                if($gcheck)
                {
                    $gfileName = $current_timestamp . "-"."." . $gextension ;
                    $this->GeneratProductThumbailsImage($file,$gfileName);
                    array_push($gallery_arr,$gfileName);
                    $counter = $counter + 1 ;
                }

            }
            $gallery_images = implode(',',$gallery_arr);
            $product->images = $gallery_images;
        }
        $product->save();
        return redirect()->route('admin.products')->with('status','product has been updated successfully');


    }


    public function GeneratProductThumbailsImage($image , $imageName)
    {
        $destinationPathThumbnail = public_path('uploads/products/thumbnails');
        $destinationPath = public_path('uploads/products');
        $img = Image::read($image->path());
        $img->cover(540,689,"top");
        $img->resize(540,689,function($constraint){
            $constraint->sapectRatio();
        })->save($destinationPath.'/'.$imageName);

        $img->resize(104,104,function($constraint){
            $constraint->sapectRatio();
        })->save($destinationPathThumbnail.'/'.$imageName);
    }


    public function product_delete($id)
    {
        $product = product::find($id);
        if(File::exists( public_path('uploads/categories').'/'.$product->image))
        {
            File::delete( public_path('uploads/categories').'/'.$product->image);
        }
        $product->Delete();

        return redirect()->route('admin.products')->with('status','product has been deleted succesfully');
    }

    public function coupons()
    {
        $coupons = Coupon::orderBy('expiry_date','DESC')->paginate(12);
        return view('admins.coupons', compact('coupons'));
    }
    public function coupon_add()
    {
        return view('admins.coupon-add');
    }
    public function coupon_store(Request $request)
    {
        $request->validate([

                'code'=>'required',
                'type'=>'required',
                'value'=>'required|numeric',
                'cart_value'=>'required|numeric',
                'expiry_date'=>'required|date',
        ]);

        $coupon =  new coupon();
        $coupon->code = $request->code;
        $coupon->type = $request->type;
        $coupon->value = $request->value;
        $coupon->cart_value = $request->cart_value;
        $coupon->expiry_date = $request->expiry_date;
        $coupon->save();
        return redirect()->route('admin.coupons')->with('status','Coupons has been added successfully');
    }
    public function coupon_edit($id)
    {
        $coupon = coupon::find($id);
        return view('admins.coupon-edit',compact('coupon'));
    }
    public function coupon_update(Request $request)
    {
        $request->validate([

            'code'=>'required',
            'type'=>'required',
            'value'=>'required|numeric',
            'cart_value'=>'required|numeric',
            'expiry_date'=>'required|date',
    ]);

    $coupon =  coupon::find($request->id);
    $coupon->code = $request->code;
    $coupon->type = $request->type;
    $coupon->value = $request->value;
    $coupon->cart_value = $request->cart_value;
    $coupon->expiry_date = $request->expiry_date;
    $coupon->save();
    return redirect()->route('admin.coupons')->with('status','Coupons has been updated successfully');

    }
    public function coupon_delete($id)
    {
        $coupon = coupon::find($id);
        $coupon->delete();
        return redirect()->route('admin.coupons')->with('status', 'Coupons has been updated successfully');
    }

    public function orders()
    {
        $orders = Order::orderBy('created_at','DESC')->paginate(12);
        return view('admins.order',compact('orders'));
    }

    public function order_details($order_id)
    {
        $order = Order::find($order_id);
        $orderItems = OrderItem::where('order_id',$order_id)->orderBy('id')->paginate(12);
        $transaction = Transaction::where('order_id',$order_id)->first();
        return view('admins.order-details',compact('order','orderItems','transaction'));

    }
    public function update_order_status(Request $request)
    {
        // التحقق من وجود الطلب
        $order = Order::find($request->order_id);
        if (!$order) {
            return back()->with('error', 'Order not found.');
        }

        // تحديث حالة الطلب
        $order->status = $request->order_status;
        if ($request->order_status == 'delivered') {
            $order->delivered_date = Carbon::now();
        } elseif ($request->order_status == 'canceled') {
            $order->canceled_date = Carbon::now();
        }
        $order->save();

        // تحديث المعاملة إذا تم تسليم الطلب
        if ($request->order_status == 'delivered') {
            $transaction = Transaction::where('order_id', $request->order_id)->first();
            if ($transaction) {
                $transaction->status = 'approved';
                $transaction->save();
            } else {
                return back()->with('error', 'Transaction not found for this order.');
            }
        }

        return back()->with("status", "Status changed successfully!");
    }


    public function slides()
    {
        $slides = Slide::orderBy('id','DESC')->paginate(12);
        return view('admins.slides',compact('slides'));
    }

    public function slide_add()
    {
        return view('admins.slide-add');
    }

    public function slide_store(Request $request)
    {
        $request->validate([
            'tagline' => 'required',
            'title' => 'required',
            'subtitle' => 'required',
            'link' => 'required',
            'status' => 'required',
            'image' => 'required|mimes:png,jpg,jpeg,webp|max:2048'
        ]);

        $slide = new Slide();

        $slide->tagline = $request->tagline;
        $slide->title = $request->title;
        $slide->subtitle = $request->subtitle;
        $slide->link = $request->link;
        $slide->status = $request->status;

        $image = $request->file('image');
        $file_extention = $request->file('image')->extension();
        $file_name = Carbon::now()->timestamp .'.'.$file_extention;
        $this->GeneratSlideThumbailsImage($image,$file_name);
        $slide->image = $file_name;
        $slide->save();

        return redirect()->route('admin.slides')->with("status", "Slide added successfully!");
    }

    public function GeneratSlideThumbailsImage($image,$imageName)
    {
        $destinationPath = public_path('uploads/slides');
        $img = Image::read($image->path());
        $img->cover(400,690,"top");
        $img->resize(400,690,function($constraint){
            $constraint->sapectRatio();
        })->save($destinationPath.'/'.$imageName);
    }
}




