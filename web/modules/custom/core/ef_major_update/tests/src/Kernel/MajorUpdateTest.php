<?php

namespace Drupal\Tests\ef_major_update;

use Drupal\KernelTests\KernelTestBase;

/**
 * @group ef
 */
class MajorUpdateTest extends KernelTestBase {

  /**
   * Modules to install.
   * @var array
   */
  public static $modules = ['language', 'content_translation', 'node', 'ef', 'ef_major_update', 'ef_major_update_test'];


  public function setUp() {
    parent::setUp();
    $this->installConfig(['ef_major_update', 'ef_major_update_test']);
  }

  public function testMajorlyUpdatedFieldIsEmptyforNewNodesWithFieldOffForm() {
    $this->drupalCreateNode(['type' => 'test_field_off_form', 'status' => TRUE]);
  }
}