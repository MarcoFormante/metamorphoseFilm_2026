<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\File;

interface ImageResizerInterface
{
    public function saveImageResized(File $img, string $newPath,?int $fit = 500): void;
}
