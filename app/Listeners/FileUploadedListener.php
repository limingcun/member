<?php

namespace App\Listeners;

use Storage;
use App\Models\Image;
use Overtrue\LaravelUploader\Events\FileUploaded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class FileUploadedListener
{
    /**
     * Handle the event.
     *
     * @param  Overtrue\LaravelUploader\Events\FileUploaded  $event
     * @param  Array $result
     *
     * @return void
     */
    public function handle(FileUploaded $event)
    {
        $result = $event->result;

        \Storage::disk('qiniu')->put($result['relative_url'], \Storage::disk('public')->get($result['storage_path']));

        $image = $this->createImage($event->file, $result, $event->strategy);

        $result['image_id'] = $image->id;

        return $result;
    }

    /**
     * Create the image's record.
     *
     * @param  Illuminate\Http\UploadedFile $file
     * @param  array $result
     * @param  string $strategy
     * @return array
     */
    public function createImage($file, $result, $strategy)
    {
        list($width, $height) = getimagesize($file);

        $data = [
            'user_id' => auth()->guard('admin')->id() ?? auth()->guard('m_admin')->id(),
            'origin_name' => $result['original_name'],
            'path' => $result['relative_url'],
            'content_type' => $result['mime'],
            'width' => $width,
            'height' => $height,
            'size' => json_encode(['width' => $width, 'height' => $height]),
        ];

        return Image::create($data);
    }
}
