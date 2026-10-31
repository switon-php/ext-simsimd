# Examples

Call-style samples for `Switon\Simsimd`. Read them next to `stubs/`. They are not tests.

Each file is one everyday use: embedding cosine, the other dense distances, half precision, complex products, sorted intersection, the curved metrics, bit strings, probability divergences, the CPU flags, and the two extension exceptions. The sample output is in the file, under the call. Integer lines are exact. Float lines are one Apple Silicon run. `capabilities.php` prints `yes` or `no` for the CPU it is running on.

```bash
php -d extension="$PWD/pie/modules/simsimd.so" examples/cosine.php
```

On Windows the file is `pie/modules/php_simsimd.dll`.

`cosine.php`:

```text
0.012701153185568
```

`distances.php`:

```text
32
27
5.1961524227066
-24
```

`half.php`:

```text
32
32
```

`complex.php`:

```text
-18
68
70
-8
```

`intersect.php`:

```text
2
1
```

`elementwise.php`:

```text
4 6.5 9
11 18 27
32
```

`sparse.php`:

```text
2
53
```

`matrix.php`:

```text
11
4.9970412254333
```

`bits.php`:

```text
8
0.66666666666667
```

`probability.php`:

```text
0.57643705606461
1.1457254886627
1.3627319335938
```

`capabilities.php` on Apple Silicon:

```text
neon yes
neonF16 yes
neonBf16 yes
neonI8 yes
sve no
sveF16 no
sveBf16 no
sveI8 no
haswell no
skylake no
ice no
genoa no
sapphire no
turin no
sierra no
dynamicDispatch yes
flush
```

`errors.php`:

```text
vectors must have the same length, 2 and 1
vector must be a list
```
