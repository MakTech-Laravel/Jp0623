<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$dir = $root.'/public/images';

@mkdir($dir.'/machines', 0777, true);
@mkdir($dir.'/products', 0777, true);
@mkdir($dir.'/hero', 0777, true);

$ext = function_exists('imagewebp') ? 'webp' : 'png';

/**
 * @param  array{0:int,1:int,2:int}  $bg
 * @param  array{0:int,1:int,2:int}  $accent
 */
function make_placeholder(string $path, int $w, int $h, array $bg, array $accent, string $label): void
{
    $im = imagecreatetruecolor($w, $h);
    $bgc = imagecolorallocate($im, $bg[0], $bg[1], $bg[2]);
    $acc = imagecolorallocate($im, $accent[0], $accent[1], $accent[2]);
    $txt = imagecolorallocate($im, 255, 255, 255);
    imagefilledrectangle($im, 0, 0, $w, $h, $bgc);
    imagefilledrectangle($im, 24, 24, $w - 25, $h - 25, $acc);
    $font = 5;
    $tw = imagefontwidth($font) * strlen($label);
    $th = imagefontheight($font);
    imagestring($im, $font, (int) (($w - $tw) / 2), (int) (($h - $th) / 2), $label, $txt);

    if (str_ends_with($path, '.webp') && function_exists('imagewebp')) {
        imagewebp($im, $path, 82);
    } else {
        imagepng($im, preg_replace('/\.webp$/', '.png', $path) ?: $path);
    }

    imagedestroy($im);
}

$petrol = [15, 45, 48];
$brass = [184, 148, 74];
$sunk = [232, 236, 232];

make_placeholder("{$dir}/machines/fallback.{$ext}", 640, 800, $sunk, $petrol, 'MACHINE');

$machineKinds = ['combo', 'healthy', 'snack', 'drink', 'touch', 'food', 'slim', 'cooler', 'market'];
foreach ($machineKinds as $kind) {
    copy("{$dir}/machines/fallback.{$ext}", "{$dir}/machines/{$kind}.{$ext}");
}

make_placeholder("{$dir}/products/fallback.{$ext}", 512, 512, $sunk, $brass, 'PRODUCT');

$productKinds = ['drink', 'chips', 'sweets', 'nuts', 'pastry', 'bars', 'healthy', 'energy', 'food', 'coffee'];
foreach ($productKinds as $kind) {
    copy("{$dir}/products/fallback.{$ext}", "{$dir}/products/{$kind}.{$ext}");
}

make_placeholder("{$dir}/hero/combo.{$ext}", 640, 800, $petrol, $brass, 'COMBO');
make_placeholder("{$dir}/hero/cooler.{$ext}", 640, 800, $petrol, $brass, 'COOLER');

echo "ext={$ext}\n";
echo "seeded catalog images\n";
