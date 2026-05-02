<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->notPath('Kernel.php')
    ->notPath('bootstrap.php');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PER-CS' => true,
        'declare_strict_types' => true,

        // Импорты
        'no_unused_imports' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'global_namespace_import' => [
            'import_classes' => true,
            'import_functions' => false,
            'import_constants' => false,
        ],

        // Чистота кода
        'no_empty_statement' => true,
        'no_superfluous_phpdoc_tags' => true,   // Убирает @param int $x если тип уже в сигнатуре
        'single_quote' => true,

        // Типизация (важно для DDD)
        'void_return' => true,                   // Явный :void
        'nullable_type_declaration' => true,      // ?Type вместо Type|null

        // Методы тестов в snake case
        'php_unit_method_casing' => ['case' => 'snake_case'],

        // Пустая строка перед return
        'blank_line_before_statement' => [
            'statements' => ['return'],
        ],

        // Сортировка элементов класса
        'ordered_class_elements' => [
            'order' => [
                'use_trait',
                'case',
                'constant_public',
                'constant_protected',
                'constant_private',
                'property_public',
                'property_protected',
                'property_private',
                'construct',
                'destruct',
                'magic',
                'phpunit',
                'method_public_static',
                'method_public',
                'method_protected_static',
                'method_protected',
                'method_private_static',
                'method_private',
            ],
        ],
    ])
    ->setFinder($finder)
    ->setRiskyAllowed(true);
