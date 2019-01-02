<?php

namespace Drupal\ef_person\Plugin\DsField;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a image field.
 *
 * @DsField(
 *   id = "contributors_field",
 *   deriver = "Drupal\ef_person\Plugin\Derivative\ContributorsField"
 * )
 */
class ContributorsField extends DsFieldBase {

  /**
   * Constructs a Display Suite field plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $entity = $this->entity();

    $contributors = [];

    $by = "by"; // translated in the Twig template
    $and = "and"; // translated in the Twig template
    $prefix = $by;

    $image_parts = [
      'srcset' => 'contributor_responsive_image_source_set',
      'sizes' => 'contributor_responsive_image_sizes',
      'fallback_uri' => 'contributor_responsive_image_fallback',
      'alt' => 'contributor_responsive_image_alt',
    ];

    foreach ($entity->field_contributors as $field_contributors) {
      $person = $field_contributors->entity;

      $image = $person->field_person_headshot_photo->entity->field_ef_image->entity;

      $contributor_headshot_content = [];

      if ($image) {
        $image_uri = $image->uri->value;

        foreach ($image_parts as $image_part => $contributor_pattern_field) {
          $image_part_value = ef_patterns_get_responsive_image_element ('contributor_facial', $image_part, $image_uri);

          if ($image_part_value) {
            $contributor_headshot_content[$contributor_pattern_field] = $image_part_value;
          }
        }
      }

      $contributors[] = [
        '#type' => 'pattern',
        '#id' => 'contributor',
        '#fields' => [
          'contributor_prefix' => $prefix . " ",
          'contributor_name' => $person->title->value,
          'contributor_title' => $person->field_person_role->value,
        ] + $contributor_headshot_content,
      ];

      $prefix = $and;
    }

    if (count($contributors) > 0) {
      $output = [
        '#type' => 'pattern',
        '#id' => 'contributors',
        '#fields' => [
          'contributors_list' => $contributors,
        ]
      ];
    } else {
      $output = [];
    }

    return $output;
  }

}
