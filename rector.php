<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector;
use Rector\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/app/Models',
        __DIR__.'/app/Services',
        __DIR__ . '/app/Concerns',
        __DIR__.'/app/Providers',
        __DIR__.'/app/Http',
        __DIR__.'/app/View',
        __DIR__.'/database',
    ]);

    // Register individual rules that are more stable
    $rectorConfig->rule(AddVoidReturnTypeWhereNoReturnRector::class);
    $rectorConfig->rule(TypedPropertyFromStrictConstructorRector::class);
    $rectorConfig->rule(AddReturnTypeDeclarationRector::class);
    $rectorConfig->rule(AddVoidReturnTypeWhereNoReturnRector::class);

    // Skip certain paths that might cause issues
    $rectorConfig->skip([
        __DIR__.'/app/Http/Middleware',
        __DIR__.'/app/Exceptions',
        __DIR__.'/app/Console',
    ]);
};
