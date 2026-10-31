PHP ?= php
PHPIZE ?= phpize
PHP_CONFIG ?= php-config
PHP_PREFIX ?=

UNAME_S := $(shell uname -s)
WINDOWS := $(findstring MINGW,$(UNAME_S))$(findstring MSYS,$(UNAME_S))$(findstring CYGWIN,$(UNAME_S))
ifneq ($(WINDOWS),)
EXT := pie/modules/php_simsimd.dll
else
EXT := pie/modules/simsimd.so
endif

.PHONY: test bench build clean

build: src/simsimd_version.h
ifneq ($(WINDOWS),)
	cl_bin=$$(command -v cl || command -v cl.exe); \
	test -n "$$cl_bin"; \
	PATH="$$(dirname "$$cl_bin"):$$PATH"; \
	cd pie && cmd //c "phpize && configure --enable-simsimd --with-prefix=$(PHP_PREFIX) && nmake"
	mkdir -p pie/modules
	dll=$$(find pie -name php_simsimd.dll -type f ! -path '*/modules/*' | head -n 1); \
	test -n "$$dll"; \
	cp "$$dll" $(EXT)
else
	cd pie && test -f configure || $(PHPIZE)
	cd pie && ./configure --with-php-config=$(PHP_CONFIG)
	$(MAKE) -C pie
endif

test: build
	$(PHP) -d extension=$(CURDIR)/$(EXT) -d display_errors=1 tests/vector.php
	$(PHP) -d extension=$(CURDIR)/$(EXT) -d display_errors=1 tests/algorithms.php

bench: build
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
