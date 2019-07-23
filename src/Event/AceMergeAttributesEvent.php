<?php

namespace Drupal\d324_ace\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event that is fired when ace attributes are merged into layout_options.
 */
class AceMergeAttributesEvent extends Event {

  const EVENT_NAME = 'ace_merge_attributes';

  public $attributes;
  public $formValues;

  /**
   * Constructs the object.
   *
   * @param array $attributes
   *   Attributes array being built.
   * @param array $form_values
   *   Form values to merge into attributes.
   */
  public function __construct(array &$attributes, array $form_values) {
    $this->attributes =& $attributes;
    $this->formValues = $form_values;
  }

}
