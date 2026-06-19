### Hexlet tests and linter status:
[![Actions Status](https://github.com/Faik-man/php-project-48/actions/workflows/hexlet-check.yml/badge.svg)](https://github.com/Faik-man/php-project-48/actions)
[![Main workflow](https://github.com/Faik-man/php-project-48/actions/workflows/main.yml/badge.svg)](https://github.com/Faik-man/php-project-48/actions/workflows/main.yml)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=Faik-man_php-project-48&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=Faik-man_php-project-48)
[![Bugs](https://sonarcloud.io/api/project_badges/measure?project=Faik-man_php-project-48&metric=bugs)](https://sonarcloud.io/summary/new_code?id=Faik-man_php-project-48)
[![Code Smells](https://sonarcloud.io/api/project_badges/measure?project=Faik-man_php-project-48&metric=code_smells)](https://sonarcloud.io/summary/new_code?id=Faik-man_php-project-48)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=Faik-man_php-project-48&metric=coverage)](https://sonarcloud.io/summary/new_code?id=Faik-man_php-project-48)
[![Duplicated Lines (%)](https://sonarcloud.io/api/project_badges/measure?project=Faik-man_php-project-48&metric=duplicated_lines_density)](https://sonarcloud.io/summary/new_code?id=Faik-man_php-project-48)
[![Lines of Code](https://sonarcloud.io/api/project_badges/measure?project=Faik-man_php-project-48&metric=ncloc)](https://sonarcloud.io/summary/new_code?id=Faik-man_php-project-48)
[![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=Faik-man_php-project-48&metric=reliability_rating)](https://sonarcloud.io/summary/new_code?id=Faik-man_php-project-48)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=Faik-man_php-project-48&metric=security_rating)](https://sonarcloud.io/summary/new_code?id=Faik-man_php-project-48)
[![Technical Debt](https://sonarcloud.io/api/project_badges/measure?project=Faik-man_php-project-48&metric=sqale_index)](https://sonarcloud.io/summary/new_code?id=Faik-man_php-project-48)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=Faik-man_php-project-48&metric=sqale_rating)](https://sonarcloud.io/summary/new_code?id=Faik-man_php-project-48)
[![Vulnerabilities](https://sonarcloud.io/api/project_badges/measure?project=Faik-man_php-project-48&metric=vulnerabilities)](https://sonarcloud.io/summary/new_code?id=Faik-man_php-project-48)

## Проект "Вычислитель отличий (PHP)"

### Краткое описание проекта:
Это итоговый проект по окончанию выполнения модуля №2 из курса PHP-Разработчик. Данный проект определяет различия между двумя файлами в формате json/yml путем парсинга файлов, выстраивания промежуточного представления и вывода в определенном формате.

### Минимальные требования:
* php 8.3
* composer 2.9 (должен быть установлен глобально)
* GNU make

### Инструкция по установке:
- Выполняем ```make install``` для установки зависимостей

### Инструкция по запуску:
- Для вычисления отличий вызываем исполняемый файл bin/gendiff и в качестве входных значений передаем путь до файлов в формате json/yml.
```./bin/gendiff filepath1 filepath2```
- По умолчанию формат вывода отличий - `stylish`. Для явного указания формата нужно добавить параметр `--format`. Варианты форматов: `stylish`, `plain` и `json`;
```./bin/gendiff --format=plan filepath1 filepath2```

### Аскинемы:
- [Шаг 4](https://asciinema.org/a/6glYHIaVRWi93IjV)
- [Шаг 6](https://asciinema.org/a/uqMMC2geQiprmr22)
- [Шаг 7](https://asciinema.org/a/yRRKO8OGYQu9NHQH)
- [Шаг 8](https://asciinema.org/a/YCmnTEnUlA9BG2Qu)
- [Шаг 9](https://asciinema.org/a/ZxqP7sl1iZqrZPPh)
