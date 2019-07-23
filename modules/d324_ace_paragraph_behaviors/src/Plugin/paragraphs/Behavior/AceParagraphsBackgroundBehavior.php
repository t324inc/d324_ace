<?php

namespace Drupal\d324_ace_paragraph_behaviors\Plugin\paragraphs\Behavior;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\paragraphs\ParagraphsBehaviorBase;

/**
 * Provides a background image feature plugin.
 *
 * @ParagraphsBehavior(
 *   id = "ace_background",
 *   label = @Translation("Background"),
 *   description = @Translation("Media to be used as background for the paragraph."),
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
    else {
      $form['message'] = [
        '#type' => 'container',
        '#markup' => $this->t('No media field type available. Please add at least one in the <a href=":link">Manage fields</a> page.', [
          ':link' => Url::fromRoute("entity.{$paragraphs_type->getEntityType()->getBundleOf()}.field_ui_fields", [$paragraphs_type->getEntityTypeId() => $paragraphs_type->id()])
            ->toString(),
        ]),
        '#attributes' => [
          'class' => ['messages messages--error'],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    if (!$form_state->getValue('background_media_field')) {
      $form_state->setErrorByName('message', $this->t('The Background plugin cannot be enabled without a media field.'));
    }
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
    $build['#attributes']['class'][] = 'ace-paragraphs-behavior-background';
    $build['#attached']['library'][] = 'd324_ace_paragraph_behaviors/background';
    foreach (Element::children($build) as $field) {
      if ($field == $this->configuration['background_media_field']) {
        // Put the selected field into the background.
        $build[$field]['#attributes']['class'][] = 'ace-paragraphs-behavior-background--image';
      }
      else {
        // Identify all other elements to put them on top of the background.
        $build[$field]['#attributes']['class'][] = 'ace-paragraphs-behavior-background--element';
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'background_media_field' => '',
    ];
  }

}
