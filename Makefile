test:
	vendor/bin/phpunit --testdox

analyse:
	vendor/bin/phpstan analyse
	vendor/bin/psalm --no-cache

bench:
	vendor/bin/phpbench run benchmarks --report=default
