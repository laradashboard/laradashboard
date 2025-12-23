<?php

declare(strict_types=1);

use App\Services\ExportService;
use Illuminate\Support\Facades\Route;

it('handles route exceptions gracefully and falls back to URL generation', function () {
    // Mock Route facade to simulate route exists but throws exception
    Route::shouldReceive('has')
        ->with('admin.crm.export.download')
        ->andReturn(true);
    
    Route::shouldReceive('route')
        ->with('admin.crm.export.download', ['filename' => 'test.csv'])
        ->andThrow(new Exception('Route parameter missing'));
    
    Route::shouldReceive('has')
        ->with('admin.export.download')
        ->andReturn(false);
    
    $service = new ExportService('Contact');
    
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('getDownloadUrl');
    $method->setAccessible(true);
    
    $url = $method->invoke($service, 'test.csv');
    
    // Should fall back to URL generation
    expect($url)->toBe(url('admin/export/download/test.csv'));
});

it('handles both route checks throwing exceptions', function () {
    Route::shouldReceive('has')
        ->with('admin.crm.export.download')
        ->andReturn(true);
    
    Route::shouldReceive('route')
        ->with('admin.crm.export.download', ['filename' => 'test.csv'])
        ->andThrow(new Exception('First route error'));
    
    Route::shouldReceive('has')
        ->with('admin.export.download')
        ->andReturn(true);
    
    Route::shouldReceive('route')
        ->with('admin.export.download', ['filename' => 'test.csv'])
        ->andThrow(new Exception('Second route error'));
    
    $service = new ExportService('Contact');
    
    $reflection = new ReflectionClass($service);
    $method = $reflection->getMethod('getDownloadUrl');
    $method->setAccessible(true);
    
    $url = $method->invoke($service, 'test.csv');
    
    // Should still fall back to URL generation
    expect($url)->toBe(url('admin/export/download/test.csv'));
});