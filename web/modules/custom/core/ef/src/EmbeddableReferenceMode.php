<?php

namespace Drupal\ef;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Implementation of the EmbeddableReferenceModeInterface interface
 * @package Drupal\ef
 */
class EmbeddableReferenceMode implements EmbeddableReferenceModeInterface {
  use StringTranslationTrait;

  public function __construct(TranslationInterface $translation) {
    $this->setStringTranslation($translation);
  }

  /**
   * @inheritdoc
   */
  public function getModes() {
    return [
      EmbeddableReferenceModeInterface::ENABLED => $this->t('Enabled'),
      EmbeddableReferenceModeInterface::TEST => $this->t('Test'),
      EmbeddableReferenceModeInterface::DISABLED => $this->t('Disabled'),
    ];
  }

  /**
   * @inheritdoc
   */
  public function getDefaultMode() {
    return EmbeddableReferenceModeInterface::ENABLED;
  }

  /**
   * @inheritdoc
   */
  public function getAccess ($mode) {
    return $mode == EmbeddableReferenceModeInterface::ENABLED || ($mode == EmbeddableReferenceModeInterface::TEST && \Drupal::currentUser()->hasPermission(EmbeddableReferenceModeInterface::TEST_MODE_PERMISSION_NAME));
  }

}