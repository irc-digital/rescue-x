<?php

namespace Drupal\ef_reach_through_content\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * No not permit a reach-through entity to be created if their is already another
 * with a matching reach_through_ref (i.e. that points to the same node)
 *
 * @Constraint(
 *   id = "no_duplicate_reach_through_reference_constraint",
 *   label = @Translation("Ensures that no reach through bundle can end up with duplicate wrapped nodes", context="Validation"),
 *   type = "entity"
 * )
 */
class NoDuplicateReachThroughReferenceConstraint extends Constraint {
  /**
   * Message shown when validation fails.
   *
   * @var string
   */
  public $message = 'The content is already being wrapped.';
}