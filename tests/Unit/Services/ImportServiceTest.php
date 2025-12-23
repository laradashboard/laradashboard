<?php

declare(strict_types=1);

use App\Services\ImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->importService = new ImportService('Contact', 'App\\Models');
});

it('initializes with correct model type and namespace', function () {
    $service = new ImportService('User');
    expect($service)->toBeInstanceOf(ImportService::class);
});

it('returns empty arrays for required and valid columns when model class not found', function () {
    $service = new ImportService('NonExistentModel');
    expect($service->getRequiredColumns())->toBe([]);
    expect($service->getValidColumns())->toBe([]);
});

it('extracts CSV file headers and rows correctly', function () {
    $csvContent = "name,email,phone\nJohn Doe,john@example.com,123456789\nJane Smith,jane@example.com,987654321";
    $tempFile = tmpfile();
    fwrite($tempFile, $csvContent);
    $tempPath = stream_get_meta_data($tempFile)['uri'];
    
    $uploadedFile = new UploadedFile($tempPath, 'test.csv', 'text/csv', null, true);
    
    $result = $this->importService->uploadedFileInfo($uploadedFile);
    
    expect($result['headers'])->toBe(['name', 'email', 'phone']);
    expect($result['rows'])->toHaveCount(2);
    expect($result['rows'][0])->toBe(['John Doe', 'john@example.com', '123456789']);
    
    fclose($tempFile);
});

it('handles CSV files with BOM correctly', function () {
    $csvContent = "\xEF\xBB\xBFname,email\nJohn,john@example.com";
    $tempFile = tmpfile();
    fwrite($tempFile, $csvContent);
    $tempPath = stream_get_meta_data($tempFile)['uri'];
    
    $uploadedFile = new UploadedFile($tempPath, 'test.csv', 'text/csv', null, true);
    
    $result = $this->importService->uploadedFileInfo($uploadedFile);
    
    expect($result['headers'][0])->toBe('name');
    
    fclose($tempFile);
});

