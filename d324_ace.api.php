<?php
/**
 * @file
 * Hooks provided by the Geysir module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Modify D324 Ace Frontend links.
 *
 * @param array $links
 *   D324 Ace Frontend links.
 * @param array $context
 *   Context of links.
 *   - paragraph (\Drupal\paragraphs\ParagraphInterface)
 *     Displayed paragraph item.
 *   - parent (\Drupal\Core\Entity\FieldableEntityInterface)
 *     Parent entity.
 *   - delta (int)
 *     Delta of field.
 *   - field_definition (\Drupal\Core\Field\FieldDefinition)
 *     The field definition.
 */
function hook_d324_ace_paragraph_links_alter(&$links, $context) {
  // You can add custom actions here.
  $links['up'] = [
    // Move up links.
  ];
  $links['down'] = [
    // Move down link.
  ];
}

/**
 * @} End of "addtogroup hooks".
 */
