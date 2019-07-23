<?php

namespace Drupal\d324_ace\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\d324_ace\Ajax\AceCloseModalDialogCommand;
use Drupal\d324_ace\Ajax\AceOpenModalDialogCommand;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Controller for all modal dialogs.
 */
class AceModalController extends AceControllerBase {
   /**
    * Create a modal dialog to add the first paragraph.
    */
   public function addFirst($parent_entity_type, $parent_entity_bundle, $parent_entity_revision, $field, $field_wrapper_id, $delta, $position, $js = 'nojs', $bundle = NULL) {

     $paragraph_title = $this->getParagraphTitle($parent_entity_type, $parent_entity_bundle, $field);

     if ($bundle) {
       $newParagraph = Paragraph::create(['type' => $bundle]);
       $form = $this->entityFormBuilder()->getForm($newParagraph, 'd324_ace_frontend_modal_add', []);
       $form['#title'] = $this->t('Add @paragraph_title', ['@paragraph_title' => $paragraph_title]);
     }
     else {
       $entity = $this->entityTypeManager()->getStorage($parent_entity_type)->loadRevision($parent_entity_revision);
       $bundle_fields = $this->entityFieldManager->getFieldDefinitions($parent_entity_type, $entity->bundle());
       $field_definition = $bundle_fields[$field];
       $bundles = $field_definition->getSetting('handler_settings')['target_bundles'];

       if ($field_definition->getSetting('handler_settings')['negate']) {
         $bundles = array_diff_key(\Drupal::service('entity_type.bundle.info')->getBundleInfo('paragraph'), $bundles);
       }

       $routeParams = [
         'parent_entity_type' => $parent_entity_type,
         'parent_entity_bundle' => $parent_entity_bundle,
         'parent_entity_revision' => $parent_entity_revision,
         'field' => $field,
         'field_wrapper_id' => $field_wrapper_id,

         'delta' => $delta,
         'position' => $position,
         'js' => $js,
       ];

       $form = \Drupal::formBuilder()->getForm('\Drupal\d324_ace\Form\AceModalParagraphAddSelectTypeForm', $routeParams, $bundles);
       $form['#title'] = $this->t('Add @paragraph_title', ['@paragraph_title' => $paragraph_title]);
     }
     return $form;  }

  /**
   * Create a modal dialog to add a single paragraph.
   */
  public function add($parent_entity_type, $parent_entity_bundle, $parent_entity_revision, $field, $field_wrapper_id, $delta, $paragraph, $paragraph_revision, $layout_paragraph, $layout_region, $position, $js = 'nojs', $bundle = NULL) {

    $paragraph_title = $this->getParagraphTitle($parent_entity_type, $parent_entity_bundle, $field);

    if ($bundle) {
      $newParagraph = Paragraph::create(['type' => $bundle]);
      $paragraph_title .= " - " . $newParagraph->getParagraphType()->label();
      $form = $this->entityFormBuilder()->getForm($newParagraph, 'd324_ace_frontend_modal_add', []);
      if($js == "ajax") {
        $response = new AjaxResponse();
        $response->addCommand(new AceCloseModalDialogCommand('#drupal-off-canvas'));
        $response->addCommand(new AceOpenModalDialogCommand($this->t('Add @paragraph_title', ['@paragraph_title' => $paragraph_title]), render($form)));
        return $response;
      } else {
        $form['#title'] = $this->t('Add @paragraph_title', ['@paragraph_title' => $paragraph_title]);
        return $form;
      }
    }
    else {
      // Get the parent revision if available, otherwise the parent.
      $entity = $this->getParentRevisionOrParent($parent_entity_type, $parent_entity_revision);

      $bundle_fields = $this->entityFieldManager->getFieldDefinitions($parent_entity_type, $entity->bundle());
      $field_definition = $bundle_fields[$field];
      $bundles = $field_definition->getSetting('handler_settings')['target_bundles'];

      if ($field_definition->getSetting('handler_settings')['negate']) {
        $bundles = array_diff_key(\Drupal::service('entity_type.bundle.info')->getBundleInfo('paragraph'), $bundles);
      }

      $routeParams = [
        'parent_entity_type'     => $parent_entity_type,
        'parent_entity_bundle'   => $parent_entity_bundle,
        'parent_entity_revision' => $parent_entity_revision,
        'field'                  => $field,
        'field_wrapper_id'       => $field_wrapper_id,
        'delta'                  => $delta,
        'paragraph'              => $paragraph->id(),
        'paragraph_revision'     => $paragraph->getRevisionId(),
        'layout_paragraph'       => $layout_paragraph->id(),
        'layout_region'          => $layout_region,
        'position'               => $position,
        'js'                     => $js,
      ];

      $form = \Drupal::formBuilder()->getForm('\Drupal\d324_ace\Form\AceModalParagraphAddSelectTypeForm', $routeParams, $bundles);
      $form['#title'] = $this->t('Add @paragraph_title', ['@paragraph_title' => $paragraph_title]);

    }
    return $form;
  }



