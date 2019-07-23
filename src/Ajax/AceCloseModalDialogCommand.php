<?php

namespace Drupal\d324_ace\Ajax;

use Drupal\Core\Ajax\CloseDialogCommand;

/**
 * Defines an AJAX command that closes the currently visible D324 Ace frontend modal.
 *
 * @ingroup ajax
 */
class AceCloseModalDialogCommand extends CloseDialogCommand {

  /**
   * Constructs a AceCloseModalDialogCommand object.
   *
   * @param bool $persist
   *   (optional) Whether to persist the dialog in the DOM or not.
   */
  public function __construct($selector = NULL, $persist = FALSE) {
    if(empty($selector)) {
      $this->selector = AceOpenModalDialogCommand::MODAL_SELECTOR;
    } else {
      $this->selector = $selector;
    }
    $this->persist = $persist;
  }

}
