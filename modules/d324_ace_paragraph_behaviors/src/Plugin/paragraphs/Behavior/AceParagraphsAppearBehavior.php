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
 * Paragraph appear animation options.
 *
 * @ParagraphsBehavior(
 *   id = "ace_paragraph_appear",
 *   label = @Translation("Paragraphs scroll appear animation"),
 *   description = @Translation("Add an animated effect to the paragraph when it enters the viewport"),
 *   weight = 0,
 * )s
 */
class AceParagraphsAppearBehavior extends ParagraphsBehaviorBase {

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
    if(!empty($paragraph->getBehaviorSetting($this->getPluginId(), 'appear_effect'))) {
      $build['#attached']['library'] = array_merge(['d324_core/sal'], $build['#attached']['library']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildBehaviorForm(ParagraphInterface $paragraph, array &$form, FormStateInterface $form_state) {
    $form['#type'] = 'details';
    $form['#title'] = $this->t('Scroll Appear Animation Options');
    $form['#open'] = FALSE;
    $form['#weight'] = 5;
    $form['appear_effect'] = [
      '#type' => 'select',
      '#title' => $this->t('Appear Effect'),
      '#options' => [
        'fade' => 'Fade',
        'slide-up' => 'Slide Up',
        'slide-down' => 'Slide Down',
        'slide-left' => 'Slide Left',
        'slide-right' => 'Slide Right',
        'zoom-in' => 'Zoom In',
        'zoom-out' => 'Zoom Out',
        'flip-up' => 'Flip Up',
        'flip-down' => 'Flip Down',
        'flip-left' => 'Flip Left',
        'flip-right' => 'Flip Right',
      ],
      '#description' => $this->t('This animation will be triggered upon the paragraph entering the viewport on scroll.'),
      '#default_value' => $paragraph->getBehaviorSetting($this->getPluginId(), 'appear_effect'),
    ];
    $form['appear_duration'] = [
      '#type' => 'number',
      '#title' => $this->t('Duration in milliseconds (min 50)'),
      '#min' => '50',
      '#step' => 1,
      '#size' => 5,
      '#default_value' => $paragraph->getBehaviorSetting($this->getPluginId(), 'appear_duration'),
    ];
    $form['appear_delay'] = [
      '#type' => 'number',
      '#title' => $this->t('Delay in milliseconds'),
      '#min' => '0',
      '#step' => 1,
      '#size' => 5,
      '#default_value' => $paragraph->getBehaviorSetting($this->getPluginId(), 'appear_delay'),
    ];
    $form['appear_easing'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Easing function'),
      '#default_value' => $paragraph->getBehaviorSetting($this->getPluginId(), 'appear_easing'),
    ];
    return $form;
  }

  public function preprocess(&$variables) {
    parent::preprocess($variables);
    $paragraph = $variables['paragraph'];
    $appear_effect = $paragraph->getBehaviorSetting($this->getPluginId(), 'appear_effect');
    if(!empty($appear_effect)) {
      if(empty($variables['wrapper_attributes'])) {
        $attributes = new Attribute($variables['attributes']);
      } else {
        $attributes = $variables['wrapper_attributes'];
      }
      $attributes->setAttribute("data-sal", $appear_effect);
      if($appear_duration = $paragraph->getBehaviorSetting($this->getPluginId(), 'appear_duration')) {
        $attributes->setAttribute("data-sal-duration", $appear_duration);
      }
      if($appear_delay = $paragraph->getBehaviorSetting($this->getPluginId(), 'appear_delay')) {
        $attributes->setAttribute("data-sal-delay", $appear_delay);
        if($appear_delay > 1000) {
          if($attributes['style']) {
            $style = $attributes['style'];
          } else {
            $style = "";
          }
          $s_delay = $appear_delay / 1000.00;
          $style .= "transition-delay: ${s_delay}s;";
          $attributes->setAttribute("style", $style);
        }
      }
      if($appear_easing = $paragraph->getBehaviorSetting($this->getPluginId(), 'appear_easing')) {
        $attributes->setAttribute("data-sal-easing", $appear_easing);
      }
      if(empty($variables['wrapper_attributes'])) {
        $variables['attributes'] = $attributes->toArray();
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
