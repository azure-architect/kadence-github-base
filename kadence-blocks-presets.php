<?php

/**
 * Plugin Name: Kadence Blocks Presets
 * Description: Adds custom presets for Kadence Blocks
 * Version: 1.0
 * Author: Your Agency
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
  exit;
}

/**
 * Add custom presets for Kadence Blocks
 */
function client_template_kadence_blocks_presets($presets)
{
  // Add preset for Info Box
  $presets['info-box'] = array_merge($presets['info-box'] ?? [], [
    [
      'name' => 'Agency Service Box',
      'key' => 'agency-service-box',
      'icon' => [
        'size' => [
          'desktop' => 50,
        ],
        'color' => '#3182CE',
        'style' => 'default',
        'borderRadius' => 5,
        'padding' => [
          'desktop' => ['', '', '', ''],
        ],
        'margin' => [
          'desktop' => ['', '', '15', ''],
        ],
      ],
      'titles' => [
        'font' => [
          'size' => [
            'desktop' => 24,
          ],
          'lineHeight' => [
            'desktop' => 1.5,
          ],
          'family' => '',
          'color' => '',
          'weight' => 600,
        ],
        'margin' => [
          'desktop' => ['', '', '10', ''],
        ],
      ],
      'texts' => [
        'font' => [
          'size' => [
            'desktop' => 16,
          ],
          'lineHeight' => [
            'desktop' => 1.6,
          ],
          'family' => '',
          'color' => '',
        ],
      ],
      'containerBG' => '#ffffff',
      'containerBorder' => '#e2e8f0',
      'containerBorderWidth' => [
        'desktop' => [1, 1, 1, 1],
      ],
      'containerBorderRadius' => 5,
      'containerPadding' => [
        'desktop' => [30, 30, 30, 30],
      ],
      'containerMargin' => [
        'desktop' => [0, 0, 0, 0],
      ],
      'linkStyle' => 'button',
      'linkColor' => '#3182CE',
      'linkHoverColor' => '#2B6CB0',
      'linkMargin' => [
        'desktop' => [15, 0, 0, 0],
      ],
    ],
  ]);

  // Add preset for Call to Action
  $presets['call-to-action'] = array_merge($presets['call-to-action'] ?? [], [
    [
      'name' => 'Agency CTA',
      'key' => 'agency-cta',
      'align' => 'center',
      'textAlign' => 'center',
      'title' => [
        'text' => 'Ready to get started?',
        'color' => '#ffffff',
        'size' => [
          'desktop' => 32,
        ],
        'lineHeight' => [
          'desktop' => 1.3,
        ],
        'margin' => [
          'desktop' => [0, 0, 20, 0],
        ],
      ],
      'text' => [
        'text' => 'Contact us today to discuss your project.',
        'color' => '#ffffff',
        'size' => [
          'desktop' => 18,
        ],
        'lineHeight' => [
          'desktop' => 1.6,
        ],
        'margin' => [
          'desktop' => [0, 0, 30, 0],
        ],
      ],
      'buttons' => [
        [
          'text' => 'Contact Us',
          'link' => '/contact/',
          'color' => '#3182CE',
          'background' => '#ffffff',
          'hoverColor' => '#ffffff',
          'hoverBackground' => '#2B6CB0',
          'borderRadius' => 5,
          'borderStyle' => 'solid',
          'size' => 'large',
          'padding' => [15, 30, 15, 30],
        ],
      ],
      'backgroundType' => 'color',
      'background' => '#3182CE',
      'containerPadding' => [
        'desktop' => [60, 40, 60, 40],
        'tablet' => [50, 30, 50, 30],
        'mobile' => [40, 20, 40, 20],
      ],
      'containerMargin' => [
        'desktop' => [0, 0, 0, 0],
      ],
      'containerBorderRadius' => 5,
    ],
  ]);

  // Add preset for Testimonials
  $presets['testimonials'] = array_merge($presets['testimonials'] ?? [], [
    [
      'name' => 'Agency Testimonials',
      'key' => 'agency-testimonials',
      'layout' => 'grid',
      'style' => 'card',
      'columns' => [
        'desktop' => 3,
        'tablet' => 2,
        'mobile' => 1,
      ],
      'columnGap' => 30,
      'containerBgColor' => '#ffffff',
      'containerBorderColor' => '#e2e8f0',
      'containerBorderWidth' => [1, 1, 1, 1],
      'containerBorderRadius' => 5,
      'containerPadding' => [
        'desktop' => [30, 30, 30, 30],
      ],
      'mediaStyle' => 'card',
      'mediaAlign' => 'top',
      'mediaWidth' => [
        'desktop' => 60,
      ],
      'mediaBackground' => '#f7fafc',
      'mediaBorderRadius' => 100,
      'mediaPadding' => [
        'desktop' => [0, 0, 0, 0],
      ],
      'titleFont' => [
        'size' => [
          'desktop' => 18,
        ],
        'lineHeight' => [
          'desktop' => 1.4,
        ],
        'weight' => 600,
        'color' => '#1A202C',
      ],
      'contentFont' => [
        'size' => [
          'desktop' => 16,
        ],
        'lineHeight' => [
          'desktop' => 1.6,
        ],
        'color' => '#4A5568',
      ],
      'nameFont' => [
        'size' => [
          'desktop' => 16,
        ],
        'lineHeight' => [
          'desktop' => 1.4,
        ],
        'weight' => 600,
        'color' => '#2D3748',
      ],
      'occupationFont' => [
        'size' => [
          'desktop' => 14,
        ],
        'lineHeight' => [
          'desktop' => 1.4,
        ],
        'color' => '#718096',
      ],
      'displayIcon' => true,
      'iconStyle' => 'basic',
      'iconColor' => '#FFB900',
      'iconSize' => [
        'desktop' => 20,
      ],
    ],
  ]);

  return $presets;
}
add_filter('kadence_blocks_get_block_presets', 'client_template_kadence_blocks_presets');
