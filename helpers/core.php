<?php

if (!function_exists('core_path')) {
    function core_path(): string
    {
        return base_path("core");
    }
}

if (!function_exists('core_base_path')) {
    function core_base_path(string $path = ""): string
    {
        return base_path("core/base/" . $path);
    }
}

if (!function_exists('core_generate_path')) {
    function core_generate_path(string $path = ""): string
    {
        return base_path("core/generators/" . $path);
    }
}

if (!function_exists('core_package_path')) {
    function core_package_path(string $path = ""): string
    {
        return base_path("core/packages/" . $path);
    }
}
