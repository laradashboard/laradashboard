<?php

require_once __DIR__ . '/vendor/autoload.php';

use Modules\Site\Templates\LandingPageTemplate;

try {
    $template = new LandingPageTemplate();

    echo "Template Name: " . $template->getName() . "\n";
    echo "Content Array Structure:\n";
    var_dump($template->content());

    echo "\n=== Rendered HTML ===\n";
    $html = $template->render();
    echo $html;

    echo "\n\n=== Test Completed Successfully ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
