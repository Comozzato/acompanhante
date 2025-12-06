<?php

use App\Gates\Authentic;
use Illuminate\Support\Str;

if (!function_exists('auth_user')) {
    function auth_user()
    {
        return Authentic::Auth();
    }
}

if (! function_exists('generate_unique_filename')) {
    /**
     * Gera um nome de arquivo único para salvar em disco, evitando conflitos.
     */
    function generate_unique_filename(string $prefix, string $originalName, string $extension = 'png'): string
    {
        return $prefix . '_' . Str::slug($originalName) . '_' . now()->timestamp . '_' . Str::random(6) . '.' . $extension;
    }
}
