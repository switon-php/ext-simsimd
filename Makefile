PHP ?= php
PHPIZE ?= phpize
PHP_CONFIG ?= php-config

UNAME_S := $(shell uname -s)
ifneq ($(findstring MINGW,$(UNAME_S))$(findstring MSYS,$(UNAME_S))$(findstring CYGWIN,$(UNAME_S)),)
EXT := pie/modules/php_simsimd.dll
else
EXT := pie/modules/simsimd.so
endif

.PHONY: test bench clean

test: src/simsimd_version.h
	cd pie && test -f configure || $(PHPIZE)
	cd pie && ./configure --with-php-config=$(PHP_CONFIG)
	$(MAKE) -C pie
	$(PHP) -d extension=$(CURDIR)/$(EXT) -d display_errors=1 tests/vector.php
	$(PHP) -d extension=$(CURDIR)/$(EXT) -d display_errors=1 tests/algorithms.php

bench: src/simsimd_version.h
	cd pie && test -f configure || $(PHPIZE)
	cd pie && ./configure --with-php-config=$(PHP_CONFIG)
	$(MAKE) -C pie
	$(PHP) -d extension=$(CURDIR)/$(EXT) -d display_errors=1 bench/bench.php

src/simsimd_version.h: scripts/ext-version.sh
	printf '#define PHP_SIMSIMD_VERSION "%s"\n' "$$(sh scripts/ext-version.sh)" > $@

clean:
	rm -f src/simsimd_version.h
	rm -f src/*.lo src/*.loT src/*.dep
	rm -rf src/.libs
	rm -f third_party/simsimd/c/*.lo third_party/simsimd/c/*.loT third_party/simsimd/c/*.dep
	rm -rf third_party/simsimd/c/.libs
	cd pie && if test -f configure; then $(PHPIZE) --clean; fi
