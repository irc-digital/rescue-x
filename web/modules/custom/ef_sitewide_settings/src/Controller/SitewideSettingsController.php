<?php

namespace Drupal\ef_sitewide_settings\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\ef\EmbeddableTypeInterface;
use Drupal\ef_sitewide_settings\Entity\SitewideSettings;
use Drupal\ef_sitewide_settings\Entity\SitewideSettingsType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Sitewide settings routes.
 */
class SitewideSettingsController extends ControllerBase implements ContainerInjectionInterface {

  public function __construct() {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
    );
  }


  public function addSitewideSettings(SitewideSettingsType $sitewide_settings_type) {
    $sitewide_settings = $this->entityTypeManager()->getStorage('sitewide_settings')->create([
      'type' => $sitewide_settings_type->id(),
    ]);

    $form = $this->entityFormBuilder()->getForm($sitewide_settings);

    return $form;
  }

  public function addPageTitle(SitewideSettingsType $sitewide_settings_type) {
    return $this->t('Create @name sitewide settings', ['@name' => $sitewide_settings_type->label()]);
  }
  public function editPageTitle(SitewideSettings $sitewide_settings) {
    return $this->t('Edit site-wide setting: @name', ['@name' => $sitewide_settings->type->entity->label()]);
  }
}
