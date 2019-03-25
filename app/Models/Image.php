<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\Image
 *
 * @property int $id
 * @property int $user_id
 * @property string $origin_name
 * @property string $path
 * @property string|null $width
 * @property string|null $height
 * @property string|null $size
 * @property string|null $content_type
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image whereContentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image whereOriginName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Image whereWidth($value)
 * @mixin \Eloquent
 */
class Image extends Model
{
    protected $guarded = ['id'];

//    function getPathAttribute($value)
//    {
//        if ($value) {
//            return env('QINIU_URL') . $value;
//        }
//    }
}
