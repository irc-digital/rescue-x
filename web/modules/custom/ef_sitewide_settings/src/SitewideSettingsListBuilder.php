<?php

namespace Drupal\ef_sitewide_settings;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, DateFormatterInterface $date_formatter, RedirectDestinationInterface $redirect_destination, LanguageManagerInterface $languageManager) {
    parent::__construct($entity_type, $storage);
    $this->dateFormatter = $date_formatter;
    $this->redirectDestination = $redirect_destination;
    $this->languageManager = $languageManager;
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
      $container->get('language_manager')
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

    $entity = $entity->getTranslation($activeLanguageCode);

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

  /**
   * Loads entity IDs using a pager sorted by the entity id.
   *
   * @return array
   *   An array of entity IDs.
   */
  protected function getEntityIds() {

    $user = \Drupal::currentUser();

    $is_admin = $user->hasPermission('administer sitewide settings');

    $query = $this->getStorage()->getQuery()
      ->sort($this->entityType->getKey('id'));

//    if (!$is_admin) {
//      // only list the sitewide settings that the editor is permitted to edit
//      $query->condition();
//    }

    return $query->execute();
  }

}
