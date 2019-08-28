<?php

namespace Drupal\d324_ace\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Render\Element;

/**
 * Functionality to edit a paragraph.
 */
class AceParagraphForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $route_match = $this->getRouteMatch();

    $parent_entity_type = $route_match->getParameter('parent_entity_type');
    $parent_entity_revision = $route_match->getParameter('parent_entity_revision');
    $field_name = $route_match->getParameter('field');
    $delta = $route_match->getParameter('delta');

    // Get the parent revision if available, otherwise the parent.
    $parent_entity_revision = $this->getParentRevisionOrParent($parent_entity_type, $parent_entity_revision);

    $field = $parent_entity_revision->get($field_name);
    $field_definition = $field->getFieldDefinition();
    $field_label = $field_definition->getLabel();

    $form['#title'] = $this->t('Edit @delta of @field of %label', [
      '@delta' => $delta,
      '@field' => $field_label,
      '%label' => $parent_entity_revision->label(),
    ]);

    /** @var \Drupal\paragraphs\ParagraphInterface $paragraph */
    $paragraph = $route_match->getParameter('paragraph');

    $behavior_element = [];
    // Build the behavior plugins fields, do not display behaviors when
    // translating and untranslatable fields are hidden.
    $paragraphs_type = $paragraph->getParagraphType();
    if ($paragraphs_type && \Drupal::currentUser()->hasPermission('edit behavior plugin settings')) {
      $behavior_element['#weight'] = 99;
      $behavior_element['#type'] = 'container';
      $behavior_element['#title'] = 'Additional Styles and Behaviors';
      $behavior_element['#attributes']['style'] = 'margin-top: 1rem; padding-top: 1rem; border-top: 1px solid #ccc;';
      $element_parents = [];
      foreach ($paragraphs_type->getEnabledBehaviorPlugins() as $plugin_id => $plugin) {
        $behavior_element[$plugin_id] = [
          '#type' => 'container',
          '#tree' => TRUE,
          //'#group' => implode('][', array_merge($element_parents, ['paragraph_behavior'])),
        ];
        $subform_state = SubformState::createForSubform($behavior_element[$plugin_id], $form, $form_state);
        if ($plugin_form = $plugin->buildBehaviorForm($paragraph, $behavior_element[$plugin_id], $subform_state)) {
          $behavior_element[$plugin_id] = $plugin_form;
          // Add the paragraphs-behavior class, so that we are able to show
          // and hide behavior fields, depending on the active perspective.
          // $behavior_element[$plugin_id]['#attributes']['class'][] = 'paragraphs-behavior';
        }
      }
    }
    $form['behavior_plugins'] = $behavior_element;

    if(!empty($form['actions'])) {
      foreach(Element::children($form['actions']) as $action) {
        switch($form['actions'][$action]['#value']->render()) {
          case "Save":
            $action_class = 'success';
            break;
          case "Delete":
            $action_class = 'danger';
            break;
          case "Cancel":
            $action_class = 'warning';
            break;
          default:
            $action_class = 'primary';
            break;
        }
        $form['actions'][$action]['#attributes'] = [
          'class' => ["bg-$action_class"],
        ];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function copyFormValuesToEntity(EntityInterface $entity, array $form, FormStateInterface $form_state) {
    parent::copyFormValuesToEntity($entity, $form, $form_state);

    /** @var \Drupal\paragraphs\ParagraphInterface $paragraph */
    $paragraph = $entity;

    if (isset($form['behavior_plugins'])) {
      // Submit all enabled behavior plugins.
      $paragraphs_type = $paragraph->getParagraphType();
      foreach ($paragraphs_type->getEnabledBehaviorPlugins() as $plugin_id => $plugin_values) {
        if (!isset($form['behavior_plugins'][$plugin_id])) {
          $form['behavior_plugins'][$plugin_id] = [];
        }
        if (isset($form['behavior_plugins'][$plugin_id]) && \Drupal::currentUser()->hasPermission('edit behavior plugin settings')) {
          if ($form_state instanceof SubformStateInterface) {
            $form_state = $form_state->getCompleteFormState();
          }
          $subform_state = SubformState::createForSubform($form['behavior_plugins'][$plugin_id], $form_state->getCompleteForm(), $form_state);
          if (isset($form['behavior_plugins'][$plugin_id])) {
            $plugin_values->submitBehaviorForm($paragraph, $form['behavior_plugins'][$plugin_id], $subform_state);
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $route_match = $this->getRouteMatch();
    $parent_entity_type = $route_match->getParameter('parent_entity_type');
    $parent_entity_revision = $route_match->getParameter('parent_entity_revision');
    $field = $route_match->getParameter('field');
    $delta = $route_match->getParameter('delta');

    $this->entity->setNewRevision(TRUE);
    $this->entity->save();

    // Get the parent revision if available, otherwise the parent.
    $parent_entity_revision = $this->getParentRevisionOrParent($parent_entity_type, $parent_entity_revision);

    $layout_paragraph = $route_match->getParameter('layout_paragraph');
    $layout_region = $route_match->getParameter('layout_region');

    $parent_entity_revision->get($field)->get($delta)->setValue([
      'target_id' => $this->entity->id(),
      'target_revision_id' => $this->entity->getRevisionId(),
      'section_id' => $layout_paragraph->id(),
      'region' => $layout_region,
    ]);

    if(!empty($parent_entity_revision->isDefaultRevision())) {
      $parent_entity_revision->setNewRevision(TRUE);
      $type_label = $this->entity->type->entity->label();
      $field_name = $route_match->getParameter('field');
      $parent_entity_revision->revision_log = "Updated $type_label paragraph in $field_name via front-end editing.";
      $parent_entity_revision->setRevisionCreationTime(REQUEST_TIME);
      // Fix for https://www.drupal.org/project/entity_reference_revisions/issues/3025709
      $parent_entity_revision->setRevisionTranslationAffected(TRUE);
    }
    $save_status = $parent_entity_revision->save();

    // Use the parent revision id if available, otherwise the parent id.
    $parent_revision_id = ($parent_entity_revision->getRevisionId()) ? $parent_entity_revision->getRevisionId() : $parent_entity_revision->id();
    $form_state->setTemporary(['parent_entity_revision' => $parent_revision_id]);

    return $save_status;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    /** @var \Drupal\paragraphs\ParagraphInterface $entity */
    $entity = $this->buildEntity($form, $form_state);

    $paragraphs_type = $entity->getParagraphType();
    if (\Drupal::currentUser()->hasPermission('edit behavior plugin settings')) {
      foreach ($paragraphs_type->getEnabledBehaviorPlugins() as $plugin_id => $plugin_values) {
        if (!empty($form['behavior_plugins'][$plugin_id])) {
          if ($form_state instanceof SubformStateInterface) {
            $form_state = $form_state->getCompleteFormState();
          }
          $subform_state = SubformState::createForSubform($form['behavior_plugins'][$plugin_id], $form_state->getCompleteForm(), $form_state);
          $plugin_values->validateBehaviorForm($entity, $form['behavior_plugins'][$plugin_id], $subform_state);
        }
      }
    }

    return $entity;
  }

  /**
   * @param $parent_entity_type
   * @param $parent_entity_revision
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getParentRevisionOrParent($parent_entity_type, $parent_entity_revision) {
    $entity_storage = $this->entityTypeManager->getStorage($parent_entity_type);
    if ($this->entityTypeManager->getDefinition($parent_entity_type)->isRevisionable()) {
      return $entity_storage->loadRevision($parent_entity_revision);
    }
    else {
      return $entity_storage->load($parent_entity_revision);
    }
  }

}
