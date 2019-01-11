<?php

namespace Drupal\ef_sitewide_settings\Exception;

use Drupal\ef_sitewide_settings\SitewideSettingsInterface;

class DuplicateSettingNotPermittedException extends \RuntimeException  {
  /** @var SitewideSettingsInterface */
  private $sitewideSettings;

  public function __construct(SitewideSettingsInterface $sitewideSettings, $message = "", $code = 0, \Throwable $previous = NULL) {
    parent::__construct($message, $code, $previous);

    $this->sitewideSettings = $sitewideSettings;
  }

  public function getSitewideSettings () {
    return $this->sitewideSettings;
  }
}