.DEFAULT_GOAL := help

mkfile_path := $(abspath $(lastword $(MAKEFILE_LIST)))
current_dir := $(dir $(mkfile_path))

help:
	@echo "Use this makefile to execute your tests in correct php version"
	@echo "\tr.php-7.4\t\trun Tests with PHP 7.4"
	@echo "\tr.php-8.0\t\trun Tests with PHP 8.0"
	@echo "\tr.php-8.1\t\trun Tests with PHP 8.1"
	@echo "\tr.php-8.2\t\trun Tests with PHP 8.2"

r.php-7.4:
	docker build -t robo:php-7.4 --target PHP74 --build-arg PHP_VERSION=7.4 docker
	docker run --rm -v $(current_dir):/app -w /app robo:php-7.4 composer install
	docker run --rm -v $(current_dir):/app -w /app robo:php-7.4 composer test

r.php-8.0:
	docker build -t robo:php-8.0 --target PHP8 --build-arg PHP_VERSION=8.0 docker
	docker run --rm -v $(current_dir):/app -w /app robo:php-8.0 composer install
	docker run --rm -v $(current_dir):/app -w /app robo:php-8.0 composer test

r.php-8.1:
	docker build -t robo:php-8.1 --target PHP8 --build-arg PHP_VERSION=8.1 docker
	docker run --rm -v $(current_dir):/app -w /app robo:php-8.1 composer install
	docker run --rm -v $(current_dir):/app -w /app robo:php-8.1 composer test

r.php-8.2:
	docker build -t robo:php-8.2 --target PHP8 --build-arg PHP_VERSION=8.2 docker
	docker run --rm -v $(current_dir):/app -w /app robo:php-8.2 composer install
	docker run --rm -v $(current_dir):/app -w /app robo:php-8.2 composer test