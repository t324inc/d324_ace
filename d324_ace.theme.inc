<?php

/**
 * @file
 * D324 Ace Frontend theming functionalities.
 */

use Drupal\Core\Render\Markup;

/**
 * Prepares variables for D324 Ace Frontend Field Layout wrapper templates.
 *
 * Default template: d324-ace-frontend-layout-wrapper.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - element: An associative array containing the properties of the element.
 *     Properties used: .
 */
function template_preprocess_d324_ace_frontend_layout_wrapper(array &$vars) {
  $element = $vars['element'];
  $paragraph = $element['#paragraph'];

  $vars['attributes']['data-ace-layout-id'] = ($paragraph)? $paragraph->id() : 0;
  $vars['attributes']['class'][] = 'd324-ace-frontend-layout-wrapper';

  $vars['content'] = $element['#children'];

  $links = $element['#layout_links'];

  _d324_ace_prepare_links($vars, $links, 'layout');
}

/**
 * Prepares variables for D324 Ace Frontend Field Paragraph wrapper templates.
 *
 * Default template: d324-ace-frontend-paragraph-wrapper.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - element: An associative array containing the properties of the element.
 *     Properties used: .
 */
function template_preprocess_d324_ace_frontend_paragraph_wrapper(array &$vars) {
  $element = $vars['element'];
  $paragraph = $element['#paragraph'];

  $vars['attributes']['data-ace-paragraph-id'] = ($paragraph)? $paragraph->id() : 0;
  $vars['attributes']['class'][] = 'd324-ace-frontend-paragraph-wrapper';

  $vars['content'] = $element['#children'];

  $links = $element['#paragraph_links'];

  _d324_ace_prepare_links($vars, $links, 'paragraph');
}

/**
 * Prepares variables for D324 Ace frontend modal content
 *
 * Default template: d324-ace-frontend-modal-content-wrapper.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - element: An associative array containing the properties of the element.
 */
function template_preprocess_d324_ace_frontend_modal_content_wrapper(array &$vars) {
  $vars['attributes']['class'][] = 'd324-ace-modal';
}

/**
 * Prepares variables for D324 Ace frontend dialog content
 *
 * Default template: d324-ace-frontend-dialog-content-wrapper.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - element: An associative array containing the properties of the element.
 */
function template_preprocess_d324_ace_frontend_dialog_content_wrapper(array &$vars) {
  $element = $vars['element'];
}

