<?php

namespace Drupal\d324_ace_paragraph_behaviors;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines a class containing permission callbacks.
 */
class Permissions {

  use StringTranslationTrait;

  /**
   * Returns an array of permissions for advanced styles.
   *
   * @return array
   */
  public function permissions() {
    $permissions = [];
    /** @var \Drupal\d324_ace_paragraph_behaviors\StyleDiscoveryInterface $style_discovery */
    $style_discovery = \Drupal::service('d324_ace_paragraph_behaviors.style_discovery');

    // Generate permissions for advanced behavior styles.
    foreach ($style_discovery->getStyles() as $style) {
      if (isset($style['permission']) && $style['permission'] === TRUE) {
        $permissions['use ' . $style['name'] . ' style'] = [
          'title' => $this->t('Use %style style', ['%style' => $style['title']]),
          'description' => $this->t('Users with this permission can use %style behavior style.', ['%style' => $style['title']]),
        ];
      }
    }

    return $permissions;
  }

}
