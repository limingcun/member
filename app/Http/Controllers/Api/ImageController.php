<?php

namespace App\Http\Controllers\Api;

use App\Models\Image;
use Illuminate\Http\Request;
use App\Transformers\Api\ImageTransformer;

class ImageController extends Controller
{
    public function store(Request $request)
    {
        $user = $this->user();
        $image = new Image();

        $file = $request->file('image');

        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();

        // save path 按月分，以用户id开头
        $prefix = date('Y/m/');
        $name = $user->id.'_'.time().rand(1000, 9999).'.'. $extension;
        $targetPath = public_path('uploads/images/'.$prefix);

        $image->content_type = $file->getMimeType();
        $image->path= $prefix.$name;
        $image->user_id = $user->id;
        $image->origin_name = $file->getClientOriginalName();
        $image->size = $file->getSize();

        $size = getimagesize($file);
        list($width, $height) = $size;
        $image->width = $width;
        $image->height= $height;

        $file->move($targetPath, $name);
        $image->save();

        return $this->response->item($image, new ImageTransformer())
            ->setStatusCode(201);
    }
}
