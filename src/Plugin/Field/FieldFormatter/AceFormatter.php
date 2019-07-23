<?php

namespace Drupal\d324_ace\Plugin\Field\FieldFormatter;

use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsEntityFormatter;

/**
 * D324 Ace field formatter.
 *
 * Currently stub only. Content is formatted in
 * module theme functions.
 *
 * @todo: Move formatter functionality out
 *  of module into field formatter class.
 *
 * @FieldFormatter(
 *   id = "d324_ace",
 *   label = @Translation("D324 Ace"),
 *   description = @Translation("Display the referenced entities recursively rendered by entity_view()."),
 *   field_types = {
 *     "d324_ace",
 *     "d324_ace_revisioned"
 *   }
 * )
 */
class AceFormatter extends EntityReferenceRevisionsEntityFormatter {
}
