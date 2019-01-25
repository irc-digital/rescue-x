<?php

namespace Drupal\ef_reach_through_content;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\ef_reach_through_content\ReachThroughServiceInterface;

class ReachThroughService implements ReachThroughServiceInterface {

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityFieldManager;

  public function __construct(EntityFieldManagerInterface $entityFieldManager) {
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * @inheritdoc
   */
  public function geReachThroughFields($reach_through_bundle) {
    $mappable_fields = [];

    $fields = $this->entityFieldManager->getFieldDefinitions('reach_through', $reach_through_bundle);

    /** @var \Drupal\Core\Field\FieldDefinitionInterface $field */
    foreach ($fields as $field) {
      if ($field instanceof FieldConfigInterface) {
        $mappable_fields[$field->getName()] = $field->label();
      }
    }

    return $mappable_fields;
  }

}