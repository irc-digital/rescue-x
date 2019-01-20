<?php

namespace Drupal\ef_wysiwyg_embed\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides Entity Embed Display plugin definitions for the embeddable field formatter.
 *
 */
class EmbeddableFieldFormatterDeriver extends DeriverBase implements ContainerDeriverInterface {
  use StringTranslationTrait;

  /**
   * EmbeddableFieldFormatterDeriver constructor.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translation
   */
  public function __construct(TranslationInterface $translation) {
    $this->setStringTranslation($translation);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('string_translation')
    );
  }

  /**
   * {@inheritdoc}
   *
   * @throws \LogicException
   *   Throws an exception if field type is not defined in the annotation of the
   *   Entity Embed Display plugin.
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    // The field type must be defined in the annotation of the Entity Embed
    // Display plugin.
    if (!isset($base_plugin_definition['field_type'])) {
      throw new \LogicException("Undefined field_type definition in plugin {$base_plugin_definition['id']}.");
    }

    $this->derivatives['entity_reference_embeddable_view'] = $base_plugin_definition;
    $this->derivatives['entity_reference_embeddable_view']['label'] = $this->t('Embeddable');

    return $this->derivatives;
  }

}
