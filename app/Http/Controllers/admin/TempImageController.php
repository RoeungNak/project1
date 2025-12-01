<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\TempImage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;


class TempImageController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,bmp,webp'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 400,
                'errors' => $validator->errors()
            ], 400);
        }

        $image = $request->file('image');
        $imageName = time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('uploads/temp'), $imageName);

        $tempImage = new TempImage();
        $tempImage->name  = $imageName;
        $tempImage->save();

        //Save image 
        $manager = new ImageManager(Driver::class);
        $img = $manager->read(public_path('uploads/temp/' . $tempImage->name));
        $img->coverDown(400, 450);
        $img->save(public_path('uploads/temp/thumb/' . $imageName));

        return response()->json([
            'status' => 201,
            'message' => 'Image has been uploaded successfully',
            'data' => $tempImage
        ], 201);
    }
    public function destroy($id)
    {
        $tempImage = TempImage::find($id);

        if (!$tempImage) {
            return response()->json([
                'status' => false,
                'message' => 'Image not found'
            ], 404);
        }

        // delete file from storage if needed
        if ($tempImage->name && file_exists(public_path('uploads/temp/' . $tempImage->name))) {
            unlink(public_path('uploads/temp/' . $tempImage->name));
        }

        $tempImage->delete();

        return response()->json([
            'status' => true,
            'message' => 'Temp image deleted successfully'
        ]);
    }
}
