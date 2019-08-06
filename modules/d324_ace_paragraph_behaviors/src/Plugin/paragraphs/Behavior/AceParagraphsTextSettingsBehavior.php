<?php

namespace Drupal\d324_ace_paragraph_behaviors\Plugin\paragraphs\Behavior;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\paragraphs\ParagraphsBehaviorBase;

/**
 * Custom paragraph wrapper text settings.
 *
 * @ParagraphsBehavior(
 *   id = "ace_paragraph_text_settings",
 *   label = @Translation("Text settings"),
 *   description = @Translation("Provides size, alignment, and color settings"),
 *   weight = 0,
 * )s
 */
class AceParagraphsTextSettingsBehavior extends ParagraphsBehaviorBase {

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
    $class = $paragraph->getBehaviorSetting($this->getPluginId(), 'extra_classes');
    $build['#attributes']['class'][] = $class;
  }

  /**
   * {@inheritdoc}
   */
  public function buildBehaviorForm(ParagraphInterface $paragraph, array &$form, FormStateInterface $form_state) {
    $form['extra_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Extra classes'),
      '#description' => $this->t('Extra CSS classes to add to the paragraph HTML wrapper element'),
      '#default_value' => $paragraph->getBehaviorSetting($this->getPluginId(), 'extra_classes'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateBehaviorForm(ParagraphInterface $paragraph, array &$form, FormStateInterface $form_state) {
    if(!empty($form_state->getValue('extra_classes'))) {
      $values = explode(" ", $form_state->getValue('extra_classes'));
      foreach($values as $value) {
        if(!is_numeric($value)) {
          $form_state->setError($form, 'Extra classes must be a number or something');
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(Paragraph $paragraph) {
    return [$this->t('Wrapper class element')];
  }

}
