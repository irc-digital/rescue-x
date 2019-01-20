<?php

namespace Drupal\ef_sitewide_settings;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\ef_sitewide_settings\Entity\SitewideSettingsType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Provides a list controller for the site-wide settings
 */
class SitewideSettingsListBuilder extends EntityListBuilder {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The redirect destination service.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirectDestination;

  /** @var \Drupal\Core\Language\LanguageManagerInterface */
  protected $languageManager;

  /** @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface */
  var $entityTypeBundleInfo;

  /**
   * Constructs a new SitewideSettingsListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirect_destination
   *   The redirect destination service.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, DateFormatterInterface $date_formatter, RedirectDestinationInterface $redirect_destination, LanguageManagerInterface $languageManager, EntityTypeBundleInfoInterface $entityTypeBundleInfo) {
    parent::__construct($entity_type, $storage);
    $this->dateFormatter = $date_formatter;
    $this->redirectDestination = $redirect_destination;
    $this->languageManager = $languageManager;
    $this->entityTypeBundleInfo = $entityTypeBundleInfo;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('date.formatter'),
      $container->get('redirect.destination'),
      $container->get('language_manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $build['table'] = parent::render();

    $build['table']['table']['#empty'] = $this->t('There are no site-wide settings.');

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {

    $header['type'] = [
      'data' => $this->t('Title'),
    ];


    if (\Drupal::languageManager()->isMultilingual()) {
      $header['language_name'] = [
        'data' => $this->t('Language'),
      ];
    }

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $activeLanguageCode = $this->languageManager->getCurrentLanguage()->getId();

    if ($entity instanceof ContentEntityInterface) {
      /** @var ContentEntityInterface $content_entity */
      $content_entity = $entity;
      if ($content_entity->hasTranslation($activeLanguageCode)) {
        $entity = $entity->getTranslation($activeLanguageCode);
      }
    }

    $langcode = $entity->language()->getId();

    $row['type'] = $entity->label();

    $language_manager = \Drupal::languageManager();
    if ($language_manager->isMultilingual()) {
      $row['language_name'] = $language_manager->getLanguageName($langcode);
    }

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    $destination = $this->redirectDestination->getAsArray();
    foreach ($operations as $key => $operation) {
      $operations[$key]['query'] = $destination;
    }
    return $operations;
  }

  public function getOperations(EntityInterface $entity) {
    if ($entity instanceof ContentEntityInterface) {
      return parent::getOperations($entity);
    } else {
      $operations = [];
      if (\Drupal::currentUser()->hasPermission('administer sitewide settings')) {
        $operations['add'] = [
          'title' => $this->t('Add'),
          'weight' => 10,
          'url' => $this->ensureDestination(Url::fromRoute('entity.sitewide_settings.add', ['sitewide_settings_type' => $entity->id()])),
        ];
      }

      uasort($operations, '\Drupal\Component\Utility\SortArray::sortByWeightElement');

      return $operations;
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $sitewide_settings_types = $this->entityTypeBundleInfo->getBundleInfo('sitewide_settings');

    /** @var \Drupal\user\UserInterface $user */
    $user = \Drupal::currentUser();

    $is_admin = $user->hasPermission('administer sitewide settings');

    $entities = [];

    foreach ($sitewide_settings_types as $sitewide_settings_type_key => $sitewide_settings_type_info) {
      if ($is_admin || $user->hasPermission(sprintf("edit %s sitewide settings", $sitewide_settings_type_key))) {
        $query = $this->getStorage()->getQuery()->condition('type', $sitewide_settings_type_key);
        $entity_id = $query->execute();

        if (sizeof($entity_id) > 0) {
          $entities[] = $this->storage->load(key($entity_id));
        } else {
          // no setting created for this yet
          $entities[] = SitewideSettingsType::load($sitewide_settings_type_key);
        }
      };
    }

    return $entities;
  }


}
