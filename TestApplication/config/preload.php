<?php

declare(strict_types=1);

$preloadFilename = dirname(__DIR__) . '/var/cache/prod/App_KernelProdContainer.preload.php';
if (file_exists($preloadFilename)) {
    require $preloadFilename;
}