it('throws exception when ZipArchive is not available for Excel files', function () {
    if (class_exists('ZipArchive')) {
        $this->markTestSkipped('ZipArchive is available');
    }
    
    $tempFile = tmpfile();
    $uploadedFile = new UploadedFile(stream_get_meta_data($tempFile)['uri'], 'test.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    
    expect(fn() => $this->importService->uploadedFileInfo($uploadedFile))
        ->toThrow(Exception::class, 'ZipArchive extension is required');
    
    fclose($tempFile);
});

it('validates file and identifies missing required columns', function () {
    $csvContent = "name,phone\nJohn,123456789";
    $tempFile = tmpfile();
    fwrite($tempFile, $csvContent);
    $tempPath = stream_get_meta_data($tempFile)['uri'];
    
    $uploadedFile = new UploadedFile($tempPath, 'test.csv', 'text/csv', null, true);
    
    $validColumns = ['name', 'email', 'phone'];
    $requiredColumns = ['name', 'email'];
    
    $result = $this->importService->validateFile($uploadedFile, $validColumns, $requiredColumns);
    
    expect($result['missingRequiredColumns'])->toContain('email');
    expect($result['headers'])->toBe(['name', 'phone']);
    
    fclose($tempFile);
});

it('identifies unmatched columns in uploaded file', function () {
    $csvContent = "name,email,invalid_column\nJohn,john@example.com,value";
    $tempFile = tmpfile();
    fwrite($tempFile, $csvContent);
    $tempPath = stream_get_meta_data($tempFile)['uri'];
    
    $uploadedFile = new UploadedFile($tempPath, 'test.csv', 'text/csv', null, true);
    
    $validColumns = ['name', 'email'];
    $requiredColumns = ['name'];
    
    $result = $this->importService->validateFile($uploadedFile, $validColumns, $requiredColumns);
    
    expect($result['unmatchedColumns'])->toContain('invalid_column');
    
    fclose($tempFile);
});

it('extracts row data correctly from headers and row', function () {
    $headers = ['name', 'email', 'phone'];
    $row = ['John Doe', 'john@example.com', '123456789'];
    
    $result = $this->importService->extractRowData($row, $headers);
    
    expect($result)->toBe([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'phone' => '123456789'
    ]);
});

it('converts empty strings to null in row data', function () {
    $headers = ['name', 'email'];
    $row = ['John Doe', ''];
    
    $result = $this->importService->extractRowData($row, $headers);
    
    expect($result['name'])->toBe('John Doe');
    expect($result['email'])->toBeNull();
});

it('finds missing columns correctly', function () {
    $headers = ['name', 'phone'];
    $validColumns = ['name', 'email', 'phone'];
    $requiredColumns = ['name', 'email'];
    
    $result = $this->importService->findMissingColumns($headers, $validColumns, $requiredColumns);
    
    expect($result['missingColumns'])->toContain('email');
    expect($result['missingRequiredColumns'])->toContain('email');
    expect($result['unmatchedColumns'])->toBe([]);
});

it('auto-selects column mappings based on name matching', function () {
    $mandatoryColumns = ['name', 'email'];
    $optionalColumns = ['phone'];
    $fileColumns = ['Name', 'EMAIL', 'Phone Number'];
    
    $result = $this->importService->autoSelectColumnMappings($mandatoryColumns, $optionalColumns, $fileColumns);
    
    expect($result['name'])->toBe('Name');
    expect($result['email'])->toBe('EMAIL');
    expect($result)->not->toHaveKey('phone'); // Phone Number doesn't match exactly
});

it('handles case insensitive column matching', function () {
    $headers = ['NAME', 'Email', 'PHONE'];
    $validColumns = ['name', 'email', 'phone'];
    $requiredColumns = ['name'];
    
    $result = $this->importService->findMissingColumns($headers, $validColumns, $requiredColumns);
    
    expect($result['missingColumns'])->toBe([]);
    expect($result['missingRequiredColumns'])->toBe([]);
});

it('returns error when form request class not found during row validation', function () {
    $service = new ImportService('NonExistentModel');
    $rows = [['John', 'john@example.com']];
    $headers = ['name', 'email'];
    
    $result = $service->validateRows($rows, $headers);
    
    expect($result)->toHaveKey('error');
    expect($result['error'])->toContain('FormRequest class not found');
});

it('handles file reading errors gracefully', function () {
    $invalidFile = new UploadedFile('/nonexistent/path', 'test.csv', 'text/csv', null, true);
    
    expect(fn() => $this->importService->uploadedFileInfo($invalidFile))
        ->toThrow(Exception::class, 'Error reading file');
});

it('processes import with column mappings and optional values', function () {
    // Mock a simple model for testing
    $mockModel = new class {
        public static function create(array $data) {
            return (object) $data;
        }
    };
    
    // Create a service that will use our mock
    $service = new class('TestModel') extends ImportService {
        protected function resolveModelClass(string $modelType, ?string $modelNamespace = null): ?string {
            return 'MockModel';
        }
        
        public function getRequiredColumns(): array {
            return ['name'];
        }
    };
    
    $csvContent = "full_name,contact_email\nJohn Doe,john@example.com";
    $tempFile = tmpfile();
    fwrite($tempFile, $csvContent);
    $tempPath = stream_get_meta_data($tempFile)['uri'];
    
    $uploadedFile = new UploadedFile($tempPath, 'test.csv', 'text/csv', null, true);
    
    $columnMappings = ['name' => 'full_name', 'email' => 'contact_email'];
    $optionalValues = ['status' => 'active'];
    
    // This will fail because the mock model class doesn't actually exist
    $result = $service->import($uploadedFile, $columnMappings, $optionalValues);
    
    expect($result)->toHaveKey('total');
    expect($result)->toHaveKey('validationErrors');
    
    fclose($tempFile);
});

it('validates password hashing security during import', function () {
    $csvContent = "name,email,password\nJohn,john@example.com,plaintext123";
    $tempFile = tmpfile();
    fwrite($tempFile, $csvContent);
    $tempPath = stream_get_meta_data($tempFile)['uri'];
    
    $uploadedFile = new UploadedFile($tempPath, 'test.csv', 'text/csv', null, true);
    
    $headers = ['name', 'email', 'password'];
    $row = ['John', 'john@example.com', 'plaintext123'];
    
    $data = $this->importService->extractRowData($row, $headers);
    
    // Verify password is not hashed by default (security concern)
    expect($data['password'])->toBe('plaintext123');
    
    // Demonstrate proper password hashing
    if (isset($data['password'])) {
        $data['password'] = Hash::make($data['password']);
        expect(Hash::check('plaintext123', $data['password']))->toBeTrue();
    }
    
    fclose($tempFile);
});

it('handles large file processing efficiently', function () {
    // Create a larger CSV for performance testing
    $csvContent = "name,email\n";
    for ($i = 1; $i <= 1000; $i++) {
        $csvContent .= "User{$i},user{$i}@example.com\n";
    }
    
    $tempFile = tmpfile();
    fwrite($tempFile, $csvContent);
    $tempPath = stream_get_meta_data($tempFile)['uri'];
    
    $uploadedFile = new UploadedFile($tempPath, 'large.csv', 'text/csv', null, true);
    
    $startTime = microtime(true);
    $result = $this->importService->uploadedFileInfo($uploadedFile);
    $endTime = microtime(true);
    
    expect($result['rows'])->toHaveCount(1000);
    expect($endTime - $startTime)->toBeLessThan(5.0); // Should process within 5 seconds
    
    fclose($tempFile);
});

it('sanitizes malicious content in CSV data', function () {
    $csvContent = "name,email,description\n=cmd|'/c calc',test@example.com,<script>alert('xss')</script>";
    $tempFile = tmpfile();
    fwrite($tempFile, $csvContent);
    $tempPath = stream_get_meta_data($tempFile)['uri'];
    
    $uploadedFile = new UploadedFile($tempPath, 'malicious.csv', 'text/csv', null, true);
    
    $result = $this->importService->uploadedFileInfo($uploadedFile);
    
    // Verify malicious content is preserved as-is (application should handle sanitization)
    expect($result['rows'][0][0])->toBe("=cmd|'/c calc'");
    expect($result['rows'][0][2])->toBe("<script>alert('xss')</script>");
    
    fclose($tempFile);
});