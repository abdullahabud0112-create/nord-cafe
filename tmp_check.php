<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$names = ['Kentang Goreng Ori','Kentang Bucket','Roti Maryam Ori','Roti Maryam Coklat','Tenderloin Ori'];
foreach ($names as $n) {
    $p = App\Models\Product::where('name', $n)->first();
    echo $n.' | '.($p ? ($p->price.' | '.substr($p->image_url, 0, 90)) : 'NOT FOUND').PHP_EOL;
}
echo 'Palm: '.App\Models\Product::where('name','like','%Palm Sugar%')->pluck('name')->implode(', ').PHP_EOL;
echo 'Total: '.App\Models\Product::count().PHP_EOL;
