<?php

namespace Drupal\purl;

use Drupal\purl\Plugin\Purl\Method\MethodInterface;
use Drupal\purl\Plugin\Purl\Provider\ProviderInterface;

class Modifier {
  /**
   * @var string
   */
  private $modifierKey;

  /**
   * @var string
   */
  private $value;

  /**
   * @var \Drupal\purl\Plugin\Purl\Method\MethodInterface
   */
  private $method;

  /**
   * @var \Drupal\purl\Entity\ProviderInterface
   */
  private $provider;

  public function __construct($modifierKey, $value, MethodInterface $method, ProviderInterface $provider) {
    $this->modifierKey = $modifierKey;
    $this->value = $value;
    $this->method = $method;
    $this->provider = $provider;
  }

  /**
   * @return string
   */
  public function getModifierKey() {
    return $this->modifierKey;
  }

  /**
   * @return string
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * @return \Drupal\purl\Plugin\Purl\Method\MethodInterface
   */
  public function getMethod() {
    return $this->method;
  }

  /**
   * @return \Drupal\purl\Entity\ProviderInterface
   */
  public function getProvider() {
    return $this->provider;
  }

}
