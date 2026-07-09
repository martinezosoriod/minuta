<?php

/**
 * Autoloader PSR-4 para Minuta Electrónica
 */

spl_autoload_register(function ($class) {
    // Prefijo del namespace
    $prefix = 'App\\';
    
    // Directorio base
    $baseDir = __DIR__ . '/app/';
    
    // Verificar si la clase usa el namespace prefix
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    
    // Obtectar parte relativa de la clase
    $relativeClass = substr($class, $len);
    
    // Reemplazar separadores de namespace por separadores de directorio
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    // Si el archivo existe, incluirlo
    if (file_exists($file)) {
        require $file;
    }
});
