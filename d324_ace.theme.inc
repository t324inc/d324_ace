<?php

/**
 * @file
 * D324 Ace Frontend theming functionalities.
 */

use Drupal\Component\Utility\Html;
use Drupal\Core\Render\Markup;

function template_preprocess_d324_ace_frontend_field_paragraph_links(array &$vars) {
  $vars['attributes']['class'][] = 'd324-ace-frontend-field-paragraph-links';
  $vars['attributes']['class'][] = 'links';
  foreach(['top','bottom'] as &$tb) {
    foreach($vars[$tb] as $reg_key => &$reg) {
      $reg['#type'] = 'container';
      $reg['#attributes']['class'][] = 'd324-ace-frontend-links-region';
      $reg['#attributes']['class'][] = 'd324-ace-frontend-links-region-' . $reg_key;
      foreach($reg as $link_key => &$reg_link) {
        if ($link_key === '' || $link_key[0] !== '#') {
          $reg_link['#attributes']['class'][] = 'd324-ace-button-' . $link_key;
          if(!empty($reg_link['#icon'])) {
            $reg_link['#title'] = Markup::create('<img src="/modules/d324/d324_ace/img/ace-' . $reg_link['#icon'] . '.svg" />');
          }
        }
      }
    }
  }
}

/**
 * Prepares variables for D324 Ace Frontend Field Layout wrapper templates.
 *
 * Default template: d324-ace-frontend-field-layout-wrapper.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - element: An associative array containing the properties of the element.
 *     Properties used: .
 */
function template_preprocess_d324_ace_frontend_field_layout_wrapper(array &$vars) {
  $element = $vars['element'];
  $paragraph = $element['#paragraph'];

  $vars['attributes']['data-ace-layout-id'] = ($paragraph)? $paragraph->id() : 0;
  $vars['attributes']['class'][] = Html::cleanCssIdentifier('d324-ace-frontend-field-layout-wrapper');

  $vars['content'] = $element['#children'];

  $links = $element['#d324_ace_frontend_field_layout_links'];

  foreach(['top','bottom'] as &$tb) {
    foreach($links['#' . $tb] as $reg_key => &$reg) {
      $reg['#type'] = 'container';
      $reg['#attributes']['class'][] = 'd324-ace-frontend-links-region';
      $reg['#attributes']['class'][] = 'd324-ace-frontend-links-region-' . $reg_key;
      foreach($reg as $link_key => &$reg_link) {
        if ($link_key === '' || $link_key[0] !== '#') {
          $reg_link['#attributes']['class'][] = 'd324-ace-button-' . $link_key;
          if(!empty($reg_link['#icon'])) {
            $reg_link['#title'] = Markup::create('<img src="/modules/d324/d324_ace/img/ace-' . $reg_link['#icon'] . '.svg" />');
          }
        }
      }
    }
    $vars[$tb . '_links']['#type'] = "container";
    $vars[$tb . '_links']['#attributes']['class'][] = 'd324-ace-frontend-links-region-layout';
    $vars[$tb . '_links']['#attributes']['class'][] = 'd324-ace-frontend-links-region-layout-' . $tb;
    $vars[$tb . '_links'] = $links['#' . $tb];
  }
}

/**
 * Prepares variables for D324 Ace Frontend Field Paragraph wrapper templates.
 *
 * Default template: d324-ace-frontend-field-paragraph-wrapper.html.twig.
 *
 * @param array $vars
 *   An associative array containing:
 *   - element: An associative array containing the properties of the element.
 *     Properties used: .
 */
function template_preprocess_d324_ace_frontend_field_paragraph_wrapper(array &$vars) {
  $element = $vars['element'];
  $paragraph = $element['#paragraph'];

  $vars['attributes']['data-ace-paragraph-id'] = ($paragraph)? $paragraph->id() : 0;
  $vars['attributes']['class'][] = Html::cleanCssIdentifier('d324-ace-frontend-field-paragraph-wrapper');

  $vars['content'] = $element['#children'];
  $vars['links'] = $element['#d324_ace_frontend_field_paragraph_links'];
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
  $vars['attributes']['id'] = 'd324-ace-scoped';
  $vars['attributes']['class'][] = 'd324-ace-scoped-content-wrapper';
  $vars['attributes']['class'][] = 'd324-ace-scoped-modal';
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
  $element['#attributes']['id'] = 'd324-ace-scoped';
  $element['#attributes']['class'][] = 'd324-ace-scoped-content-wrapper';
  $element['#attributes']['class'][] = 'd324-ace-scoped-dialog';
}

