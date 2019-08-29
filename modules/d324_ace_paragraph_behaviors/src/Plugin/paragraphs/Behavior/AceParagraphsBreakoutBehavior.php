<?php

namespace Drupal\d324_ace_paragraph_behaviors\Plugin\paragraphs\Behavior;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Template\Attribute;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\paragraphs\ParagraphInterface;
use Drupal\paragraphs\ParagraphsBehaviorBase;

/**
 * Paragraph breakout container style options.
 *
 * @ParagraphsBehavior(
 *   id = "ace_paragraph_breakout",
 *   label = @Translation("Paragraphs breakout options"),
 *   description = @Translation("Render the paragraph as a breakout container spanning edge to edge on the screen"),
 *   weight = 0,
 * )s
 */
class AceParagraphsBreakoutBehavior extends ParagraphsBehaviorBase {

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

  }

  /**
   * {@inheritdoc}
   */
  public function buildBehaviorForm(ParagraphInterface $paragraph, array &$form, FormStateInterface $form_state) {
    $form['#type'] = 'details';
    $form['#title'] = $this->t('Breakout Style Options');
    $form['#open'] = FALSE;
    $form['#weight'] = 2;
    $form['breakout_style'] = [
      '#type' => 'select',
      '#options' => [
        'normal' => 'Normal - No Breakout',
        'fluid' => 'Breakout - Fluid Edge to Edge',
        'container' => 'Breakout - Content in Container',
      ],
      '#description' => $this->t('Select the breakout style options for the paragraph.'),
      '#default_value' => $paragraph->getBehaviorSetting($this->getPluginId(), 'breakout_style'),
    ];

    return $form;
  }

  public function preprocess(&$variables) {
    parent::preprocess($variables);
    $paragraph = $variables['paragraph'];
    $breakout_class = $paragraph->getBehaviorSetting($this->getPluginId(), 'breakout_style');
    if(!empty($breakout_class) && $breakout_class != "normal") {
      if(empty($variables['wrapper_attributes'])) {
        $variables['wrapper_attributes'] = new Attribute(array());
      }
      $variables['wrapper_attributes']->addClass("breakout-container");
      if($breakout_class == "container") {
        $variables['attributes']['class'][] = "container";
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
