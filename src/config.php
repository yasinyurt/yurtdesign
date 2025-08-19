<?php

// Hata raporlamayı aç (geliştirme aşamasında iyidir)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_URL', 'http://localhost/yurtdesign/'); // YAYINLAMADAN ÖNCE DEĞİŞTİR

// Projenin sunucudaki kök dizin yolu.
define('ROOT_PATH', dirname(__DIR__)); // __DIR__ bu dosyanın (config.php) olduğu klasördür (src). dirname(__DIR__) ise onun bir üst klasörü yani proje-klasorun'dur.