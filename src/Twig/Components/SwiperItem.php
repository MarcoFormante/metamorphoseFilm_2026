<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class SwiperItem
{
    public string $src;
    public string $title;
    public string $collab;
}
