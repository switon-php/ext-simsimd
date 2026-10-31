# ext-simsimd

PHP extension. `Switon\Simsimd\Vector` calls [SimSIMD](https://github.com/ashvardanian/SimSIMD) 6.5.16, a SIMD library for distances and dot products on equal-length vectors. Supported platforms are macOS Apple Silicon, Linux glibc (`x86_64` and `arm64`), and Windows x64, PHP 8.2–8.5, NTS and ZTS. Contributor conventions are in [AGENTS.md](AGENTS.md).

## Install

`pie install switon/simsimd`. PIE takes a prebuilt file from the GitHub release when the PHP version matches, and compiles the C sources otherwise. A source build needs the toolchain in the build section below.

## SimSIMD

[SimSIMD](https://github.com/ashvardanian/SimSIMD) is a C99 library of SIMD kernels for vector distances, dot products, and a few matrix and sparse operations. [Ash Vardanian](https://github.com/ashvardanian) wrote it for vector search: score one embedding, binary hash, or sparse id list against another without a scalar loop. [USearch](https://github.com/unum-cloud/usearch) calls those kernels from its index. The same code ships as Python, Rust, C, C++, Swift, JavaScript, and Go bindings. The write-up for this generation is [SimSIMD, faster than SciPy](https://ashvardanian.com/posts/simsimd-faster-scipy). The license is Apache-2.0.

At runtime the library reads the CPU and uses NEON, AVX2, or AVX-512 when that machine has it. A portable C fallback covers the rest. The caller passes two equal-length buffers and a numeric type, and does not choose an instruction set.

This extension compiles tag [v6.5.16](https://github.com/ashvardanian/SimSIMD/releases/tag/v6.5.16), `c/lib.c`, into the module. `Vector` is that call from PHP. The caller still picks which pairs to compare. The extension does not store a collection or run a nearest-neighbour search. Upstream later renamed the project to NumKong and changed the symbols. This tree stays on 6.5.16, so the symbols stay `simsimd_*`.

PHP has no equivalent of these kernels. A `for` loop can compute a dot product, and then every later change has to be checked against the formula again. Half precision, complex products, a sorted intersection, and `aᵀCb` are worse: the scalar version is easy to get subtly wrong. The binding exists so that code calls the library instead.

Where a PHP service actually uses it:

- Embedding score. Cosine or dot product on a vector of a few hundred to a few thousand dimensions, usually `f32`. 1536 is the length in the bench below.
- Half precision. `f16` and `bf16` packed strings are the bytes already stored for the vector. The call reads them in place.
- Binary fingerprints. Hamming and Jaccard on raw bytes, eight bits per byte.
- Sparse ids. How many values two sorted unsigned lists share, with optional weights on that walk.
- Forms that are painful to reimplement: complex products, bilinear `aᵀCb`, Mahalanobis, `wsum`, and `fma`.

What we rely on:

- The kernel runs inside the extension. A PHP list is copied into a contiguous buffer for the call.
- `kind` is `f64` by default. `f32` is the kernel for embedding vectors. `f16` and `bf16` take a PHP list or a native-endian packed string. `i8` accepts only ints in `-128..127`. `u8` accepts only ints in `0..255`.
- `cosine` is the cosine distance, `1` minus the cosine similarity. `dot` is the inner product. `complexDot` returns the real and imaginary parts. `jaccard` is the bit-level Jaccard distance.
- `intersect` counts matches in two sorted unsigned lists. `sparseI16Dot` and `sparseBf16Dot` add `i16` or `bf16` weights to that walk. `bilinear` is `aᵀCb`. `mahalanobis` is the square root of that form on `a - b`.
- `wsum` is `alpha * a + beta * b`. `fma` is `alpha * a * b + beta * c`. Both return the stored vector.
- `hamming` and `jaccard` take raw byte strings, eight bits per byte.
- `flushDenormals()` is not called on startup. It changes the floating-point environment of the calling thread.

```php
use Switon\Simsimd\Vector;

$distance = Vector::cosine($left, $right, 'f32');
```

The method list, defaults, exceptions, and pitfalls are in [stubs/README.md](stubs/README.md). Call-style samples are in [examples/](examples/).

## Compared with PHP

The same cosine in PHP is a `for` loop over the two lists. The same Hamming distance is a loop over the bytes with a 256-entry popcount table. The packed `f16` row times that loop on the decoded values against the extension reading the packed string. The intersection row is a merge of two sorted lists of 4096 ids. `bench/bench.php` checks that the two sides still agree, then times them after a warmup.

One local run, PHP 8.3.30 NTS on Apple Silicon:

| Call | PHP | simsimd |
| --- | --- | --- |
| cosine, 1536 `f32` | 95.34 µs | 5.11 µs |
| cosine, 1536 `f64` | 83.44 µs | 5.13 µs |
| cosine, 4 `f32` | 0.44 µs | 0.12 µs |
| hamming, 256 bytes | 32.73 µs | 0.09 µs |
| cosine, 1536 `f16` packed | 80.94 µs | 0.86 µs |
| intersect, 4096 `u32` | 424.65 µs | 28.82 µs |

That is about 19× on the 1536-element `f32` cosine, about 94× on the packed `f16` cosine, and about 360× on the 256-byte Hamming distance. The 4-element cosine is about 4×. The sorted intersection is about 15×. It is one machine, not a portability claim. A single short vector in a request does not need the extension. The gain shows up when the call sits on a hot path.

`make bench` repeats that run. It is separate from `make test` and is not part of CI.

## Build

The library matches the `php` and `php-config` on `PATH` while it compiles. Those two commands must print the same version. On Windows there is no `php-config`; the build reads the PHP on `PATH`. The result loads only on that PHP minor version, that NTS or ZTS build, and that CPU.

`make test` is the root `Makefile`. It runs `phpize` inside `pie/`, so the root file is not replaced. `pie/config.m4` is the Unix source build. `pie/config.w32` is the Windows source build.

From a clone of this repository:

```bash
make test
```

`make test` builds the library and loads it. The file is `pie/modules/simsimd.so` on macOS and Linux, and `pie/modules/php_simsimd.dll` on Windows. `php -m` lists the module as `simsimd`. The classes stay under `Switon\Simsimd`.

```bash
php -d extension="$PWD/pie/modules/simsimd.so" -m
```

On Windows use `php_simsimd.dll`.

### Homebrew

Apple Silicon. The Command Line Tools supply clang.

```bash
xcode-select --install
brew install php
export PATH="$(brew --prefix php)/bin:$PATH"
php -v
php-config --version
make test
```

### apt

Debian and Ubuntu. `php-dev` follows the distribution's default PHP, which must be 8.2–8.5.

```bash
sudo apt update
sudo apt install build-essential php-cli php-dev
php -v
php-config --version
make test
```

To compile against a specific minor version, install that pair instead, for example `php8.3-cli` and `php8.3-dev`, and put that `php` and `php-config` first on `PATH`.

### yum

RHEL, AlmaLinux, Rocky Linux, and Fedora. On Fedora the same packages install with `dnf`. `php-devel` must be PHP 8.2–8.5.

```bash
sudo yum install gcc make php-cli php-devel
php -v
php-config --version
make test
```

A ZTS library is a second build. Point `PATH` and `PHP_CONFIG` at the ZTS `php` and `php-config`, then run `make test` again. On Windows, put the ZTS `php.exe` first on `PATH` instead.

### Windows

Windows x64. PHP is a [windows.php.net](https://windows.php.net/) build, VS16 for 8.2 and 8.3, VS17 for 8.4 and 8.5. Visual Studio 2022 Build Tools with the C++ workload supplies `cl.exe`. Run the commands from Git Bash so `make` can see `uname`.

```bash
choco install make -y
php -v
make test
```

The extension version is the git tag without the leading `v`. A tag `v1.0.1` builds version `1.0.1`, and `php --ri simsimd` reports that. A commit after that tag keeps the distance, such as `1.0.1-3`, and does not include the commit id. With no tag in the clone, the build uses `1.0.0`. A release asset is `simsimd-1.0.1-php8.3-nts-linux-x64.tar.gz`: that version, the PHP minor, `nts` or `zts`, `linux`, `macos`, or `windows`, `x64` or `arm64`. Unpack it. The only file inside is `simsimd.so` on Linux and macOS, or `php_simsimd.dll` on Windows. Load the file:

```ini
extension=/path/simsimd.so
```

`extension=simsimd` looks for `simsimd.so` on Linux and macOS, and for `php_simsimd.dll` on Windows. On Windows the line is `extension=C:/path/php_simsimd.dll`. The file still has to match the PHP minor version, NTS or ZTS, and the CPU. A Linux build from Ubuntu 24.04 needs that glibc or a newer one. Windows builds are x64.

## CI

[`.github/workflows/ci.yml`](.github/workflows/ci.yml) runs `make test` on every push and pull request: PHP 8.5 NTS on each runner.

A `v*` tag runs [`.github/workflows/release.yml`](.github/workflows/release.yml). That build is the full set: PHP 8.2–8.5, NTS and ZTS, on all four runners. Each archive contains one library, and CI rejects a build that links a second project library. `pie install switon/simsimd` uses the `php_simsimd-*.zip` assets from that release. `make bench` is not part of CI.
