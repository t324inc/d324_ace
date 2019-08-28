<?php

namespace Drupal\d324_ace_paragraph_behaviors\Plugin\paragraphs\Behavior;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\paragraphs\ParagraphsBehaviorBase;

/**
 * Custom paragraph wrapper padding/margin settings.
 *
 * @ParagraphsBehavior(
 *   id = "ace_paragraph_padding_margin",
 *   label = @Translation("Padding/Margin settings"),
 *   description = @Translation("Provides padding and margin settings"),
 *   weight = 0,
 * )s
 */
class AceParagraphsPaddingMarginBehavior extends ParagraphsBehaviorBase {

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(ParagraphsType $paragraphs_type) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function view(array &$build, Paragraph $paragraph, EntityViewDisplayInterface $display, $view_mode) {
    $dimstring = $this->dimensionToStyle($paragraph);
    $build['#attributes']['style'] = $dimstring;
  }

  /**
   * {@inheritdoc}
   */
  public function buildBehaviorForm(ParagraphInterface $paragraph, array &$form, FormStateInterface $form_state) {
    $form['#type'] = 'details';
    $form['#title'] = $this->t('Margin/Padding Settings');
    $form['#open'] = FALSE;
    $form['#weight'] = 4;
    foreach(['padding', 'margin'] as $type) {
      $form[$type] = [
        '#type' => 'container',
        'dimensions' => [
          '#type' => 'container',
          '#attributes' => [
            'style' => 'display: flex; flex-wrap: wrap;'
          ],
        ],
        'description' => [
          '#markup' => $this->t("<strong>" . ucfirst($type) . ":</strong> Enter the values including the unit of measure (ex: 12px or 2rem) or \"0\" or \"auto\" .  You may also add \"!\" to use !important (ex: 20px! or 50rem!)"),
        ],
        '#attributes' => [
          'style' => 'margin-top: 1rem;',
        ]
      ];
      $this->addDimensionInputs($form[$type], $paragraph,$type);
    }
    return $form;
  }

  protected function addDimensionInputs(&$element, $paragraph, $type) {
    foreach(['Top', 'Right', 'Bottom', 'Left'] as $d) {
      $dl = strtolower($d);
      $element['dimensions'][$dl] = [
        '#type' => 'textfield',
        '#title_display' => 'hidden',
        '#attributes' => [
          'size' => 4,
          'style' => 'width: auto;',
          'placeholder' => $d,
        ],
        '#wrapper_attributes' => [
          'style' => 'margin: 0 10px 0 0;',
        ],
        '#default_value' => $paragraph->getBehaviorSetting($this->getPluginId(), $type)['dimensions'][$dl],
      ];
    }
  }

  protected function dimensionToStyle($paragraph) {
    $styles = [];
    foreach (['padding', 'margin'] as $type) {
      $settings = $paragraph->getBehaviorSetting($this->getPluginId(), $type);
      if(!empty($settings['dimensions'])) {
        foreach ($settings['dimensions'] as $dl => $val) {
          if (!empty($val) || $val === '0') {
            $string = $type . '-' . $dl . ': ' . $val;
            $string = str_replace('!', ' !important', $string);
            $styles[] = $string;
          }
        }
      }
    }
    if (!empty($styles)) {
      $stylestring = implode($styles, '; ');
      return $stylestring;
    }
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function validateBehaviorForm(ParagraphInterface $paragraph, array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    foreach(['padding', 'margin'] as $type) {
      if(!empty($values[$type])) {
        foreach($values[$type]['dimensions'] as $key => $val) {
          $trimval = trim($val);
          if(!empty($trimval) && $trimval != '0' && $trimval != '0!') {
            $remnum = preg_replace('/[0-9\.]+/', '', $trimval);
            $remnum = str_replace('!', '', $remnum);
            if(!in_array($remnum, ['px', 'pt', 'rem', 'em', 'vw', 'vh', '%', 'auto'])) {
              $form_state->setError($form[$type]['dimensions'][$key], $trimval . ' is not a valid CSS size');
            }
          }
          if($trimval !== $val) {
            $values[$type]['dimensions'][$key] = $trimval;
            $form_state->setValue($type, $values[$type]);
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(Paragraph $paragraph) {
    return '';
  }

}
