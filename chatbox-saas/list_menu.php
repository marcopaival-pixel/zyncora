<?php

use Illuminate\Contracts\Console\Kernel;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

$files = array_merge(
    glob(__DIR__.'/app/Filament/Resources/*.php'),
    glob(__DIR__.'/app/Filament/Pages/*.php')
);

$items = [];
foreach ($files as $file) {
    $content = file_get_contents($file);
    if (preg_match('/class\s+(\w+)\s+extends/', $content, $matches)) {
        $className = $matches[1];

        $group = '';
        if (preg_match('/\$navigationGroup\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $m)) {
            $group = $m[1];
        }

        $label = '';
        if (preg_match('/\$navigationLabel\s*=\s*[\'"]([^\'"]+)[\'"]/', $content, $m)) {
            $label = $m[1];
        } elseif (preg_match('/public\s+static\s+function\s+getNavigationLabel\(\).*?return\s+[\'"]([^\'"]+)[\'"]/s', $content, $m)) {
            $label = $m[1];
        }

        $sort = 0;
        if (preg_match('/\$navigationSort\s*=\s*(\d+)/', $content, $m)) {
            $sort = (int) $m[1];
        }

        $items[] = [
            'file' => basename($file),
            'class' => $className,
            'group' => $group,
            'label' => $label,
            'sort' => $sort,
        ];
    }
}

usort($items, function ($a, $b) {
    if ($a['group'] != $b['group']) {
        return strcmp($a['group'], $b['group']);
    }

    return $a['sort'] <=> $b['sort'];
});

foreach ($items as $item) {
    echo str_pad($item['group'] ?: 'No Group', 30).' | '.str_pad($item['label'] ?: $item['class'], 30)." | Sort: {$item['sort']} | File: {$item['file']}\n";
}
