<?php

namespace Drupal\d324_ace_paragraph_behaviors\Plugin\paragraphs\Behavior;

use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\paragraphs\ParagraphsBehaviorBase;

/**
 * Provides a background image feature plugin.
 *
 * @ParagraphsBehavior(
 *   id = "ace_background",
 *   label = @Translation("Background"),
 *   description = @Translation("Background settings for the paragraph."),
 *   weight = 3
 * )
 */
class AceParagraphsBackgroundBehavior extends ParagraphsBehaviorBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $paragraphs_type = $form_state->getFormObject()->getEntity();
    if ($paragraphs_type->isNew()) {
      return [];
    }
    $media_field_options = $this->getFieldNameOptions($paragraphs_type, 'entity_reference');

    $field_definitions = $this->entityFieldManager->getFieldDefinitions('paragraph', $paragraphs_type->id());
    foreach ($field_definitions as $name => $definition) {
      if ($field_definitions[$name] instanceof FieldConfigInterface) {
        if (empty($field_type) || $definition->getType() == $field_type) {
          if($field_definitions[$name]->getSetting('handler') == "default:media") {
            $fields[$name] = $definition->getLabel();
          }
        }
      }
    }

    // Show Image select form only if this entity has at least one image field.
    if (count($media_field_options) > 0) {
      $form['background_media_field'] = [
        '#type' => 'select',
        '#title' => $this->t('Background field'),
        '#description' => $this->t('Media field to be used as background.'),
        '#options' => $media_field_options,
        '#empty_value' => '',
        '#default_value' => count($media_field_options) == 1 ? key($media_field_options) : $this->configuration['background_media_field'],
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildBehaviorForm(ParagraphInterface $paragraph, array &$form, FormStateInterface $form_state) {
    $moduleHandler = \Drupal::service('module_handler');
    if ($moduleHandler->moduleExists('color_field')){
      $form['#attached']['library'][] = 'color_field/color-field-widget-spectrum';
      $form['#type'] = 'details';
      $form['#title'] = $this->t('Background Image/Color Settings');
      $form['#open'] = FALSE;
      $form['#weight'] = 3;
      $form['color_description'] = [
        '#markup' => '<p><small>Add 1 color for a solid color background.  Add 2 colors to form a gradient background.</small></p>',
      ];
      foreach([1,2] as $delta) {
        $uid = Html::getUniqueId( 'color-field-field-color_' . $delta);
        $form['background_color_' . $delta] = [
          '#type' => 'container',
          '#title' => 'Color ' . $delta . ': ',
          '#tree' => TRUE,
          '#attached' => [
            'drupalSettings' => [
              'color_field' => [
                'color_field_widget_spectrum' => [
                  $uid => [
                    'show_input' => TRUE,
                    'show_palette' => TRUE,
                    'palette' => '["#333333","#FCFCFC","#1F2949","#555555","#11689B","#FEC114","#AEBED7","#175088","#F89406","#DC3545","#FEC114","#17A2B8","#28A745","#111111","#F0F0F0","#000000","#FFFFFF"]',
                    'show_buttons' => TRUE,
                    'allow_empty' => TRUE,
                    'show_palette_only' => FALSE,
                    'show_alpha' => TRUE,
                  ],
                ],
              ],
            ],
          ],
          'color' => [
            '#title' => 'Color Value ' . $delta,
            '#type' => 'textfield',
            '#maxlength' => 7,
            '#size' => 7,
            '#attributes' => [
              'class' => [
                'js-color-field-widget-spectrum__color',
              ],
            ],
            '#default_value' => $paragraph->getBehaviorSetting($this->getPluginId(), 'background_color_' . $delta)['color'],
          ],
          'opacity' => [
            '#type' => 'number',
            '#min' => 0,
            '#max' => 1,
            '#step' => .1,
            '#attributes' => [
              'class' => [
                'js-color-field-widget-spectrum__opacity',
              ],
            ],
            '#default_value' => $paragraph->getBehaviorSetting($this->getPluginId(), 'background_color_' . $delta)['opacity'],
          ],
          '#show_alpha' => TRUE,
          '#attributes' => [
            'id' => $uid,
            'class' => 'js-color-field-widget-spectrum',
          ],
        ];
      }
      $form['hover_effects'] = [
        '#type' => 'checkboxes',
        '#title' => 'Hover Effects',
        '#description' => 'Effects for the background image (if used) when the paragraph is hovered over by the user',
        '#multiple' => TRUE,
        '#options' => [
          'zoom' => 'Zoom',
          'lighten' => 'Lighten',
          'darken' => 'Darken',
        ],
        '#default_value' => $paragraph->getBehaviorSetting($this->getPluginId(), 'hover_effects'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['background_media_field'] = $form_state->getValue('background_media_field');
  }

  /**
   * {@inheritdoc}
   */
  public function view(array &$build, Paragraph $paragraphs_entity, EntityViewDisplayInterface $display, $view_mode) {
    $build['#attached']['library'][] = 'd324_ace_paragraph_behaviors/background';
  }

  public function preprocess(&$variables) {
    parent::preprocess($variables);
    $background_field = null;
    $paragraph = $variables['paragraph'];
    if(!empty($this->configuration['background_media_field'])) {
      if(!empty($paragraph->get($this->configuration['background_media_field']))) {
        $media = $paragraph->get($this->configuration['background_media_field'])->first();
      }
      if(!empty($media)) {
        foreach ($variables as $key => $var) {
          if (is_array($var) && isset($var[$this->configuration['background_media_field']])) {
            $background_field = $var[$this->configuration['background_media_field']];
            unset($variables[$key][$this->configuration['background_media_field']]);
            $variables['use_wrapper'] = TRUE;
            if(empty($variables['wrapper_attributes'])) {
              $variables['wrapper_attributes'] = new Attribute(array());
            }
            $variables['wrapper_attributes']->addClass('paragraph-background-wrapper');
          }
        }
      }
      if($background_field) {
        $background_field['#attributes']['class'][] = 'paragraph-background-media-overlay';
        $variables['background'][] = $background_field;
        $variables['background']['#type'] = 'container';
        $variables['background']['#attributes'] = [
          'class' => ['paragraph-background-container'],
        ];
      }
    }
    if(!empty($paragraph)) {
      $background_color_1 = $paragraph->getBehaviorSetting($this->getPluginId(), 'background_color_1');
      $background_color_2 = $paragraph->getBehaviorSetting($this->getPluginId(), 'background_color_2');
      if($background_color_1['color'] && $background_color_2['color']) {
        list($r1, $g1, $b1) = sscanf($background_color_1['color'], "#%02x%02x%02x");
        $a1 = $background_color_1['opacity'];
        list($r2, $g2, $b2) = sscanf($background_color_2['color'], "#%02x%02x%02x");
        $a2 = $background_color_2['opacity'];
        $style = "background: linear-gradient(rgba($r1,$g1,$b1,$a1),rgba($r2,$g2,$b2,$a2)); ";
      } elseif($background_color_1['color']) {
        list($r1, $g1, $b1) = sscanf($background_color_1['color'], "#%02x%02x%02x");
        $a1 = $background_color_1['opacity'];
        $style = "background: rgba($r1,$g1,$b1,$a1); ";
      }
      if($hover_effects = $paragraph->getBehaviorSetting($this->getPluginId(), 'hover_effects')) {
        foreach($hover_effects as $hover_effect) {
          $variables['wrapper_attributes']->addClass($hover_effect . '-wrap');
        }
      }
      if(!empty($style)) {
        $variables['background'][] = [
          '#type' => 'container',
          '#attributes' => [
            'style' => $style,
            'class' => ['paragraph-background-color-overlay'],
          ],
        ];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'background_media_field' => '',
      'background_color_1' => [
        'color' => '',
        'opacity' => '',
      ],
      'background_color_2' => [
        'color' => '',
        'opacity' => '',
      ],
      'hover_effects' => [],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(Paragraph $paragraph) {
    return '';
  }

}
