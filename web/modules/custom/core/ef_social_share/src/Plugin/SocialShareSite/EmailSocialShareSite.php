<?php

namespace Drupal\ef_social_share\Plugin\SocialShareSite;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Utility\Token;
use Drupal\ef_icon_library\IconLibraryInterface;
use Drupal\ef_social_share\Annotation\SocialShareSite;
use Drupal\ef_social_share\SocialShareSiteBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The email 'social share site' plugin
 *
 * @SocialShareSite(
 *   id = "email_social_share_site",
 *   label = @Translation("Email"),
 * )
 */
class EmailSocialShareSite extends SocialShareSiteBase implements ContainerFactoryPluginInterface {

  public function __construct(array $configuration, $plugin_id, $plugin_definition, IconLibraryInterface $icon_library, Token $token_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $icon_library, $token_service);
  }

  /**
   * @inheritdoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('ef.icon_library'),
      $container->get('token')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
        'email_subject' => '[node:custom-social-share-title]',
        'email_body' => '',
      ] + parent::defaultConfiguration();
  }

  /**
   * @inheritdoc
   */
  public function getLink(array $context = []) {
    $subject = $this->configuration['email_subject'];
    $body = $this->configuration['email_body'];

    $subject = $this->tokenService->replace($subject, $context);
    $body = $this->tokenService->replace($body, $context);

    return "mailto:?subject=" . rawurlencode($subject) . '&body=' . rawurlencode($body);
  }

  public function shouldOpenInPopup () {
    return FALSE;
  }

  /**
   * @inheritdoc
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['email_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subject'),
      '#description' => $this->t('This will be the default subject of the email.'),
      '#default_value' => $this->configuration['email_subject'],
    ];

    $form['email_body'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Body'),
      '#description' => $this->t('This will be the default body of the email.'),
      '#default_value' => $this->configuration['email_body'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['email_subject'] = $form_state->getValue('email_subject');
    $this->configuration['email_body'] = $form_state->getValue('email_body');
    parent::submitConfigurationForm($form, $form_state);
  }

}
