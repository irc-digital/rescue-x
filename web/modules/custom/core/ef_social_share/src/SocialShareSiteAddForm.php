<?php

namespace Drupal\ef_social_share;

use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for social share sites add forms.
 *
 * @internal
 */
class SocialShareSiteAddForm extends SocialShareSiteFormBase {

  /**
   * {@inheritdoc}
   *
   * @param string $social_share_site_id
   *   The social share site ID.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $social_share_site_id = NULL) {
    $this->entity->setPlugin($social_share_site_id);

    // Derive the label and type from the action definition.
    $definition = $this->entity->getPluginDefinition();
    $this->entity->set('label', $definition['label']);

    return parent::buildForm($form, $form_state);
  }

}
