<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CreatePlaceholderImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-placeholder-images';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create placeholder images for categories and tags';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Creating placeholder images...');

        // Create directories
        Storage::disk('public')->makeDirectory('categories');
        Storage::disk('public')->makeDirectory('tags');

        // Define placeholder images.
        $placeholders = [
            // Categories
            'categories/uncategorized.jpg' => [
                'text' => 'Uncategorized',
                'color' => '#3498db', // Blue
            ],

            // Posts.
            'posts/hellow-world.jpg' => [
                'text' => 'Hello World',
                'color' => '#e74c3c', // Red
            ],
        ];

        // Create images
        foreach ($placeholders as $path => $options) {
            $this->createPlaceholderImage($path, $options['text'], $options['color']);
            $this->info("Created: {$path}");
        }

        $this->info('Placeholder images created successfully!');

        return Command::SUCCESS;
    }

    /**
     * Create a placeholder image with text
     */
    protected function createPlaceholderImage(string $path, string $text, string $hexColor): void
    {
        // Skip if GD library is not available
        if (! extension_loaded('gd')) {
            $this->warn('GD library not available. Skipping image creation.');

            return;
        }

        // Convert hex color to RGB
        [$r, $g, $b] = sscanf($hexColor, '#%02x%02x%02x');

        // Create a 600x400 image
        $image = imagecreatetruecolor(600, 400);

        // Fill background
        $bgColor = imagecolorallocate($image, $r, $g, $b);
        imagefill($image, 0, 0, $bgColor);

        // Add text
        $textColor = imagecolorallocate($image, 255, 255, 255);

        // Use a larger font size
        $fontSize = 5;
        $textWidth = imagefontwidth($fontSize) * strlen($text);
        $textHeight = imagefontheight($fontSize);
        $x = (600 - $textWidth) / 2;
        $y = (400 - $textHeight) / 2;

        // Draw text
        imagestring($image, $fontSize, $x, $y, $text, $textColor);

        // Save the image to storage
        $tempFile = tempnam(sys_get_temp_dir(), 'img');
        imagejpeg($image, $tempFile, 90);
        imagedestroy($image);

        // Move to storage
        Storage::disk('public')->put($path, file_get_contents($tempFile));
        unlink($tempFile);
    }
}
