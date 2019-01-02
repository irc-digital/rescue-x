<?php

namespace Drupal\ef_patterns\Plugin\DsField;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\LinkGeneratorInterface;
use Drupal\ds\Plugin\DsField\DsFieldBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a link field.
 *
 * @DsField(
 *   id = "link_attributes_field",
 *   deriver = "Drupal\ef_patterns\Plugin\Derivative\LinkAttributesField"
 * )
 */
class LinkAttributesField extends DsFieldBase {

  /**
   * The EntityDisplayRepository service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The link generator.
   *
   * @var \Drupal\Core\Utility\LinkGeneratorInterface
   */
  protected $linkGenerator;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs a Display Suite field plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, LinkGeneratorInterface $link_generator, AccountInterface $current_user) {
    $this->entityTypeManager = $entity_type_manager;
    $this->linkGenerator = $link_generator;
    $this->currentUser = $current_user;

    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.manager'),
      $container->get('link_generator'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    $entity = $this->entity();
    $link_field_name = $config['field']['field_name'];

    $output = '';

    switch ($config['field']['field_attribute']) {
      case 'link_text':
        $output = $entity->{$link_field_name}->title;
        break;
      case 'url':
        $output = $entity->{$link_field_name}->uri;
        break;
    }

    return [
      '#markup' => $output,
      '#ef_ds_custom_field_element' => TRUE,
    ];
  }

}
