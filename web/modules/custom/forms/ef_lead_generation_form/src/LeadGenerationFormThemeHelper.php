<?php

namespace Drupal\ef_lead_generation_form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\Element;
use Drupal\ef_sitewide_settings\SitewideSettingsManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LeadGenerationFormThemeHelper implements ContainerInjectionInterface {

  /**
   * @var \Drupal\ef_sitewide_settings\SitewideSettingsManagerInterface
   */
  protected $sitewideSettingsManager;

  /**
   * @var LanguageManagerInterface
   */
  protected $languageManager;

  public function __construct(SitewideSettingsManagerInterface $sitewideSettingsManager, LanguageManagerInterface $languageManager) {
    $this->sitewideSettingsManager = $sitewideSettingsManager;
    $this->languageManager = $languageManager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('ef_sitewide_settings.manager'),
      $container->get('language_manager')
    );
  }

  /**
   * Preprocess the simple lead generation form (aka footer lead generation)
   *
   * This is the EF theme element - we basically look at config to discover
   * what webform has been used in that section and render that - it is preprocessed below
   *
   * @param $variables
   */
  public function preprocessSimpleLeadGenerationForm ($name, &$variables) {
    $lead_generation_info = $this->getFooterLeadGenerationInfo();

    if (!is_null($lead_generation_info)) {
      $variables[$name] = [
        '#type' => 'webform',
        '#webform' => $lead_generation_info['webform_id'],
      ];
    }
  }

  /**
   * Process the webform that is rendered as part of the simple lead generation form
   *
   * @param $variables
   */
  public function preprocessSimpleHelpSignUpWebform (&$variables) {
    unset ($variables['form']['elements']);

    $email_signup_form_hidden_field = [];

    foreach (Element::children($variables['form']) as $key) {
      $email_signup_form_hidden_field[$key] = $variables['form'][$key];
      unset($variables['form'][$key]);
    }

    $lead_generation_info = $this->getFooterLeadGenerationInfo();

    if (!is_null($lead_generation_info)) {
      $variables['form']['elements'][$lead_generation_info['webform_id']] = [
        '#type' => "pattern",
        '#id' => 'email_signup',
        '#fields' => [
          'email_signup_form_hidden_fields' => $email_signup_form_hidden_field,
          'email_signup_title' => $lead_generation_info['title'],
          'email_signup_email_field_placeholder' => $lead_generation_info['placeholder_text'],
          'email_signup_email_subscribe_button_text' => $lead_generation_info['submit_link_text'],
        ],
      ];
    }
  }


  protected function getFooterLeadGenerationInfo () {
    $result = NULL;

    /** @var \Drupal\ef_sitewide_settings\SitewideSettingsInterface $donation_settings */
    $footer_lead_generation = $this->sitewideSettingsManager->getSitewideSettingsForType('footer_lead_generation');

    if ($footer_lead_generation) {
      $active_language = $this->languageManager->getCurrentLanguage()->getId();

      if ($footer_lead_generation->hasTranslation($active_language)) {
        $footer_lead_generation = $footer_lead_generation->getTranslation($active_language);

        /** @var \Drupal\webform\Entity\Webform $webform_type */
        $webform_type = $footer_lead_generation->field_flg_form->entity;

        if (!is_null($webform_type)) {
          $result['webform_id'] = $webform_type->id();
          $result['placeholder_text'] = $footer_lead_generation->field_flg_placeholder_text->value;
          $result['title'] = $footer_lead_generation->field_flg_title->value;
          $result['submit_link_text'] = $footer_lead_generation->field_flg_submit_link->value;
        }
      }

      return $result;
    }
  }

}