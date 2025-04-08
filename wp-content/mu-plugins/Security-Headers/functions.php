<?php

/**
 * Plugin Name: Security Headers
 * Description: Adds important security headers to all WordPress sites
 * Version: 1.0
 * Author: Locally Known Pro
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Add security headers to all pages
 */
function client_template_add_security_headers()
{
    // Content Security Policy - Prevents XSS attacks
    // This is a basic implementation - customize based on client needs
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval' https://www.google-analytics.com https://www.googletagmanager.com https://*.googleapis.com https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; img-src 'self' data: https://www.google-analytics.com https://www.googletagmanager.com https://*.googleapis.com https://*.gstatic.com; font-src 'self' https://fonts.gstatic.com https://cdn.jsdelivr.net; connect-src 'self' https://*.googleapis.com https://www.google-analytics.com https://www.googletagmanager.com; frame-src 'self' https://www.youtube.com https://www.google.com https://player.vimeo.com; object-src 'none'");

    // X-Content-Type-Options - Prevents MIME type sniffing
    header("X-Content-Type-Options: nosniff");

    // X-Frame-Options - Prevents clickjacking
    header("X-Frame-Options: SAMEORIGIN");

    // X-XSS-Protection - Additional XSS protection
    header("X-XSS-Protection: 1; mode=block");

    // Referrer-Policy - Controls referrer information
    header("Referrer-Policy: strict-origin-when-cross-origin");

    // Permissions-Policy - Controls browser features
    header("Permissions-Policy: camera=(), microphone=(), geolocation=(), interest-cohort=()");

    // Strict-Transport-Security - Forces HTTPS
    if ($_SERVER['HTTPS'] == 'on') {
        header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
    }
}
add_action('send_headers', 'client_template_add_security_headers');
