<?php

namespace Drupal\d324_ace\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\d324_ace\Ajax\AceCloseModalDialogCommand;
use Drupal\d324_ace\Ajax\AceReattachBehaviors;
use Drupal\d324_ace\Ajax\AceScrollToParagraph;

/**
 * Functionality to edit a paragraph through a modal.
 */
class AceModalParagraphForm extends AceParagraphForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // We don't need this title on the Modal because we stay on the same page
    // using a Modal, thus we don't loose context.
    unset($form['#title']);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['#prefix'] = '<div id="d324-ace-frontend-modal-form" class="adminimal">';
    $form['#suffix'] = '</div>';

    // @TODO: fix problem with form is outdated.
    $form['#token'] = FALSE;

    // Define alternative submit callbacks using AJAX by copying the default
    // submit callbacks to the AJAX property.
    $submit = &$form['actions']['submit'];
    $submit['#ajax'] = [
      'callback' => '::ajaxSave',
      'event' => 'click',
      'progress' => [
        'type' => 'throbber',
        'message' => NULL,
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxSave(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // When errors occur during form validation, show them to the user.
    if ($form_state->getErrors()) {
      unset($form['#prefix']);
      unset($form['#suffix']);
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -10,
      ];
      $response->addCommand(new HtmlCommand('#d324-ace-frontend-modal-form', $form));
    }
    else {
      // Get all necessary data to be able to correctly update the correct
      // field on the parent node.
      $route_match = $this->getRouteMatch();
      $paragraph = $route_match->getParameter('paragraph');
      $parent_entity_type = $route_match->getParameter('parent_entity_type');
      $temporary_data = $form_state->getTemporary();
      $parent_entity_revision = isset($temporary_data['parent_entity_revision']) ?
        $temporary_data['parent_entity_revision'] :
        $route_match->getParameter('parent_entity_revision');
      $field_name = $route_match->getParameter('field');
      $field_wrapper_id = $route_match->getParameter('field_wrapper_id');

      // Get the parent revision if available, otherwise the parent.
      $parent_entity_revision = $this->getParentRevisionOrParent($parent_entity_type, $parent_entity_revision);

      // Refresh the paragraphs field.
      $response->addCommand(
        new ReplaceCommand(
          '[data-ace-field-wrapper=' . $field_wrapper_id . ']',
          $parent_entity_revision->get($field_name)->view('default'))
      );

      // Add change event after refreshing.
      $response->addCommand(
        new InvokeCommand(
          '[data-ace-field-wrapper=' . $field_wrapper_id . ']',
          'change'
        )
      );

      $response->addCommand(new AceCloseModalDialogCommand());

      $response->addCommand(new AceReattachBehaviors());

      $response->addCommand(new AceScrollToParagraph($paragraph->id()));
    }

    return $response;
  }

}
