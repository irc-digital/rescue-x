<?php

namespace Drupal\ef_reach_through_content;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
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

  /**
   * Render the reach through entity
   *
   * @param array $build
   * @param \Drupal\ef_reach_through_content\EntityInterface $entity
   * @param \Drupal\ef_reach_through_content\EntityViewDisplayInterface $display
   * @param $view_mode
   * @return mixed
   */
  public function viewReachThroughEntity (array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode);

  public function getReachThoughtFieldMappings (EntityInterface $entity);
}