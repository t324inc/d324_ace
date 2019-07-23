<?php

namespace Drupal\d324_ace\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Defines an AJAX command that scrolls the window to a given paragraph
 *
 * @ingroup ajax
 */
class AceScrollToParagraph implements CommandInterface {

  /**
   * The id of the target paragraph
   *
   * @var integer
   */
  protected $paragraph_id;

  /**
   * Constructs an AceScrollToParagraph object.
   *
   * @param integer $paragraph_id
   *   The paragraph id to scroll to
   */
  public function __construct($paragraph_id) {
    $this->paragraph_id = $paragraph_id;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'aceScrollToParagraph',
      'paragraph_id' => $this->paragraph_id,
    ];
  }

}
