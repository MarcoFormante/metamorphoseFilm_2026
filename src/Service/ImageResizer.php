<?php

namespace App\Service;

use Spatie\Image\Image;
use Spatie\Image\Enums\Fit;
use Symfony\Component\HttpFoundation\File\File;

final class ImageResizer implements ImageResizerInterface
{
    public function saveImageResized(File $img, string $newPath,?int $fit = 500): void
    {
        Image::load($img->getPathname())
            ->format('webp')
            ->fit(Fit::Max, $fit)
            ->quality(80)
            ->optimize()
            ->save($newPath);
    }
}
