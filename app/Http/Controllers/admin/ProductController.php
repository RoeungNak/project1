<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\TempImage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Models\ProductSize;
use Illuminate\Support\Facades\File;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('created_at', 'DESC')
            ->with(['product_images', 'product_sizes'])
            ->get();
        return response()->json([
            'status' => 200,
            'data' => $products
        ], 200);
    }
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'price' => 'required|numeric',
            'cost' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'category' => 'required|integer',
            'sku' => 'required|unique:products,sku',
            'status' => 'boolean',


        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }
        $product = new Product();
        $product->title = $request->title;
        $product->price = $request->price;
        $product->cost = $request->cost;
        $product->selling_price = $request->selling_price;
        $product->category_id = $request->category;
        $product->brand_id = $request->brand;
        $product->supplier_id = $request->supplier;
        $product->qty = $request->qty ?? 0;
        $product->barcode = $request->barcode;
        $product->compare_price = $request->compare_price;
        $product->description = $request->description;
        $product->discount = $request->discount ?? 0;
        $product->image = $request->image;
        $product->sku = $request->sku;
        $product->status = $request->status ?? 1;
        $product->save();

        if (!empty($request->sizes)) {
            foreach ($request->sizes as $sizeId) {
                $productSize = new ProductSize();
                $productSize->product_id = $product->id;
                $productSize->size_id = $sizeId;
                $productSize->save();
            }
        }



        if (!empty($request->gallery)) {
            foreach ($request->gallery as $key => $tempImageId) {
                $tempImage = TempImage::find($tempImageId);
                if ($tempImage) {
                    $extArray = explode('.', $tempImage->name);
                    $ext = end($extArray);

                    $imageName = $product->id . '-' . time() . '.' . $ext;
                    $manager = new ImageManager(Driver::class);
                    $img = $manager->read(public_path('uploads/temp/' . $tempImage->name));
                    $img->scaleDown(1200);
                    $img->save(public_path('uploads/products/large/' . $imageName));

                    $manager = new ImageManager(Driver::class);
                    $img = $manager->read(public_path('uploads/temp/' . $tempImage->name));
                    $img->coverDown(400, 460);
                    $img->save(public_path('uploads/products/small/' . $imageName));
                }
                $productImage = new ProductImage();
                $productImage->image = $imageName;
                $productImage->product_id = $product->id;
                $productImage->save();
                if ($key == 0) {
                    $product->image = $imageName;
                    $product->save();
                }
            }
        }

        return response()->json([
            'status' => 201,
            'message' => 'Product created successfully',
            'data' => $product
        ], 201);
    }
    public function show($id)
    {
        $product = Product::with(['product_images', 'product_sizes'])
            ->find($id);
        if ($product == null) {
            return response()->json([
                'status' => 404,
                'message' => 'Product not found'
            ], 404);
        }
        return response()->json([
            'status' => 200,
            'data' => $product
        ], 200);
    }
    public function update($id, Request $request)
    {
        $product = Product::find($id);
        if ($product == null) {
            return response()->json([
                'status' => 404,
                'message' => 'Product not found'
            ], 404);
        }
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'price' => 'required|numeric',
            'cost' => 'required|numeric',
            'category' => 'required|integer',
            'sku' => 'required|unique:products,sku,' . $id,
            'status' => 'boolean',



        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $product->title = $request->title;
        $product->price = $request->price;
        $product->cost = $request->cost;
        $product->selling_price = $request->selling_price;
        $product->category_id = $request->category;
        $product->brand_id = $request->brand;
        $product->supplier_id = $request->supplier;
        $product->qty = $request->qty ?? 0;
        $product->barcode = $request->barcode;
        $product->compare_price = $request->compare_price;
        $product->description = $request->description;
        $product->discount = $request->discount ?? 0;
        $product->image = $request->image;
        $product->sku = $request->sku;
        $product->status = $request->status ?? 1;

        $product->save();
        if (!empty($request->sizes)) {
            ProductSize::where('product_id', $product->id)->delete();
            foreach ($request->sizes as $sizeId) {
                $productSize = new ProductSize();
                $productSize->product_id = $product->id;
                $productSize->size_id = $sizeId;
                $productSize->save();
            }
        }

        return response()->json([
            'status' => 201,
            'message' => 'Product Upade successfully',
            'data' => $product
        ], 201);
    }
    public function destroy($id)
    {
        $product = Product::find($id);
        if ($product == null) {
            return response()->json([
                'status' => 404,
                'message' => 'Product not found'
            ], 404);
        }
        $product->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Product deleted successfully'
        ], 200);
    }
    public function saveProductImage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,bmp,webp|max:2048',
            'product_id' => 'required|exists:products,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $image = $request->file('image');
        $imageName = $request->product_id . '-' . time() . '.' . $image->getClientOriginalExtension();

        try {
            $manager = new ImageManager(new Driver());

            // Large image
            $largeImage = $manager->read($image->getRealPath());
            $largeImage->scaleDown(1200);
            $largeImage->save(public_path('uploads/products/large/' . $imageName));

            // Small image
            $smallImage = $manager->read($image->getRealPath());
            $smallImage->coverDown(400, 460);
            $smallImage->save(public_path('uploads/products/small/' . $imageName));

            // Save to database
            $productImage = new ProductImage();
            $productImage->image = $imageName;
            $productImage->product_id = $request->product_id;
            $productImage->save();

            return response()->json([
                'status' => 201,
                'message' => 'Image uploaded successfully',
                'data' => $productImage
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Image processing failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateDefaultImage(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'image' => 'required|string',
        ]);

        $product = Product::findOrFail($request->product_id);

        // save only filename (e.g. "2-1756535983.png")
        $filename = basename($request->image);
        $product->image = $filename;
        $product->save();

        // build correct image_url
        $product->image_url = asset('uploads/products/small/' . $filename);

        return response()->json([
            'status' => 200,
            'message' => 'Default image updated successfully',
            'data' => $product,
        ], 200);
    }
    public function destroyImage($id)
    {
        $productImage = ProductImage::find($id);
        if ($productImage == null) {
            return response()->json([
                'status' => 404,
                'message' => 'Product image not found'
            ], 404);
        }
        File::delete(public_path('uploads/products/large/' . $productImage->image));
        File::delete(public_path('uploads/products/small/' . $productImage->image));
        $productImage->delete();
        return response()->json([
            'status' => 202,
            'message' => 'Product image deleted successfully',
        ], 202);
    }
}
