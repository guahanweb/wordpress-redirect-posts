<?php
// Autoload GW\RedirectPosts namespace
spl_autoload_register(function ($class_name) {
    $parts = explode('\\', strtolower($class_name));

    if (count($parts) > 2 && $parts[0] == 'gw' && $parts[1] == 'redirectposts') {
        $filename = implode('/', array_merge(array(__DIR__, 'lib'), $parts)) . '.php';
        include_once $filename;
    }
});
