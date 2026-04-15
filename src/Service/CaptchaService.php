<?php

namespace App\Service;

/**
 * Service de génération de CAPTCHA
 * Reproduit le comportement du CAPTCHA Java :
 * - texte alphanumérique aléatoire (5-6 chars)
 * - fond sombre, texte vert, bruit visuel
 * - rotation légère des lettres
 * 
 * Supporte GD (PNG) et un fallback SVG (si GD est manquant).
 */
class CaptchaService
{
    // Caractères sans ambiguïté (pas de 0/O, 1/l/I, etc.)
    private const CHARSET = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';

    /**
     * Vérifie si l'extension GD est disponible
     */
    public function hasGd(): bool
    {
        return extension_loaded('gd');
    }

    /**
     * Génère un texte CAPTCHA aléatoire
     */
    public function generateText(int $length = 0): string
    {
        if ($length <= 0) {
            $length = random_int(5, 6);
        }

        $text = '';
        $max = strlen(self::CHARSET) - 1;

        for ($i = 0; $i < $length; $i++) {
            $text .= self::CHARSET[random_int(0, $max)];
        }

        return $text;
    }

    /**
     * Génère une image CAPTCHA
     * 
     * @return array ['content' => string, 'type' => 'image/png'|'image/svg+xml']
     */
    public function generate(string $text): array
    {
        if ($this->hasGd()) {
            return [
                'content' => $this->generateGdImage($text),
                'type'    => 'image/png'
            ];
        }

        return [
            'content' => $this->generateSvgImage($text),
            'type'    => 'image/svg+xml'
        ];
    }

    /**
     * Fallback SVG (Sans dépendance GD)
     * Génère une image vectorielle avec bruit et rotations
     */
    private function generateSvgImage(string $text): string
    {
        $width = 220;
        $height = 60;
        $svg = "<svg width='$width' height='$height' viewBox='0 0 $width $height' xmlns='http://www.w3.org/2000/svg'>";
        
        // Fond sombre
        $svg .= "<rect width='100%' height='100%' fill='#1a2529' />";
        
        // Bruit : petits cercles (points)
        for ($i = 0; $i < 60; $i++) {
            $cx = random_int(0, $width);
            $cy = random_int(0, $height);
            $r = random_int(1, 2);
            $opacity = random_int(1, 4) / 10;
            $svg .= "<circle cx='$cx' cy='$cy' r='$r' fill='#4caf50' fill-opacity='$opacity' />";
        }

        // Bruit : lignes courbes (beziers)
        for ($i = 0; $i < 3; $i++) {
            $x1 = random_int(0, $width / 2);
            $y1 = random_int(0, $height);
            $x2 = random_int($width / 2, $width);
            $y2 = random_int(0, $height);
            $qx = random_int(0, $width);
            $qy = random_int(0, $height);
            $svg .= "<path d='M $x1 $y1 Q $qx $qy $x2 $y2' stroke='#4caf50' stroke-opacity='0.15' fill='none' stroke-width='2' />";
        }

        // Texte
        $chars = str_split($text);
        $totalWidth = count($chars) * 32;
        $startX = ($width - $totalWidth) / 2 + 10;
        
        foreach ($chars as $index => $char) {
            $x = $startX + ($index * 32);
            $y = 40 + random_int(-4, 4);
            $angle = random_int(-20, 20);
            $opacity = random_int(8, 10) / 10;
            $svg .= "<text x='$x' y='$y' fill='#4caf50' fill-opacity='$opacity' font-family='Arial, sans-serif' font-weight='bold' font-size='32' transform='rotate($angle, $x, $y)' style='user-select:none;'>$char</text>";
        }

        $svg .= "</svg>";
        return $svg;
    }

    /**
     * Génération PNG avec GD
     */
    private function generateGdImage(string $text): string
    {
        $width = 220;
        $height = 60;
        $image = imagecreatetruecolor($width, $height);

        $bgColor = imagecolorallocate($image, 26, 37, 41);
        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $bgColor);

        // Bruit : points
        for ($i = 0; $i < 150; $i++) {
            $noiseColor = imagecolorallocate($image, random_int(30, 80), random_int(40, 100), random_int(50, 90));
            imagesetpixel($image, random_int(0, $width - 1), random_int(0, $height - 1), $noiseColor);
        }

        // Bruit : lignes
        for ($i = 0; $i < 6; $i++) {
            $lineColor = imagecolorallocate($image, random_int(30, 70), random_int(50, 90), random_int(40, 80));
            imageline($image, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $lineColor);
        }

        $fontSize = 5;
        $charWidth = imagefontwidth($fontSize);
        $charHeight = imagefontheight($fontSize);
        $startX = (int)(($width - (strlen($text) * ($charWidth + 10))) / 2);
        $startY = (int)(($height - $charHeight) / 2);

        for ($i = 0; $i < strlen($text); $i++) {
            $char = $text[$i];
            $green = imagecolorallocate($image, random_int(60, 100), random_int(160, 210), random_int(60, 100));
            $x = $startX + $i * ($charWidth + 10);
            $y = $startY + random_int(-5, 5);

            $charImg = imagecreatetruecolor($charWidth + 4, $charHeight + 4);
            $charBg = imagecolorallocate($charImg, 26, 37, 41);
            imagefilledrectangle($charImg, 0, 0, $charWidth + 3, $charHeight + 3, $charBg);
            imagechar($charImg, $fontSize, 2, 2, $char, $green);

            $angle = random_int(-20, 20);
            $transparent = imagecolorallocatealpha($charImg, 26, 37, 41, 127);
            $rotated = imagerotate($charImg, $angle, $transparent);
            imagesavealpha($rotated, true);

            $rw = imagesx($rotated);
            $rh = imagesy($rotated);
            imagecopymerge($image, $rotated, $x - (int)(($rw - $charWidth) / 2), $y - (int)(($rh - $charHeight) / 2), 0, 0, $rw, $rh, 95);

            imagedestroy($charImg);
            imagedestroy($rotated);
        }

        ob_start();
        imagepng($image);
        $data = ob_get_clean();
        imagedestroy($image);

        return $data;
    }

    /**
     * @deprecated Use generate($text) instead
     */
    public function generateImage(string $text): string
    {
        return $this->generate($text)['content'];
    }
}
