<?php

declare(strict_types=1);

use Switon\Simsimd\Vector;

// c is row-major n by n. bilinear is aᵀCb. mahalanobis is the square root of (a-b)ᵀC(a-b).
$a = [1.0, 2.0];
$b = [3.0, 4.0];
$identity = [1.0, 0.0, 0.0, 1.0];

echo Vector::bilinear($a, $b, $identity), "\n";
echo Vector::mahalanobis([3.0, 4.0], [0.0, 0.0], $identity), "\n";

// Sample output. 11 is exact. The square root follows the f64 kernel.
// 11
// 4.9970412254333
