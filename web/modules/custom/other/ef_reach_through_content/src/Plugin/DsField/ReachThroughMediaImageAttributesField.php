<?php

namespace Drupal\ef_reach_through_content\Plugin\DsField;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\ef_patterns\Plugin\DsField\MediaImageAttributesField;
use Drupal\ef_reach_through_content\ReachThroughServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a DS field that knows how to reach through the entity reference to
 * get image information
 *
 * @DsField(
 *   id = "reach_through_media_image_attributes_field",
 *   deriver = "Drupal\ef_reach_through_content\Plugin\Derivative\ReachThroughMediaImageAttributesField"
 * )
 */
class ReachThroughMediaImageAttributesField extends MediaImageAttributesField {
  /** @var \Drupal\ef_reach_through_content\ReachThroughServiceInterface */
  protected $reachThroughService;

  /** @var LanguageManagerInterface */
  protected $languageManager;
  /**
   * Constructs a Display Suite field plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, LinkGeneratorInterface $link_generator, AccountInterface $current_user, ReachThroughServiceInterface $reachThroughService, LanguageManagerInterface $language_manager) {
    $this->reachThroughService = $reachThroughService;
    $this->languageManager = $language_manager;
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $link_generator, $current_user);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager'),
      $container->get('link_generator'),
      $container->get('current_user'),
      $container->get('ef.reach_through_service'),
      $container->get('language_manager')
    );
  }

  protected function getEntityWithImageField() {

    $reach_through_entity = $this->entity();

    $config = $this->getConfiguration();
    $field_name = $config['field']['field_name'];

    if (isset($reach_through_entity->{$field_name}->entity)) {
      // overridden image on the curated wrapper
      $media_image_entity = $reach_through_entity->{$field_name}->entity;
    } else {
      // use image on underlying node
      /** @var \Drupal\node\NodeInterface $outer_entity */
      $outer_entity = $reach_through_entity->reach_through_ref->entity;

      $current_language_code = $this->languageManager->getCurrentLanguage()->getId();

      if ($outer_entity->hasTranslation($current_language_code)) {
        $outer_entity = $outer_entity->getTranslation($current_language_code);
      }

      $reach_through_fields = $this->reachThroughService->getReachThoughtFieldMappings($reach_through_entity);
      $outer_field_name = $reach_through_fields[$field_name];
      $media_image_entity = $outer_entity->{$outer_field_name}->entity;
    }

    return $media_image_entity;
  }
}
