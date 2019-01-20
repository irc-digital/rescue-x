<?php

namespace Drupal\ef_sitewide_settings\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\ef\EmbeddableTypeInterface;
use Drupal\ef_sitewide_settings\Entity\SitewideSettings;
use Drupal\ef_sitewide_settings\Entity\SitewideSettingsType;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Sitewide settings routes.
 */
class SitewideSettingsController extends ControllerBase implements ContainerInjectionInterface {
  /**
   * The sitewide setting storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $sitewideSettingsStorage;

  public function __construct(EntityStorageInterface $sitewideSettingsStorage) {
    $this->sitewideSettingsStorage = $sitewideSettingsStorage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')->getStorage('sitewide_settings')
    );
  }


  public function addSitewideSettings(SitewideSettingsType $sitewide_settings_type) {

    // check to see if we have am entity of this type already
    $query = $this->sitewideSettingsStorage->getQuery()->condition('type', $sitewide_settings_type->id(), '=' );
    $query_result = $query->execute();

    if (sizeof($query_result) > 0) {
      \Drupal::messenger()->addMessage($this->t('We only permit one instance of each site-wide settings type. You may edit the single %name instance below.', ['%name' => $sitewide_settings_type->label()]));
      return $this->redirect('entity.sitewide_settings.edit_form', ['sitewide_settings' => key($query_result)]);
    } else {
      $sitewide_settings = $this->sitewideSettingsStorage->create([
        'type' => $sitewide_settings_type->id(),
      ]);

      $form = $this->entityFormBuilder()->getForm($sitewide_settings);

      return $form;
    }

  }

  public function addPageTitle(SitewideSettingsType $sitewide_settings_type) {
    return $this->t('Create @name sitewide settings', ['@name' => $sitewide_settings_type->label()]);
  }

  public function editPageTitle(SitewideSettings $sitewide_settings) {
    return $this->t('Edit site-wide setting: @name', ['@name' => $sitewide_settings->type->entity->label()]);
  }

  public function deletePageTitle(SitewideSettings $sitewide_settings) {
    return $this->t('Delete site-wide setting: @name', ['@name' => $sitewide_settings->type->entity->label()]);
  }
}
