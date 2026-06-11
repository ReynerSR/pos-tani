<?php

$dir = new RecursiveDirectoryIterator('c:\xampp\htdocs\pos-tani\pos-tani\resources\views');
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.blade\.php$/i', RecursiveRegexIterator::GET_MATCH);

foreach ($files as $file) {
    $path = $file[0];
    $content = file_get_contents($path);
    $original = $content;

    // Fix per_page options
    $content = preg_replace('/<option value="20"(.*?)>\s*20(\s*row|\s*baris)?\s*<\/option>/i', '<option value="20"$1>20 Baris</option>', $content);
    $content = preg_replace('/<option value="50"(.*?)>\s*50(\s*row|\s*baris)?\s*<\/option>/i', '<option value="50"$1>50 Baris</option>', $content);
    $content = preg_replace('/<option value="100"(.*?)>\s*100(\s*row|\s*baris)?\s*<\/option>/i', '<option value="100"$1>100 Baris</option>', $content);
    
    // Fix "Row" label
    $content = preg_replace('/<label class="form-label mb-1 small">Row<\/label>/i', '', $content);
    
    if ($content !== $original) {
        file_put_contents($path, $content);
        echo "Updated: $path\n";
    }
}
