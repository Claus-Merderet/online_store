<?php

declare(strict_types=1);

namespace App\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;

class DeclareStrictTypesRule implements Rule
{
    private bool $hasStrictTypesDeclared = false;
    private array $processedFiles = [];

    public function getNodeType(): string
    {
        return Node::class; // Проверяем все узлы
    }

    public function processNode(Node $node, Scope $scope): array
    {
        // Проверяем, что узел находится в пределах первых 3 строк
        if ($node->getLine() > 3) {
            return []; // Пропускаем узлы после третьей строки
        }

        // Если файл уже обработан, не генерируем ошибку снова
        $filePath = $node->getAttribute('file') ?? '';
        if (in_array($filePath, $this->processedFiles)) {
            return [];
        }

        // Проверяем, является ли узел корневым узлом (например, началом файла)
        if ($node instanceof Node\Stmt\Declare_) {
            foreach ($node->declares as $declare) {
                if ($declare->key->toString() === 'strict_types') {
                    $this->hasStrictTypesDeclared = true;
                    $this->processedFiles[] = $filePath; // Добавляем файл в список обработанных
                    return []; // Если declare(strict_types=1) найдено, пропускаем
                }
            }
        }

        // Если strict_types не найден в первых трех строках, возвращаем ошибку
        if (!$this->hasStrictTypesDeclared && $node->getLine() <= 3) {
            $this->processedFiles[] = $filePath; // Добавляем файл в список обработанных
            return [
                RuleErrorBuilder::message('Missing "declare(strict_types=1);" at the beginning of the file.')->build(),
            ];
        }

        return [];
    }
}
