<?php

use Illuminate\Database\Seeder;
use App\Models\Image;
use App\Models\Product;

class ProductImageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $images = [
            [
                'user_id' => 1,
                'origin_name' => 'product_image1.jpg',
                'path' => '/storage/product_image1.jpg',
                'width' => '1200',
                'height' => '900',
                'size' => '454k',
                'content_type' => 'image/jpg',
            ],
            [
                'user_id' => 1,
                'origin_name' => 'product_image2.jpg',
                'path' => '/storage/product_image2.jpg',
                'width' => '1200',
                'height' => '900',
                'size' => '229k',
                'content_type' => 'image/jpg',
            ]
        ];

        $imageIds = [];
        foreach ($images as $image) {
            $image = Image::create($image);
            $imageIds[] = $image->id;
        }

        $products = Product::all();
        foreach ($products as $product) {
            $product->images()->sync($imageIds);
        }
    }
}
