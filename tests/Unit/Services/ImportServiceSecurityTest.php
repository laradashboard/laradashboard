<?php

declare(strict_types=1);

use App\Services\ImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

it('hashes password fields during import to prevent plaintext storage', function () {
    $csvContent = "name,email,password\nJohn Doe,john@example.com,plaintext123\nJane Smith,jane@example.com,secret456";
    $tempFile = tmpfile();
    fwrite($tempFile, $csvContent);
    $tempPath = stream_get_meta_data($tempFile)['uri'];
    
    $uploadedFile = new UploadedFile($tempPath, 'users.csv', 'text/csv', null, true);
    
    $importService = new ImportService('User');
    
    // Use reflection to test the protected hashSensitiveFields method
    $reflection = new ReflectionClass($importService);
    $method = $reflection->getMethod('hashSensitiveFields');
    $method->setAccessible(true);
    
    $testData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'plaintext123'
    ];
    
    $hashedData = $method->invoke($importService, $testData);
    
    // Verify password was hashed
    expect($hashedData['password'])->not->toBe('plaintext123');
    expect(Hash::check('plaintext123', $hashedData['password']))->toBeTrue();
    
    // Verify other fields remain unchanged
    expect($hashedData['name'])->toBe('John Doe');
    expect($hashedData['email'])->toBe('john@example.com');
    
    fclose($tempFile);
});

it('handles null password fields without errors', function () {
    $importService = new ImportService('User');
    
    $reflection = new ReflectionClass($importService);
    $method = $reflection->getMethod('hashSensitiveFields');
    $method->setAccessible(true);
    
    $testData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => null
    ];
    
    $result = $method->invoke($importService, $testData);
    
    expect($result['password'])->toBeNull();
});

it('hashes password_confirmation field as well', function () {
    $importService = new ImportService('User');
    
    $reflection = new ReflectionClass($importService);
    $method = $reflection->getMethod('hashSensitiveFields');
    $method->setAccessible(true);
    
    $testData = [
        'password' => 'secret123',
        'password_confirmation' => 'secret123'
    ];
    
    $result = $method->invoke($importService, $testData);
    
    expect(Hash::check('secret123', $result['password']))->toBeTrue();
    expect(Hash::check('secret123', $result['password_confirmation']))->toBeTrue();
});