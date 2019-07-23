<?php

namespace Drupal\d324_ace_paragraph_behaviors\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\ParagraphsType;
use Drupal\d324_ace_paragraph_behaviors\StyleDiscoveryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The controller for overviews of D324 Ace Paragraph Behaviors's discoverable items.
 *
 * @see \Drupal\d324_ace_paragraph_behaviors\Plugin\paragraphs\Behavior\AceParagraphsStylesBehavior
 * @see \Drupal\d324_ace_paragraph_behaviors\StyleDiscoveryInterface
 */
class OverviewController extends ControllerBase {

  /**
   * The discovery service for style files.
   *
   * @var \Drupal\d324_ace_paragraph_behaviors\StyleDiscoveryInterface
   */
  protected $styleDiscovery;

  /**
   * A nested array of Paragraphs Type objects.
   *
   * A nested array. The first level is keyed by style machine names. The second
   * level is keyed Paragraphs Type IDs. The second-level values are Paragraphs
   * Type objects that allow the respective grid layout. Styles are ordered by
   * name.
   *
   * Example:
   * @code
   * [
   *   'blue_style' => [
   *     'style_pt' => $style_paragraphs_type_object,
   *   ]
   * ]
   * @endcode
   *
   * @var array
   */
  protected $paragraphsTypesGroupedByStyles;

  /**
   * Constructs a \Drupal\d324_ace_paragraph_behaviors\Controller\OverviewController object.
   *
   * @param \Drupal\d324_ace_paragraph_behaviors\StyleDiscoveryInterface $style_discovery
   *   The discovery service for style files.
   */
  public function __construct(StyleDiscoveryInterface $style_discovery) {
    $this->styleDiscovery = $style_discovery;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('d324_ace_paragraph_behaviors.style_discovery')
    );
  }

  /**
   * Lists styles with the Paragraphs Types that allow them.
   *
   * @return array
   *   A nested array. The first level is keyed by style machine names. The
   *   second level is keyed Paragraphs Type IDs. The second-level values are
   *   Paragraphs Type objects that allow the respective grid layout. Styles
   *   are ordered by name.
   *   Example:
   *   @code
   *   [
   *     'blue_style' => [
   *       'style_pt' => $style_paragraphs_type_object,
   *     ]
   *   ]
   *   @endcodeA
   */
  public function getParagraphsTypesGroupedByStyles() {
    if (isset($this->paragraphsTypesGroupedByStyles)) {
      return $this->paragraphsTypesGroupedByStyles;
    }

    $paragraph_type_ids = \Drupal::entityQuery('paragraphs_type')->execute();
    $paragraphs_types = ParagraphsType::loadMultiple($paragraph_type_ids);

    // Find the used style group for each Paragraphs Type.
    // An as empty string as the second-level value means that the Paragraphs
    // Type uses all style groups.
    $styles_grouped_by_paragraphs_types = [];
    foreach ($paragraphs_types as $paragraph_type_id => $paragraphs_type) {
      /** @var ParagraphsType $paragraphs_type */
      $configuration = $paragraphs_type->getBehaviorPlugin('ace_paragraphs_styles')->getConfiguration();
      if (isset($configuration['enabled']) && $configuration['enabled']) {
        $styles_grouped_by_paragraphs_types[$paragraph_type_id] = array_keys($configuration['groups']);
      }
    }

    //Get all styles ordered by title.
    $styles = $this->styleDiscovery->getStyles();
    uasort($styles, function ($style1, $style2) {
      return strcasecmp($style1['title'], $style2['title']);
    });

    // Group Paragraphs Types by styles.
    $paragraphs_types_grouped_by_styles = [];
    foreach ($styles as $style_id => $style) {
      $paragraphs_types_grouped_by_styles[$style_id] = [];
      foreach ($styles_grouped_by_paragraphs_types as $paragraphs_type_id => $used_style_groups) {
        $enabled_styles = [];
        foreach ($used_style_groups as $used_style_group) {
          $enabled_styles += $this->styleDiscovery->getStyleOptions($used_style_group);
        }
        if (in_array($style_id, array_keys($enabled_styles))) {
          $paragraphs_types_grouped_by_styles[$style_id][$paragraphs_type_id] = $paragraphs_types[$paragraphs_type_id];
        }
      }
    }

    return $this->paragraphsTypesGroupedByStyles = $paragraphs_types_grouped_by_styles;
  }

  /**
   * Generates an overview page of available styles for the ace_paragraphs_styles plugin.
   *
   * @return array
   *   The output render array.
   */
  public function styles() {
    $grouped_styles = $this->getParagraphsTypesGroupedByStyles();
    return $this->formBuilder()->getForm('Drupal\d324_ace_paragraph_behaviors\Form\AceStylesOverviewForm', $grouped_styles);
  }

}
