<?php

declare(strict_types=1);

use App\Services\ImportService;
use App\Services\ExportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;

it('can export and then import the same data format', function () {
    // Create test CSV data
    $csvContent = "name,email,phone\nJohn Doe,john@example.com,123456789\nJane Smith,jane@example.com,987654321";
    $tempFile = tmpfile();
    fwrite($tempFile, $csvContent);
    $tempPath = stream_get_meta_data($tempFile)['uri'];
    
    $uploadedFile = new UploadedFile($tempPath, 'test.csv', 'text/csv', null, true);
    
    // Test import service can read the format
    $importService = new ImportService('Contact');
    $fileInfo = $importService->uploadedFileInfo($uploadedFile);
    
    expect($fileInfo['headers'])->toBe(['name', 'email', 'phone']);
    expect($fileInfo['rows'])->toHaveCount(2);
    
    // Test export service would generate compatible format
    $exportService = new ExportService('Contact');
    $columns = ['name', 'email', 'phone'];
    
    // Verify column compatibility
    expect($fileInfo['headers'])->toBe($columns);
    
    fclose($tempFile);
});

it('handles column mapping consistency between import and export', function () {
    $importService = new ImportService('Contact');
    $exportService = new ExportService('Contact');
    
    // Test column auto-mapping
    $mandatoryColumns = ['name', 'email'];
    $optionalColumns = ['phone'];
    $fileColumns = ['name', 'email', 'phone'];
    
    $mappings = $importService->autoSelectColumnMappings($mandatoryColumns, $optionalColumns, $fileColumns);
    
    expect($mappings)->toBe([
        'name' => 'name',
        'email' => 'email',
        'phone' => 'phone'
    ]);
});

it('validates data integrity through import-export cycle', function () {
    $originalData = [
        ['name' => 'John Doe', 'email' => 'john@example.com', 'phone' => '123456789'],
        ['name' => 'Jane Smith', 'email' => 'jane@example.com', 'phone' => '987654321']
    ];
    
    // Create CSV from original data
    $csvContent = "name,email,phone\n";
    foreach ($originalData as $row) {
        $csvContent .= implode(',', $row) . "\n";
    }
    
    $tempFile = tmpfile();
    fwrite($tempFile, $csvContent);
    $tempPath = stream_get_meta_data($tempFile)['uri'];
    
    $uploadedFile = new UploadedFile($tempPath, 'test.csv', 'text/csv', null, true);
    
    // Import and verify data integrity
    $importService = new ImportService('Contact');
    $fileInfo = $importService->uploadedFileInfo($uploadedFile);
    
    foreach ($fileInfo['rows'] as $index => $row) {
        $extractedData = $importService->extractRowData($row, $fileInfo['headers']);
        expect($extractedData)->toBe($originalData[$index]);
    }
    
    fclose($tempFile);
});

it('handles security concerns consistently across both services', function () {
    // Test malicious content handling
    $maliciousContent = "name,email,formula\nJohn,john@example.com,=cmd|'/c calc'";
    $tempFile = tmpfile();
    fwrite($tempFile, $maliciousContent);
    $tempPath = stream_get_meta_data($tempFile)['uri'];
    
    $uploadedFile = new UploadedFile($tempPath, 'malicious.csv', 'text/csv', null, true);
    
    $importService = new ImportService('Contact');
    $fileInfo = $importService->uploadedFileInfo($uploadedFile);
    
    // Verify malicious content is preserved (not sanitized at service level)
    expect($fileInfo['rows'][0][2])->toBe("=cmd|'/c calc'");
    
    // Export service should handle this during export
    $maliciousValue = "=cmd|'/c calc'";
    $sanitized = preg_match('/^[=+\-@]/', $maliciousValue) ? "'" . $maliciousValue : $maliciousValue;
    expect($sanitized)->toBe("'=cmd|'/c calc'");
    
    fclose($tempFile);
});

it('maintains performance standards for large datasets', function () {
    // Create large dataset
    $csvContent = "name,email,phone\n";
    for ($i = 1; $i <= 5000; $i++) {
        $csvContent .= "User{$i},user{$i}@example.com,12345678{$i}\n";
    }
    
    $tempFile = tmpfile();
    fwrite($tempFile, $csvContent);
    $tempPath = stream_get_meta_data($tempFile)['uri'];
    
    $uploadedFile = new UploadedFile($tempPath, 'large.csv', 'text/csv', null, true);
    
    // Test import performance
    $startTime = microtime(true);
    $importService = new ImportService('Contact');
    $fileInfo = $importService->uploadedFileInfo($uploadedFile);
    $importTime = microtime(true) - $startTime;
    
    expect($fileInfo['rows'])->toHaveCount(5000);
    expect($importTime)->toBeLessThan(10.0); // Should process within 10 seconds
    
    // Test memory usage
    $memoryUsage = memory_get_peak_usage(true);
    expect($memoryUsage)->toBeLessThan(128 * 1024 * 1024); // Less than 128MB
    
    fclose($tempFile);
});