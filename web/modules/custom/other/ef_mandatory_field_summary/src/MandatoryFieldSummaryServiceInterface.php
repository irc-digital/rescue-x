<?php

namespace Drupal\ef_mandatory_field_summary;

interface MandatoryFieldSummaryServiceInterface {
  /**
   * Returns true if the summary field has been marked as required on the entity/bundle/field combo
   *
   * @param $entity_type_id
   * @param $bundle_id
   * @param $field_name
   * @return mixed
   */
  public function isSummaryRequired ($entity_type_id, $bundle_id, $field_name);
}