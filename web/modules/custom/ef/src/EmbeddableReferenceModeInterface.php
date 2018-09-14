<?php


namespace Drupal\ef;

/**
 * Interface EmbeddableReferenceModeInterface
 *
 * Defines the modes that an embedded embeddable can be in. This is the the
 * published status of the embeddable, but is rather used when an editor is
 * inserting an embeddable in a piece of content and, potentially, wants to
 * hide it so they can finish it up later.
 *
 * @package Drupal\ef
 */
interface EmbeddableReferenceModeInterface {
  /** Indicates that the embedded object should be displayed */
  const ENABLED = 'enabled';

  /** Indicates that the embedded object should be only to authorized users */
  const TEST = 'test';

  /** Indicates that the embedded object should not be displayed to anyone */
  const DISABLED = 'disabled';

  /** The permission name for roles that can view embeddables when in test mode */
  const TEST_MODE_PERMISSION_NAME = 'view embeddable content in test mode';

  /**
   * Retrieve the embeddable reference modes
   *
   * @return array associative array
   */
  public function getModes ();

  /**
   * @return string the default mode. This will be either EmbeddableReferenceModeInterface::ENABLED,
   *  EmbeddableReferenceModeInterface::TEST or EmbeddableReferenceModeInterface::DISABLED
   */
  public function getDefaultMode ();

  /**
   * Returns a true/false as to whether access should be given to the provided
   * mode.
   *
   * If the mode is EmbeddableReferenceModeInterface::ENABLED then the access
   * will be true. If the mode is EmbeddableReferenceModeInterface::DISABLED then
   * the access will be false. If the mode is EmbeddableReferenceModeInterface::TEST
   * the access will be determined by whether the user has the permission
   * EmbeddableReferenceModeInterface::TEST_MODE_PERMISSION_NAME
   *
   * @param $mode
   * @return boolean
   */
  public function getAccess ($mode);
}