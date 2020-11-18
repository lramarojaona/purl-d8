<?php

namespace Drupal\purl;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\purl\Entity\Provider;
use Drupal\purl\Plugin\Purl\Method\MethodInterface;
use Symfony\Component\HttpFoundation\Request;

class ContextHelper {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ContextHelper constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * @param array $contexts
   * @param $path
   * @param array $options
   * @param \Symfony\Component\HttpFoundation\Request|null $request
   * @param \Drupal\Core\Render\BubbleableMetadata|null $metadata
   * @return mixed
   */
  public function processOutbound(array $contexts, $path, array &$options, Request $request = NULL, BubbleableMetadata $metadata = NULL) {

    $result = $path;

    /** @var Context $context */
    foreach ($contexts as $context) {

      if (!in_array(MethodInterface::STAGE_PROCESS_OUTBOUND, $context->getMethod()->getStages())) {
        continue;
      }

      $contextResult = NULL;

      if ($context->getAction() == Context::ENTER_CONTEXT) {
        $contextResult = $context->getMethod()->enterContext($context->getModifier(), $result, $options);
      }
      elseif ($context->getAction() == Context::EXIT_CONTEXT) {
        $contextResult = $context->getMethod()->exitContext($context->getModifier(), $result, $options);
      }

      $result = $contextResult ?: $result;
    }

    return $result;
  }

  /**
   * @param array $contexts
   * @param $routeName
   * @param array $parameters
   * @param array $options
   * @param $collect_bubblable_metadata
   */
  public function preGenerate(array $contexts, $routeName, array &$parameters, array &$options, $collect_bubblable_metadata) {
    $this->ensureContexts($contexts);

    /** @var Context $context */
    foreach ($contexts as $context) {

      if (!in_array(MethodInterface::STAGE_PRE_GENERATE, $context->getMethod()->getStages())) {
        continue;
      }

      if ($context->getAction() == Context::ENTER_CONTEXT) {
        $context->getMethod()->preGenerateEnter($context->getModifier(), $routeName, $parameters, $options, $collect_bubblable_metadata);
      }
      elseif ($context->getAction() == Context::EXIT_CONTEXT) {
        $context->getMethod()->preGenerateExit($context->getModifier(), $routeName, $parameters, $options, $collect_bubblable_metadata);
      }

    }
  }

  /**
   * @param array $contexts
   * @return bool
   */
  private function ensureContexts(array $contexts) {
    foreach ($contexts as $index => $context) {
      if (!$context instanceof Context) {
        throw new \InvalidArgumentException(sprintf('#%d is not a context.', $index + 1));
      }
    }
  }

  /**
   * @param array $map
   * @return array
   */
  public function createContextsFromMap(array $map) {
    if (count($map) === 0) {
      return [];
    }

    $providers = $this->entityTypeManager->getStorage('purl_provider')->loadMultiple(array_keys($map));

    return array_map(function (Provider $provider) use ($map) {
      return new Context($map[$provider->id()], $provider->getMethodPlugin());
    }, $providers);
  }

}
