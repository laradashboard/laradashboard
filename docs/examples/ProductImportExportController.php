<?php

namespace App\Http\Controllers\Examples;

use App\Http\Controllers\Controller;

/**
 * Example Controller showing how to use global Import/Export components
 */
class ProductImportExportController extends Controller
{
    /**
     * Show the import form
     */
    public function importForm()
    {
        return view('products.import');
    }

    /**
     * Download a sample CSV file for import
     */
    public function downloadSample()
    {
        $headers = [
            'name',
            'price',
            'sku',
            'description',
            'category_id',
            'stock',
        ];

        $sampleData = [
            ['Product 1', '29.99', 'SKU001', 'Sample product description', '1', '100'],
            ['Product 2', '39.99', 'SKU002', 'Another product description', '2', '50'],
            ['Product 3', '19.99', 'SKU003', 'Third product description', '1', '75'],
        ];

        $filename = 'products-import-sample-' . now()->format('YmdHis') . '.csv';
        $path = storage_path("app/exports/{$filename}");

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $csv = implode(',', $headers) . "\n";
        foreach ($sampleData as $row) {
            $csv .= implode(',', $row) . "\n";
        }

        file_put_contents($path, $csv);

        return response()->download($path)->deleteFileAfterSend(true);
    }

    /**
     * Show the export form
     */
    public function exportForm()
    {
        // Get categories for filter
        $categories = \App\Models\Category::all()->map(function ($category) {
            return [
                'value' => $category->id,
                'label' => $category->name,
            ];
        })->toArray();

        // Add "All" option
        array_unshift($categories, ['value' => '', 'label' => 'All Categories']);

        $filtersItems = [
            'category' => $categories,
            'status' => [
                ['value' => 'active', 'label' => 'Active'],
                ['value' => 'inactive', 'label' => 'Inactive'],
            ],
        ];

        return view('products.export', compact('filtersItems'));
    }
}
