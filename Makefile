.PHONY: install qa validate audit cs stan test runtime build typecheck npm-audit start stop restart

install:
	ddev composer install

qa:
	ddev composer qa

validate:
	ddev composer validate --strict --no-check-publish

audit:
	ddev composer audit --locked --no-interaction

cs:
	ddev composer cs

stan:
	ddev composer static-analysis

test:
	ddev composer test

runtime:
	ddev composer qa:runtime

build:
	ddev npm --prefix packages/sympress-demo run build

typecheck:
	ddev npm --prefix packages/sympress-demo run typecheck

npm-audit:
	ddev npm --prefix packages/sympress-demo run audit

start:
	ddev start

stop:
	ddev stop

restart:
	ddev restart
