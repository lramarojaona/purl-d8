<?php

namespace Drupal\purl;

use Drupal\purl\Event\ModifierMatchedEvent;

class MatchedModifiers {

  /**
   * @var \Drupal\purl\Event\ModifierMatchedEvent[]
   */
  private $matched = [];

  /**
   * @return Event\ModifierMatchedEvent[]
   */
  public function getMatched() {
    return $this->getEvents();
  }

  /**
   * @return Event\ModifierMatchedEvent[]
   */
  public function getEvents() {
    return $this->matched;
  }

  /**
   * @param \Drupal\purl\Event\ModifierMatchedEvent $event
   *
   * @return null
   */
  public function add(ModifierMatchedEvent $event) {
    $this->matched[] = $event;
  }

  public function createContexts($action = NULL) {
    return array_map(function (ModifierMatchedEvent $event) use ($action) {
      return new Context($event->getModifier(), $event->getMethod(), $action);
    }, $this->getMatched());
  }

}
