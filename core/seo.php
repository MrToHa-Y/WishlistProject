<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../config/constants.php';

    if (!function_exists('seo_escape')){
        function seo_escape(string $value): string
        {
            return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
    }

    if (!function_exists('seo_base_url')){
        function seo_base_url(): string
        {
            $isSecure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
            $scheme = $isSecure ? 'https' : 'http';
            $host = trim((string) ($_SERVER['HTTP_HOST'] ?? 'localhost'));

            return $scheme . '://' . $host;
        }
    }

    if (!function_exists('seo_absolute_url')){
        function seo_absolute_url(string $url): string
        {
            $url = trim($url);
            if ($url === ''){
                return seo_base_url() . '/';
            }

            if (preg_match('~^https?://~i', $url) === 1){
                return $url;
            }

            if (substr($url, 0, 1) !== '/'){
                $url = '/' . ltrim($url, '/');
            }

            return seo_base_url() . $url;
        }
    }

    if (!function_exists('render_seo_meta')){
        function render_seo_meta(array $meta = []): string
        {
            $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? ROUTE_HOME);
            $defaultPath = parse_url($requestUri, PHP_URL_PATH);
            if (!is_string($defaultPath) || $defaultPath === ''){
                $defaultPath = ROUTE_HOME;
            }

            $title = trim((string) ($meta['title'] ?? 'Ohmywishes'));
            $description = isset($meta['description']) ? trim((string) $meta['description']) : '';
            $robots = trim((string) ($meta['robots'] ?? 'noindex, nofollow'));
            $ogType = trim((string) ($meta['og_type'] ?? 'website'));
            $ogTitle = trim((string) ($meta['og_title'] ?? $title));
            $ogDescription = isset($meta['og_description'])
                ? trim((string) $meta['og_description'])
                : $description;
            $ogUrl = seo_absolute_url((string) ($meta['url'] ?? $defaultPath));
            $ogImage = seo_absolute_url((string) ($meta['og_image'] ?? (ASSETS_PATH . '/images/icons/icon_logo.png')));
            $ogSiteName = trim((string) ($meta['og_site_name'] ?? 'Ohmywishes'));

            $lines = [];
            $lines[] = '<title>' . seo_escape($title) . '</title>';

            if ($description !== ''){
                $lines[] = '<meta name="description" content="' . seo_escape($description) . '">';
            }

            $lines[] = '<meta name="robots" content="' . seo_escape($robots) . '">';
            $lines[] = '<meta property="og:title" content="' . seo_escape($ogTitle) . '">';

            if ($ogDescription !== ''){
                $lines[] = '<meta property="og:description" content="' . seo_escape($ogDescription) . '">';
            }

            $lines[] = '<meta property="og:image" content="' . seo_escape($ogImage) . '">';
            $lines[] = '<meta property="og:type" content="' . seo_escape($ogType) . '">';
            $lines[] = '<meta property="og:url" content="' . seo_escape($ogUrl) . '">';
            $lines[] = '<meta property="og:site_name" content="' . seo_escape($ogSiteName) . '">';

            return implode(PHP_EOL . '    ', $lines);
        }
    }