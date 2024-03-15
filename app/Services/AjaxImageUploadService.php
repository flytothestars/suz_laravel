<?php

namespace App\Services;

use App\Models\AjaxImage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\ImageManagerStatic as Image;

class AjaxImageUploadService
{
    public function uploadImages($requestId, $files, $validation, $title = 'Прикрепление к заявке'): array
    {
        $validator = Validator::make($validation, [
            'request_id' => 'required|int|exists:suz_requests,id',
            'file' => ['required', 'array', 'max:3'],
            'file.*' => ['required', 'mimes:jpeg,jpg,png,gif', 'max:10000']
        ]);

        if ($validator->fails()) {
            return ['success' => false, 'message' => 'Упс! Возникла ошибка при загрузке фотографий. Проверьте формат.', 'trace' => $validator->getMessageBag()];
        }

        $imageCount = AjaxImage::where('request_id', $requestId)->count();
        if ($imageCount >= 3) {
            return ['success' => false, 'message' => 'Вы превысили количество фотографий по данной заявке в базе.'];
        }

        foreach ($files as $file) {
            $fileName = $requestId . '_' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.' . $file->getClientOriginalExtension();

            Image::make($file)->resize(400, 300)->save(public_path('images/' . $fileName), 90);

            AjaxImage::create([
                'title' => $title,
                'image' => $fileName,
                'request_id' => $requestId
            ]);
        }

        return ['success' => true, 'message' => 'Фотографии загружены успешно!'];
    }
}
