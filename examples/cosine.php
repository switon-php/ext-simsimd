<?php

declare(strict_types=1);

use Switon\Simsimd\Vector;

// Embedding vectors. f32 is the kernel for that storage.
$left = [0.12, -0.4, 0.8, 0.05];
$right = [0.10, -0.5, 0.7, 0.02];

echo Vector::cosine($left, $right, 'f32'), "\n";

// Sample output, Apple Silicon. The last digits follow the f32 kernel.
// 0.012701153185568
