<?php

namespace DoniaShaker\MediaLibrary\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class SquareImageRule implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $manager = new ImageManager(new Driver());

        $image = $manager->read($value->path());

        if ($image->width() !== $image->height()) {
            $fail('The image must be square');
        }
    }
}
