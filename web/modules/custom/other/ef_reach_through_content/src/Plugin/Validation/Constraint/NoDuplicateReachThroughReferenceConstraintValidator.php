<?php

namespace Drupal\ef_reach_through_content\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NoDuplicateReachThroughReferenceConstraintValidator extends ConstraintValidator {
  /**
   * {@inheritdoc}
   */
  public function validate($entity, Constraint $constraint) {
    if (!isset($entity)) {
      return;
    }
    // If the entity already has an id we're in an entity *update* operation
    // instead of an entity *creation* operation.
    if ($entity->id()) {
      return;
    }

    $count = \Drupal::entityQuery('reach_through')
      ->condition('type', $entity->bundle())
      ->condition('reach_through_ref', $entity->reach_through_ref->entity->id(), '=')
      ->count()
      ->execute();

    if ($count >= 1) {
      $this->context->addViolation($constraint->message);
    }
  }
}