<?php

declare(strict_types=1);

$sourcePath = dirname(__DIR__) . '/public/images/logo-final-dark2.png';
$publicDir = dirname(__DIR__) . '/public';

$source = imagecreatefrompng($sourcePath);

if ($source === false) {
    fwrite(STDERR, "Unable to read logo: {$sourcePath}\n");
    exit(1);
}

$width = imagesx($source);
$height = imagesy($source);

echo "Source: {$width}x{$height}\n";

/**
 * Find the right edge of the circular mark by scanning for content,
 * then stop before the wordmark starts (large gap of near-black).
 */
$threshold = 28;
$contentRight = 0;

for ($x = 0; $x < $width; $x++) {
    $hasContent = false;

    for ($y = 0; $y < $height; $y++) {
        $rgba = imagecolorat($source, $x, $y);
        $r = ($rgba >> 16) & 0xFF;
        $g = ($rgba >> 8) & 0xFF;
        $b = $rgba & 0xFF;

        if ($r > $threshold || $g > $threshold || $b > $threshold) {
            $hasContent = true;
            break;
        }
    }

    if ($hasContent) {
        $contentRight = $x;
    }
}

// Prefer the left emblem only: find first wide black gap after content begins.
$gapStart = null;
$gapNeeded = (int) max(24, $height * 0.04);
$run = 0;
$inContent = false;
$iconRight = (int) min($height, $width);

for ($x = 0; $x < $width; $x++) {
    $hasContent = false;

    for ($y = 0; $y < $height; $y++) {
        $rgba = imagecolorat($source, $x, $y);
        $r = ($rgba >> 16) & 0xFF;
        $g = ($rgba >> 8) & 0xFF;
        $b = $rgba & 0xFF;

        if ($r > $threshold || $g > $threshold || $b > $threshold) {
            $hasContent = true;
            break;
        }
    }

    if ($hasContent) {
        $inContent = true;
        $run = 0;
        $iconRight = $x;
    } elseif ($inContent) {
        $run++;

        if ($run >= $gapNeeded) {
            $gapStart = $x - $run;
            break;
        }
    }
}

$cropRight = $gapStart ?? min($iconRight + 8, $contentRight);
$cropSize = max($cropRight + 8, (int) ($height * 0.85));
$cropSize = min($cropSize, $height, $width);

// Center the square crop vertically; keep flush-left for the mark.
$cropX = 0;
$cropY = (int) max(0, ($height - $cropSize) / 2);

echo "Crop: {$cropSize}x{$cropSize} at ({$cropX},{$cropY}) gapStart=" . ($gapStart ?? 'null') . "\n";

$icon = imagecreatetruecolor($cropSize, $cropSize);
imagealphablending($icon, false);
imagesavealpha($icon, true);
$transparent = imagecolorallocatealpha($icon, 0, 0, 0, 127);
imagefilledrectangle($icon, 0, 0, $cropSize, $cropSize, $transparent);
imagealphablending($icon, true);
imagecopy($icon, $source, 0, 0, $cropX, $cropY, $cropSize, $cropSize);

function savePng(GdImage $image, string $path, int $size): void
{
    $canvas = imagecreatetruecolor($size, $size);
    imagealphablending($canvas, false);
    imagesavealpha($canvas, true);
    $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
    imagefilledrectangle($canvas, 0, 0, $size, $size, $transparent);
    imagealphablending($canvas, true);
    imagecopyresampled(
        $canvas,
        $image,
        0,
        0,
        0,
        0,
        $size,
        $size,
        imagesx($image),
        imagesy($image),
    );
    imagepng($canvas, $path);
    imagedestroy($canvas);
}

savePng($icon, $publicDir . '/apple-touch-icon.png', 180);
savePng($icon, $publicDir . '/favicon-32x32.png', 32);
savePng($icon, $publicDir . '/favicon-16x16.png', 16);

$sizes = [16, 32, 48];
$pngFrames = [];

foreach ($sizes as $size) {
    $frame = imagecreatetruecolor($size, $size);
    imagealphablending($frame, false);
    imagesavealpha($frame, true);
    $transparent = imagecolorallocatealpha($frame, 0, 0, 0, 127);
    imagefilledrectangle($frame, 0, 0, $size, $size, $transparent);
    imagealphablending($frame, true);
    imagecopyresampled(
        $frame,
        $icon,
        0,
        0,
        0,
        0,
        $size,
        $size,
        $cropSize,
        $cropSize,
    );

    ob_start();
    imagepng($frame);
    $pngFrames[] = ob_get_clean();
    imagedestroy($frame);
}

$ico = '';
$count = count($pngFrames);
$ico .= pack('vvv', 0, 1, $count);
$offset = 6 + (16 * $count);

foreach ($pngFrames as $png) {
    $ico .= pack('CCCCvvVV', 0, 0, 0, 0, 1, 32, strlen((string) $png), $offset);
    $offset += strlen((string) $png);
}

foreach ($pngFrames as $png) {
    $ico .= $png;
}

file_put_contents($publicDir . '/favicon.ico', $ico);

$png180 = file_get_contents($publicDir . '/apple-touch-icon.png');
$dataUri = 'data:image/png;base64,' . base64_encode((string) $png180);
$svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="180" height="180" viewBox="0 0 180 180">
  <image href="{$dataUri}" width="180" height="180" />
</svg>
SVG;

file_put_contents($publicDir . '/favicon.svg', $svg);

imagedestroy($icon);
imagedestroy($source);

echo "Wrote favicon.ico, favicon.svg, apple-touch-icon.png, favicon-16x16.png, favicon-32x32.png\n";
