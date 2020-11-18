<?php

namespace Drupal\purl\Entity;


interface ProviderConfigInterface {

  /**
   * @return string
   */
  public function getProviderKey();

  /**
   * @return string
   */
  public function getLabel();

  /**
   * @return string
   */
  public function getMethodKey();

  /**
   * @return \Drupal\purl\Plugin\Purl\Method\MethodInterface
   */
  public function getMethodPlugin();

  /**
   * @return \Drupal\purl\Plugin\Purl\Provider\ProviderInterface
   */
  public function getProviderPlugin();

}
