<?php

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Material;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\Sku;
use App\Models\AttributeValue;

class ProductsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attributes = [
            [
                'name' => '冰度',
                'values' => [
                    '热','温','去冰','少冰','多冰','少少冰',
                ],
            ],
            [
                'name' => '糖度',
                'values' => [
                    '少糖','少少糖','少少少糖','不另外加糖','多糖',
                ],
            ]
        ];

        $valueIds = [];
        foreach ($attributes as $item) {
            $attribute = Attribute::firstOrCreate(['name' => $item['name']]);
            foreach ($item['values'] as $value) {
                $value = $attribute->values()->firstOrCreate(['value' => $value]);
                $valueIds[] = $value->id;
            }
        }

        $materials = [
            [

                'no' => 30800001,
                'name' => '喜乐芝',
                'price' =>6,
            ],
            [
                'no' => 30800002,
                'name' => '轻芝士',
                'price' =>6,
            ],
            [
                'no' => 30800003,
                'name' => '奥利奥',
                'price' =>3,
            ],
            [
                'no' => 30800004,
                'name' => '脆珠',
                'price' =>3,
            ],
            [
                'no' => 30800005,
                'name' => '珍珠',
                'price' =>6,
            ],
            [
                'no' => 30800006,
                'name' => '蛋糕',
                'price' =>4,
            ],
            [
                'no' => 30800007,
                'name' => '鲜奶油',
                'price' =>6,
            ],
            [
                'no' => 30800012,
                'name' => '粉红爆谷米',
                'price' => 6,
            ]
        ];

        $materialIds = [];
        foreach ($materials as $item) {
            $material = Material::firstOrCreate(['no' => $item['no']], [
                'no' => $item['no'],
                'name' => $item['name'],
                'price' => $item['price'],
            ]);
            $materialIds[] = $material->id;
        }

        $products = json_decode(\Storage::get('products.json'), true);

        foreach ($products as $product) {
            $category = Category::firstOrCreate(['name' => $product['category']]);
            $productModel = Product::create([
                //'no' => $product['no'],
                'name' => $product['name'],
                'category_id' => $category->id,
                'is_listing' => true,
            ]);

            $productModel->values()->sync($valueIds);
            $productModel->materials()->sync($materialIds);

            Sku::firstOrCreate(['no' => $product['no']], [
                'no' => $product['no'],
                'name' => $product['name'],
                'price' => $product['price'],
                'product_id' => $productModel->id,
            ]);
        }
    }
}
