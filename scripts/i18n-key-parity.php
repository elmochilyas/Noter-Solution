<?php

$langDir = __DIR__.'/../resources/lang';
$locales = ['ar', 'fr'];
$exitCode = 0;

$files = glob($langDir.'/ar/*.php');

foreach ($files as $arFile) {
    $basename = basename($arFile);
    $frFile = $langDir.'/fr/'.$basename;

    if (! file_exists($frFile)) {
        echo "MISSING: fr/{$basename}\n";
        $exitCode = 1;

        continue;
    }

    $arKeys = array_keys(require $arFile);
    $frKeys = array_keys(require $frFile);

    $missingInFr = array_diff($arKeys, $frKeys);
    $missingInAr = array_diff($frKeys, $arKeys);

    foreach ($missingInFr as $key) {
        echo "MISSING: fr/{$basename} -> '{$key}'\n";
        $exitCode = 1;
    }

    foreach ($missingInAr as $key) {
        echo "MISSING: ar/{$basename} -> '{$key}'\n";
        $exitCode = 1;
    }
}

exit($exitCode);
