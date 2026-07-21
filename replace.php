<?php
$files = ['app/Http/Controllers/LaporanController.php', 'app/Http/Controllers/JadwalController.php'];
foreach($files as $file) {
    $content = file_get_contents($file);
    $content = str_replace('->download(', '->stream(', $content);
    file_put_contents($file, $content);
    echo 'Updated ' . $file . PHP_EOL;
}
