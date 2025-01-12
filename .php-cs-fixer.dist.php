<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'no_unused_imports' => true,
        'single_quote' => true,
        'no_extra_blank_lines' => true,
        'ordered_imports' => true,
        'phpdoc_to_comment' => true,
        'no_superfluous_phpdoc_tags' => true,
        'escape_implicit_backslashes' => true,
        'visibility_required' => ['elements' => ['property', 'method']],
        'concat_space' => ['spacing' => 'one'],
        'blank_line_before_statement' => ['statements' => ['return', 'throw', 'try']],
        'braces' => true,
        'binary_operator_spaces' => true,
        'array_syntax' => ['syntax' => 'short'],
        'trailing_comma_in_multiline' => [
            'elements' => ['arrays', 'arguments', 'parameters'],
        ],
        'class_attributes_separation' => [
            'elements' => [
                'const' => 'one', // Пустые строки между константами
                'property' => 'one', // Пустые строки между свойствами
                'method' => 'one', // Пустые строки между методами
            ],
        ],
        'single_line_after_imports' => true,
    ])
    ->setFinder($finder)
;