  /**
   * Create a modal dialog to add a single paragraph.
   */
  public function add_layout($parent_entity_type, $parent_entity_bundle, $parent_entity_revision, $field, $field_wrapper_id, $delta, $paragraph, $paragraph_revision, $position, $js = 'nojs', $bundle = NULL) {

    $paragraph_title = $this->getParagraphTitle($parent_entity_type, $parent_entity_bundle, $field);

    if ($bundle) {
      $newParagraph = Paragraph::create(['type' => $bundle]);
      $paragraph_title .= " - " . $newParagraph->getParagraphType()->label();
      $form = $this->entityFormBuilder()->getForm($newParagraph, 'd324_ace_frontend_modal_add_layout', []);
      if($js == "ajax") {
        $response = new AjaxResponse();
        $response->addCommand(new AceCloseModalDialogCommand('#drupal-off-canvas'));
        $response->addCommand(new AceOpenModalDialogCommand($this->t('Add @paragraph_title', ['@paragraph_title' => $paragraph_title]), render($form)));
        return $response;
      } else {
        $form['#title'] = $this->t('Add @paragraph_title', ['@paragraph_title' => $paragraph_title]);
        return $form;
      }
    }
    else {
      // Get the parent revision if available, otherwise the parent.
      $entity = $this->getParentRevisionOrParent($parent_entity_type, $parent_entity_revision);

      $bundle_fields = $this->entityFieldManager->getFieldDefinitions($parent_entity_type, $entity->bundle());
      $field_definition = $bundle_fields[$field];
      $bundles = $field_definition->getSetting('handler_settings')['target_bundles'];

      if ($field_definition->getSetting('handler_settings')['negate']) {
        $bundles = array_diff_key(\Drupal::service('entity_type.bundle.info')->getBundleInfo('paragraph'), $bundles);
      }

      $routeParams = [
        'parent_entity_type'     => $parent_entity_type,
        'parent_entity_bundle'   => $parent_entity_bundle,
        'parent_entity_revision' => $parent_entity_revision,
        'field'                  => $field,
        'field_wrapper_id'       => $field_wrapper_id,
        'delta'                  => $delta,
        'paragraph'              => $paragraph->id(),
        'paragraph_revision'     => $paragraph->getRevisionId(),
        'position'               => $position,
        'js'                     => $js,
      ];

      $form = \Drupal::formBuilder()->getForm('\Drupal\d324_ace\Form\AceModalParagraphAddSelectLayoutTypeForm', $routeParams, $bundles);
      $form['#title'] = $this->t('Add Layout @paragraph_title', ['@paragraph_title' => $paragraph_title]);

    }
    return $form;
  }

  /**
   * Create a modal dialog to edit a single paragraph.
   */
  public function edit($parent_entity_type, $parent_entity_bundle, $parent_entity_revision, $field, $field_wrapper_id, $delta, $paragraph, $paragraph_revision, $js = 'nojs') {
    $form = $this->entityFormBuilder()->getForm($paragraph_revision, 'd324_ace_frontend_modal_edit', []);
    $paragraph_title = $this->getParagraphTitle($parent_entity_type, $parent_entity_bundle, $field);
    $paragraph_title .= " - " . $paragraph->getParagraphType()->label();
    if($js == "ajax") {
      $scopedwrap = [
        '#theme' => 'd324_ace_frontend_modal_content_wrapper',
        '#content' => $form,
      ];
      $response = new AjaxResponse();
      $response->addCommand(new AceCloseModalDialogCommand('#drupal-off-canvas'));
      $response->addCommand(new AceOpenModalDialogCommand($this->t('Editing @paragraph_title', ['@paragraph_title' => $paragraph_title]), $scopedwrap));
      return $response;
    } else {
      $form['#title'] = $this->t('Editing @paragraph_title', ['@paragraph_title' => $paragraph_title]);
      return [
        '#theme' => 'd324_ace_frontend_modal_content_wrapper',
        '#content' => $form,
      ];
    }
  }

  /**
   * Create a modal dialog to translate a single paragraph.
   */
  public function translate($parent_entity_type, $parent_entity_bundle, $parent_entity_revision, $field, $field_wrapper_id, $delta, Paragraph $paragraph, $paragraph_revision, $js = 'nojs') {
    $langcode = $this->languageManager()->getCurrentLanguage()->getId();
    $translated_paragraph = $paragraph->addTranslation($langcode, $paragraph->toArray());
    $form = $this->entityFormBuilder()->getForm($translated_paragraph, 'd324_ace_frontend_modal_edit', []);
    $paragraph_title = $this->getParagraphTitle($parent_entity_type, $parent_entity_bundle, $field);
    $paragraph_title .= " - " . $paragraph->getParagraphType()->label();
    if($js == "ajax") {
      $response = new AjaxResponse();
      $response->addCommand(new AceCloseModalDialogCommand('#drupal-off-canvas'));
      $response->addCommand(new AceOpenModalDialogCommand($this->t('Translating @paragraph_title', ['@paragraph_title' => $paragraph_title]), render($form)));
      return $response;
    } else {
      $form['#title'] = $this->t('Translating @paragraph_title', ['@paragraph_title' => $paragraph_title]);
      return $form;
    }
  }

  /**
   * Create a modal dialog to delete a single paragraph.
   */
  public function delete($parent_entity_type, $parent_entity_bundle, $parent_entity_revision, $field, $field_wrapper_id, $delta, $paragraph, $paragraph_revision, $js = 'nojs') {
    if ($js == 'ajax') {
      $options = [
        'dialogClass' => 'd324-ace-frontend-dialog',
        'width' => '20%',
      ];

      $form = $this->entityFormBuilder()->getForm($paragraph, 'd324_ace_frontend_modal_delete', []);

      $response = new AjaxResponse();
      //$paragraph_title = $this->getParagraphTitle($parent_entity_type, $parent_entity_bundle, $field);
      $paragraph_title = $paragraph->getParagraphType()->label();
      $response->addCommand(new AceCloseModalDialogCommand('#drupal-off-canvas'));
      $response->addCommand(new OpenModalDialogCommand($this->t('Delete @paragraph_title', ['@paragraph_title' => $paragraph_title]), render($form), $options));
      return $response;
    }

    return $this->t('Javascript is required for this functionality to work properly.');
  }

}
