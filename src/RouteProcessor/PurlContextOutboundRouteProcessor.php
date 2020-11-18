<?php

namespace Drupal\purl\RouteProcessor;

use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\RouteProcessor\OutboundRouteProcessorInterface;
use Drupal\purl\Event\ModifierMatchedEvent;
use Drupal\purl\Plugin\MethodPluginManager;
use Drupal\purl\Plugin\Purl\Method\OutboundRouteAlteringInterface;
use Drupal\purl\PurlEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Route;

class PurlContextOutboundRouteProcessor implements OutboundRouteProcessorInterface, EventSubscriberInterface {
  /**
   * @var \Drupal\purl\Plugin\MethodPluginManager
   */
  private $manager;

  /**
   * @var \Drupal\purl\Event\ModifierMatchedEvent[]
   */
  private $events = [];

  public function __construct(MethodPluginManager $manager) {
    $this->manager = $manager;
  }

  public function processOutbound($route_name, Route $route, array &$parameters, BubbleableMetadata $bubbleable_metadata = NULL) {
    foreach ($this->events as $event) {
      $method = $event->getMethod();
      if ($method instanceof OutboundRouteAlteringInterface) {
        $path = $method->alterOutboundRoute($route_name, $event->getModifier(), $route, $parameters, $bubbleable_metadata);
      }
    }
  }

  public function onModifierMatched(ModifierMatchedEvent $event) {
    $this->events[] = $event;
  }

  public static function getSubscribedEvents() {
    return [
      PurlEvents::MODIFIER_MATCHED => ['onModifierMatched', 300],
    ];
  }

}
