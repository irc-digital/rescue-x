<?php

namespace Drupal\Tests\ef_sitewide_settings\Kernel;

use Drupal\Core\Entity\EntityStorageException;
use Drupal\ef_sitewide_settings\Entity\SitewideSettings;
use Drupal\KernelTests\KernelTestBase;

class SitewideSettingsTest extends KernelTestBase  {
  public static $modules = ['system', 'field', 'language', 'content_translation', 'ef_sitewide_settings', 'ef_sitewide_settings_test'];

  public function setUp() {
    parent::setUp();

    $this->installEntitySchema('sitewide_settings');
    $this->installConfig(['field', 'ef_sitewide_settings', 'ef_sitewide_settings_test']);

    $this->languageManager = $this->createMock('\Drupal\Core\Language\LanguageManagerInterface');
    $english = $this->createMock('\Drupal\Core\Language\LanguageInterface');
    $english->expects($this->any())
      ->method('getId')
      ->willReturn('en');
    $german = $this->createMock('\Drupal\Core\Language\LanguageInterface');
    $german->expects($this->any())
      ->method('getId')
      ->willReturn('de');
    $this->languageManager = $this->createMock('\Drupal\Core\Language\LanguageManagerInterface');
    $this->languageManager->expects($this->any())
      ->method('getCurrentLanguage')
      ->will($this->onConsecutiveCalls($english, $english, $german, $german));

    $this->languageManager->expects($this->any())
      ->method('getLanguages')
      ->willReturn(['en' => $english, 'de' => $german]);

    $this->languageManager->expects($this->any())
      ->method('getDefaultLanguage')
      ->will($this->returnValue($english));

    \Drupal::getContainer()->set('language_manager', $this->languageManager);
  }

  public function testBasicSettingsCreation () {
    /** @var SitewideSettings $testSettings */
    $testSettings = SitewideSettings::create([
      'type' => 'sws_test_1',
    ]);

    $testSettings->save();

    $this->assertNotNull($testSettings->id());
  }

  public function testBasicSettingsEdit () {
    /** @var SitewideSettings $testSettings */
    $testSettings = SitewideSettings::create([
      'type' => 'sws_test_1',
      'field_sws_test_1_text' => [
        ['value' => 'Trump mania'],
      ],
    ]);

    $testSettings->save();

    $this->assertNotNull($testSettings->id());

    $testSettings->field_sws_test_1_text = 'Test change';

    $testSettings->save();

    $this->assertEquals('Test change', $testSettings->field_sws_test_1_text->value);
  }

  /**
   * @covers \Drupal\ef_sitewide_settings\SitewideSettingsManager
   */
  public function testRetrievingSettingsFromManager () {
    /** @var SitewideSettings $testSettings */
    $testSettings = SitewideSettings::create([
      'type' => 'sws_test_1',
      'field_sws_test_1_text' => [
        ['value' => 'It is gonna be huge'],
      ],
    ]);

    $testSettings->save();

    $this->assertNotNull($testSettings->id());

    /** @var \Drupal\ef_sitewide_settings\SitewideSettingsManagerInterface $settingsManager */
    $settingsManager = \Drupal::service('ef_sitewide_settings.manager');

    $value = $settingsManager->getSitewideSettingsForType('sws_test_1');
    $this->assertEquals('It is gonna be huge', $value->field_sws_test_1_text->value);

    $this->assertNotNull($value);
  }

  public function testAttemptToDuplicateSettings () {
    /** @var SitewideSettings $testSettings */
    $testSettings = SitewideSettings::create([
      'type' => 'sws_test_1',
      'field_sws_test_1_text' => [
        ['value' => 'Build that wall']
      ],
    ]);

    $testSettings->save();

    $this->assertNotNull($testSettings->id());


    /** @var \Drupal\ef_sitewide_settings\SitewideSettingsManagerInterface $settingsManager */
    $settingsManager = \Drupal::service('ef_sitewide_settings.manager');

    /** @var \Drupal\ef_sitewide_settings\SitewideSettingsInterface $value */
    $value = $settingsManager->getSitewideSettingsForType('sws_test_1');

    $this->assertNotNull($value);

    $this->assertEquals('Build that wall', $value->field_sws_test_1_text->value);

    /** @var SitewideSettings $testSettings */
    $testSettings = SitewideSettings::create([
      'type' => 'sws_test_1',
    ]);

    $test_passed = FALSE;
    try {
      $testSettings->save();
    } catch (EntityStorageException $exception) {
      $test_passed = TRUE;
    }

    $this->assertTrue($test_passed);


    /** @var \Drupal\ef_sitewide_settings\SitewideSettingsManagerInterface $settingsManager */
    $settingsManager = \Drupal::service('ef_sitewide_settings.manager');

    $value = $settingsManager->getSitewideSettingsForType('sws_test_1');

    $this->assertNotNull($value);
  }

  public function testMultilingual () {
    $english_settings = SitewideSettings::create([
      'type' => 'sws_test_1',
      'language' => 'en',
      'field_sws_test_1_text' => [
        ['value' => 'Build that wall']
      ],
    ]);

    $english_settings->save();

    $this->assertNotNull($english_settings);

    $german_settings = $english_settings->addTranslation('de');
    $german_settings = $german_settings->getTranslation('de');
    $german_settings->field_sws_test_1_text = 'Build that wall (DE)';
    $german_settings->save();

    $this->assertNotNull($german_settings);
    $this->assertEquals('Build that wall (DE)', $german_settings->field_sws_test_1_text->value);
    $this->assertEquals('Build that wall', $english_settings->field_sws_test_1_text->value);
  }

}