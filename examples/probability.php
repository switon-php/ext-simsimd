<?php

declare(strict_types=1);

use Switon\Simsimd\Vector;

$left = [0.9, 0.1];
$right = [0.2, 0.8];

// f64 is the default. i8 is rejected. Jensen-Shannon is symmetric. KL is not.
echo Vector::jensenShannon($left, $right), "\n";
echo Vector::kullbackLeibler($left, $right, 'f32'), "\n";
echo Vector::kullbackLeibler($right, $left, 'f32'), "\n";

// Sample output, Apple Silicon. The first line is the f64 kernel. The f32 lines follow the CPU.
// 0.57643705606461
// 1.1457254886627
// 1.3627319335938
