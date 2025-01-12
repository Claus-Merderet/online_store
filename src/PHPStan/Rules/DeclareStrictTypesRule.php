<?php

declare(strict_types=1);

namespace App\PHPStan\Rules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\ShouldNotHappenException;

/**
 * @implements Rule<FileNode>
 */
class DeclareStrictTypesRule implements Rule
{
    private const EXCLUDED_FILES = [
        'src/Kernel.php',
        'tests/bootstrap.php',
    ];
    public function getNodeType(): string
    {
        return FileNode::class;
    }

    /**
     * @param  FileNode $node
     * @throws ShouldNotHappenException
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $currentFile = $scope->getFile();

        // Исключение определённых файлов из проверки
        foreach (self::EXCLUDED_FILES as $excludedFile) {
            if (str_contains($currentFile, $excludedFile)) {
                return [];
            }
        }
        $nodes = $node->getNodes();

        if (count($nodes) === 0) {
            return [];
        }

        $firstNode = array_shift($nodes);
        if ($firstNode instanceof Node\Stmt\Declare_) {
            foreach ($firstNode->declares as $declare) {
                if (
                    'strict_types' === $declare->key->toLowerString()
                    && $declare->value instanceof Node\Scalar\LNumber
                    && 1 === $declare->value->value
                ) {
                    return [];
                }
            }
        }

        return [
            RuleErrorBuilder::message('PHP files should declare strict types.')
                ->identifier('worksome.declareStrictTypes')
                ->build(),
        ];
    }
}
