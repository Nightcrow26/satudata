<?php

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

if (! function_exists('resolve_media_url')) {
    /**
     * Resolve an image/media URL that may be an absolute URL or a storage key.
     * - Normalizes malformed schemes (https:/, http:/) and protocol-relative URLs (//)
     * - Upgrades known http domains to https (to avoid mixed content)
     * - Generates S3 temporary URLs for keys when requested
     * - Falls back to Storage::url or a given asset
     */
    function resolve_media_url(string|null $value, array $options = []): string
    {
        $fallback = $options['fallback'] ?? asset('kesehatan.png');
        if (empty($value)) {
            return $fallback;
        }

        $value = trim((string) $value);

        // Base64 data URI
        if (Str::startsWith($value, 'data:image/')) {
            return $value;
        }

        // Fix malformed schemes
        if (Str::startsWith($value, 'https:/') && !Str::startsWith($value, 'https://')) {
            $value = str_replace('https:/', 'https://', $value);
        }
        if (Str::startsWith($value, 'http:/') && !Str::startsWith($value, 'http://')) {
            $value = str_replace('http:/', 'http://', $value);
        }
        // Protocol-relative -> https
        if (Str::startsWith($value, '//')) {
            $value = 'https:' . $value;
        }

        // Upgrade known domains to https
        $upgradeDomains = $options['upgradeDomains'] ?? ['data.hsu.go.id'];
        foreach ((array) $upgradeDomains as $domain) {
            if (Str::startsWith($value, 'http://' . $domain)) {
                $value = str_replace('http://', 'https://', $value);
                break;
            }
        }

        // If it's now a full URL, return it
        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        // Otherwise, treat as storage path
        $disk = $options['disk'] ?? 's3';
        $temporary = $options['temporary'] ?? true;
        $minutes = $options['minutes'] ?? 15;

        try {
            if ($temporary && method_exists(Storage::disk($disk), 'temporaryUrl')) {
                return Storage::disk($disk)->temporaryUrl($value, now()->addMinutes($minutes));
            }
        } catch (\Throwable $e) {
            // fallthrough
        }

        try {
            return Storage::disk($disk)->url($value);
        } catch (\Throwable $e) {
            try {
                return Storage::url($value);
            } catch (\Throwable $e2) {
                return $fallback;
            }
        }
    }
}

if (! function_exists('delete_storage_object_if_key')) {
    /**
     * Delete an object from a storage disk only if the given value looks like a storage key (not an absolute URL).
     * Returns true if deleted or not needed, false on failure.
     */
    function delete_storage_object_if_key(string|null $value, array $options = []): bool
    {
        if (empty($value)) {
            return true;
        }

        $value = trim($value);
        // If it's an absolute URL or data URI, do not attempt to delete
        $scheme = parse_url($value, PHP_URL_SCHEME);
        if ($scheme && in_array(strtolower($scheme), ['http','https','data'])) {
            return true;
        }

        $disk = $options['disk'] ?? 's3';
        try {
            Storage::disk($disk)->delete($value);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
