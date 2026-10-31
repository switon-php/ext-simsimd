<?php

declare(strict_types=1);

use Switon\Simsimd\Vector;

// Binary embeddings. One byte is eight bits. Length is the byte length.
$left = "\x0f\xf0";
$right = "\xff\x00";

echo Vector::hamming($left, $right), "\n";
echo Vector::jaccard($left, $right), "\n";

// Sample output.
// 8
// 0.66666666666667
