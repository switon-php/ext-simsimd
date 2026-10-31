<?php

declare(strict_types=1);

use Switon\Simsimd\Vector;

// Both id lists are sorted u16. Weights line up with those ids.
// The return is [match count, weight product].
$idsA = [1, 2, 3];
$idsB = [2, 3, 5];
[$count, $product] = Vector::sparseI16Dot($idsA, $idsB, [2, 3, 4], [6, 7, 8]);
echo $count, "\n", $product, "\n";

// Sample output. The product uses the first list's cursor for both weights.
// 2
// 53
