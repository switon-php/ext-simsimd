# AGENTS.md

`README.md` has the build steps. `stubs/README.md` has the method list. `examples/` shows the call style. Review that style before adding a public method.

## Language

PHPDoc, both READMEs, `phpinfo()` strings, and commit messages are English.

## Source

The distance code is SimSIMD 6.5.16 in `third_party/simsimd/`. Compile `c/lib.c`. Do not take a newer tag, and do not rename the symbols to NumKong.

## API

`Switon\Simsimd\Vector` methods are static. A PHP list is copied into a contiguous buffer. `f16` and `bf16` packed strings are passed through. The numeric result is the kernel's `f64`. `complexDot`, `complexConjugateDot`, and `complexBilinear` return `[real, imag]`. `wsum` and `fma` return the stored vector. `sparseI16Dot` and `sparseBf16Dot` return `[count, product]`.

## Bench

`make bench` times `Vector` against a PHP loop on the same inputs. It is not part of `make test` or CI. The README table is one machine, replaced when this command is run again.

## Tests

`tests/vector.php` and `tests/algorithms.php` are the contract for a library sync. They reflect every class and every parameter, then check each `Vector` method against an independent formula at lengths that cross the SIMD body and the tail. A kernel that changes formula fails even when the PHP signatures stay the same. `flushDenormals()` stays last in `vector.php`: it changes the calling thread's floating-point environment.

## Distribution

Packagist and PIE. Copy `composer.json` and the asset names in `.github/actions/test/action.yml`. The source build is `pie/config.m4` and `pie/config.w32`. `make clean` runs `phpize --clean` inside `pie/` only. Do not add PECL, Homebrew, or distro packages.
