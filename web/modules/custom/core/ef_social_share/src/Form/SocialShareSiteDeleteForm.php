<?php

namespace Drupal\ef_social_share\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Url;

/**
 * Builds a form to delete a social share site.
 */
class SocialShareSiteDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.social_share_site.collection');
  }

}
