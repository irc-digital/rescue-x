<?php

namespace Drupal\ef_mandatory_field_summary;

use Drupal\Core\Entity\EntityTypeManagerInterface;

class MandatoryFieldSummaryService implements MandatoryFieldSummaryServiceInterface {
  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  public function isSummaryRequired($entity_type_id, $bundle_id, $field_name) {
    $form_storage = $this->entityTypeManager->getStorage('entity_form_display');
    /** @var \Drupal\Core\Entity\Display\EntityFormDisplayInterface $form_display */
    $form_display = $form_storage->load($entity_type_id . '.' . $bundle_id . '.default');

    $form_field_components = $form_display->getComponent($field_name);

    return isset($form_field_components['third_party_settings']['ef_mandatory_field_summary']['textarea_summary_required']) ? $form_field_components['third_party_settings']['ef_mandatory_field_summary']['textarea_summary_required'] : FALSE;
  }

}