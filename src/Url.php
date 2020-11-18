<?php

namespace Drupal\purl;

use Drupal\Core\Url as UrlBase;

class Url extends UrlBase {

  protected function urlGenerator() {
    if (!$this->urlGenerator) {
      $this->urlGenerator = \Drupal::getContainer()->get('purl.url_generator');
    }
    return $this->urlGenerator;
  }

}
