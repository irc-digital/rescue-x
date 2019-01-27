<?php

namespace Drupal\ef_reach_through_content;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\ef_reach_through_content\Entity\ReachThrough;
use Drupal\node\NodeInterface;

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

  /**
   * Called when a node is inserted
   */
  public function onInsert(NodeInterface $parent_entity);

  /**
   * Called when a node is updated
   *
   * @param \Drupal\node\NodeInterface $parent_entity
   * @return mixedC
   */
  public function onUpdate(NodeInterface $parent_entity);

  /**
   * Called when a node is being deleted
   *
   * @param \Drupal\node\NodeInterface $entity
   * @return mixed
   */
  public function onDelete(NodeInterface $entity);


  /**
   * Called when a node translation is being deleted
   *
   * @param \Drupal\node\NodeInterface $entity
   * @return mixed
   */
  public function onTranslationDelete(NodeInterface $entity);

  /**
   * Called when a reach through entity is about to be saved
   *
   * @param \Drupal\ef_reach_through_content\Entity\ReachThrough $reachThrough
   * @return mixed
   */
  public function onPresaveReachThrough (ReachThrough $reachThrough);

  /**
   * Returns the reach-through entity for the supplied node
   *
   * @param \Drupal\node\NodeInterface $node
   * @return mixed
   */
  public function getReachThroughEntityForNode (NodeInterface $node, $reach_though_bundle_id);

  /**
   * Modify the reach-through entity add or edit form to (mostly) account for
   * whether a field need to be marked as required
   *
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param $form_id
   * @return mixed
   */
  public function alterReachThroughAddEditForm (&$form, FormStateInterface $form_state, $form_id);
}