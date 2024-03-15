<?php

namespace App\Services;

use Illuminate\Support\Facades\Session;

class CaptchaService
{

    private string $fontPath;
    private int $fontSize;
    private int $width;
    private int $height;

    public function __construct(string $fontPath = 'fonts/OpenSans-Semibold.ttf', int $fontSize = 24, int $width = 160, int $height = 40)
    {
        $this->fontPath = $fontPath;
        $this->fontSize = $fontSize;
        $this->width = $width;
        $this->height = $height;
    }

    public function generateCaptcha(): array
    {
        $captchaCode = generateRandomCaptcha();
        Session::put('captcha_code', $captchaCode);

        // Generate the captcha image using Intervention Image
        $captchaImage = $this->generateCaptchaImage($captchaCode);

        return compact('captchaCode', 'captchaImage');
    }

    public function generateCaptchaImage(string $captchaCode): string
    {
        $image = $this->createBlankImage();
        $this->drawTextOnImage($image, $captchaCode);
        ob_start();
        imagepng($image);
        $imageData = ob_get_clean();
        imagedestroy($image);
        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    private function createBlankImage()
    {
        $image = imagecreatetruecolor($this->width, $this->height);
        $backgroundColor = imagecolorallocate($image, 245, 245, 245);
        imagefill($image, 0, 0, $backgroundColor);
        return $image;
    }

    private function drawTextOnImage($image, string $text)
    {
        $textColor = imagecolorallocate($image, 51, 51, 51);
        $textX = ($this->width - ($this->fontSize * strlen($text))) / 2;
        $textY = $this->height / 2 + ($this->fontSize / 2);
        imagettftext($image, $this->fontSize, 0, $textX, $textY, $textColor, $this->fontPath, $text);
    }
}
