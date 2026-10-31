<?php

declare(strict_types=1);

use Switon\Simsimd\Vector;

// Both lists are sorted. Lengths may differ. The default kind is u32.
echo Vector::intersect([1, 2, 3, 8], [2, 3, 4]), "\n";
echo Vector::intersect([1, 2, 3], [0, 2], 'u16'), "\n";

// Sample output. The values are counts.
// 2
// 1
