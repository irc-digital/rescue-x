<?php

namespace Drupal\ef;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\ef\Entity\EmbeddableType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic permissions of different embeddable types.
 *
 * @see ef.permissions.yml
 */
class EmbeddablePermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  public function __construct() {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static();
  }

  /**
   * Get embeddable permissions.
   *
   * @return array
   *   Permissions array.
   */
  public function embeddableTypePermissions() {
    $permissions = [];
    foreach (EmbeddableType::loadMultiple() as $embeddableType) {
      $permissions += $this->buildPermissions($embeddableType);
    }
    return $permissions;
  }

  /**
   * Builds a standard list of embeddable permissions for a given embeddable type.
   *
   * @param \Drupal\ef\EmbeddableTypeInterface $embeddableType
   *   The embeddable type.
   *
   * @return array
   *   An array of permission names and descriptions.
   */
  protected function buildPermissions(EmbeddableTypeInterface $embeddableType) {
    $id = $embeddableType->id();
    $args = ['%embeddableType' => $embeddableType->label()];

    return [
      "create $id embeddable content" => ['title' => $this->t('%embeddableType: Create new embeddable content', $args)],
      "delete $id embeddable content" => ['title' => $this->t('%embeddableType: Delete embeddable content', $args)],
      "edit $id embeddable content" => ['title' => $this->t('%embeddableType: Edit embeddable content', $args)],
    ];
  }

}
