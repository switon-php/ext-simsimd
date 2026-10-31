<?php

declare(strict_types=1);

use Switon\Simsimd\Vector;

foreach (Vector::capabilities() as $name => $enabled) {
    echo $name, ' ', $enabled ? 'yes' : 'no', "\n";
}

// Optional, and not called on startup. It changes this thread's floating-point
// environment for the rest of the thread, including later PHP float math.
echo Vector::flushDenormals() ? "flush\n" : "flush failed\n";

// Sample output, Apple Silicon. Each yes/no follows the CPU. The last line is flush.
// neon yes
// neonF16 yes
// neonBf16 yes
// neonI8 yes
// sve no
// sveF16 no
// sveBf16 no
// sveI8 no
// haswell no
// skylake no
// ice no
// genoa no
// sapphire no
// turin no
// sierra no
// dynamicDispatch yes
// flush
