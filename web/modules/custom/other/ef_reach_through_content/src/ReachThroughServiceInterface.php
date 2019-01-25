<?php

namespace Drupal\ef_reach_through_content;

use Drupal\Core\Form\FormStateInterface;

interface ReachThroughServiceInterface {
  /**
   * Return an array of the fields that can be mapped on the supplied reach-through entity bundle
   *
   * @return mixed
   */
  public function geReachThroughFields ($reach_through_bundle);

  /**
   * Modifies the provided node form to add the  reach-through options
   *
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @return mixed
   */
  public function alterNodeForm (&$form, FormStateInterface $form_state);
}