<?php

declare(strict_types=1);

use Switon\Simsimd\LengthException;
use Switon\Simsimd\ValueException;
use Switon\Simsimd\Vector;

try {
    Vector::cosine([1.0, 0.0], [1.0]);
} catch (LengthException $e) {
    echo $e->getMessage(), "\n";
}

try {
    Vector::cosine([1 => 0.2, 2 => 0.8], [0.2, 0.8]);
} catch (ValueException $e) {
    echo $e->getMessage(), "\n";
}

// Sample output.
// vectors must have the same length, 2 and 1
// vector must be a list
