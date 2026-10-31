<?php

declare(strict_types=1);

use Switon\Simsimd\Vector;

// Each element is [real, imag]. The return is [real, imag].
$left = [[1.0, 2.0], [3.0, 4.0]];
$right = [[5.0, 6.0], [7.0, 8.0]];

[$real, $imag] = Vector::complexDot($left, $right);
echo $real, "\n", $imag, "\n";

[$real, $imag] = Vector::complexConjugateDot($left, $right);
echo $real, "\n", $imag, "\n";

// Sample output. complexConjugateDot conjugates the second vector.
// -18
// 68
// 70
// -8
