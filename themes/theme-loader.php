<?php

$theme = isset( $_GET['theme'] ) ? strtolower( trim( wp_unslash( $_GET['theme'] ) ) ) : 'base';
$scope = isset( $_GET['scope'] ) ? trim( wp_unslash( $_GET['scope'] ) ) : '';

// Restrict to a safe theme-name allowlist to prevent path traversal.
$theme = preg_replace( '/[^a-z0-9_-]/', '', $theme );

// This file is requested directly as a stylesheet URL, so WordPress
// (and SECTION_WIDGET_DIR_PATH) may not be loaded. Fall back to this
// file's own location (the plugin dir is one level up from /themes).
if ( ! defined( 'SECTION_WIDGET_DIR_PATH' ) ) {
    define( 'SECTION_WIDGET_DIR_PATH', dirname( __DIR__ ) );
}

$content = @file_get_contents(SECTION_WIDGET_DIR_PATH . "/themes/{$theme}/sw-theme.css");

if(!$content) {
    // Try again with the default theme
    $content = @file_get_contents(SECTION_WIDGET_DIR_PATH . "/themes/base/sw-theme.css");
}

if($content) {
    $content = str_replace('%scope%', $scope, $content);
    $content = str_replace('url(images', "url({$theme}/images", $content);
    header('Content-Type: text/css');
    echo wp_kses_post($content);
}

?>