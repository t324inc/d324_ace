<?php

namespace Drupal\d324_ace\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\d324_ace\Ajax\AceReattachBehaviors;

/**
 * Controller for cut & paste functionality.
 */
class AceCutPasteController extends AceControllerBase {

  /**
   * Cut a paragraph entity.
   */
  public function cut($parent_entity_type, $parent_entity_bundle, $parent_entity_revision, $field, $field_wrapper_id, $delta, $paragraph_to_cut, $paragraph_revision, $js = 'nojs') {

    if ($js == 'ajax') {
      // Get the parent revision if available, otherwise the parent.
      $parent_entity_revision = $this->getParentRevisionOrParent($parent_entity_type, $parent_entity_revision);

      $parent_entity_revision->get($field)->removeItem($delta);

      $response = new AjaxResponse();
      $response->addCommand(
        new ReplaceCommand(
          sprintf('[data-ace-field-wrapper="%s"]', $field_wrapper_id),
          $parent_entity_revision->get($field)->view('default')
        )
      );
      return $response;
    }

    return $this->t('Javascript is required for this functionality to work properly.');
  }

  /**
   * Paste a paragraph entity before/after another paragraph on the given delta.
   */
  public function paste($parent_entity_type, $parent_entity_bundle, $parent_entity_revision, $field, $field_wrapper_id, $delta, $position, $paragraph_to_paste, $paragraph_revision, $layout_paragraph, $layout_region, $js = 'nojs') {
    if ($js == 'ajax') {
      // Get the parent revision if available, otherwise the parent.
      $parent_entity_revision = $this->getParentRevisionOrParent($parent_entity_type, $parent_entity_revision);

      $list_items = $parent_entity_revision->get($field)->getIterator();

      for ($index = $parent_entity_revision->get($field)->count() - 1; $index >= 0; $index--) {
          $parent_entity_revision->get($field)->removeItem($index);
      }
      foreach ($list_items as $item_delta => $item) {
        $item_value = $item->getValue();

        // Prevent the "paragraph to paste" being added again.
        $prevent_adding_twice =
          ($paragraph_to_paste->id() !== $item_value['target_id']) ? true : false;

        if ($item_delta == $delta) {
          $paste_value = [
            'target_id' => $paragraph_to_paste->id(),
            'target_revision_id' => $paragraph_to_paste->getRevisionId(),
            'section_id' => $layout_paragraph->id(),
            'region' => $layout_region,
          ];

          if ($position == 'before') {
            $parent_entity_revision->get($field)->appendItem($paste_value);
            if ($prevent_adding_twice) {
              $parent_entity_revision->get($field)->appendItem($item_value);
            }
          }
          else {
            if ($prevent_adding_twice) {
              $parent_entity_revision->get($field)->appendItem($item_value);
            }
            $parent_entity_revision->get($field)->appendItem($paste_value);
          }
        }
        else {
          if ($prevent_adding_twice) {
            $parent_entity_revision->get($field)->appendItem($item_value);
          }
        }
      }

      $parent_entity_revision->save();

      $response = new AjaxResponse();
      $response->addCommand(
        new ReplaceCommand(
          sprintf('[data-ace-field-wrapper="%s"]', $field_wrapper_id),
          $parent_entity_revision->get($field)->view('default')
        )
      );

      $response->addCommand(new AceReattachBehaviors());

      return $response;
    }

    return $this->t('Javascript is required for this functionality to work properly.');
  }

}
