<?php

namespace Drupal\d324_ace\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Defines an AJAX command that closes the current active dialog.
 *
 * @ingroup ajax
 */
class AceReattachBehaviors implements CommandInterface {

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'aceReattachBehaviors',
    ];
  }

}
