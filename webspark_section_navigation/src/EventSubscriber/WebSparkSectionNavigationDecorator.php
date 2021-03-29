<?php

namespace Drupal\webspark_section_navigation\EventSubscriber;

use Drupal\Component\Utility\SortArray;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\lb_section_navigation\EventSubscriber\SectionNavigationSubscriber;

/**
 * Section navigation event subscriber.
 */
class WebSparkSectionNavigationDecorator extends SectionNavigationSubscriber {

  /**
   * Assemble a render array with the link to the components.
   */
  protected function buildSectionLinks($section, $currentComponent, $contexts) {
    $build = [
      '#theme' => 'section_navigation',
      '#links' => [],
    ];
    foreach ($section->getComponents() as $componentUuid => $component) {
      $config = $component->get('configuration');
      if (isset($config['provider']) && $config['provider']=='block_content') {
        $id_parts = explode(':', $config['id']);
        $uuid = $id_parts[1];
        $block = \Drupal::service('entity.repository')->loadEntityByUuid('block_content', $uuid);
        $isAnchor = (int)$block->field_anchor->value;

        if ($isAnchor !== 1) {
          continue;
        }
      }
      if ($componentUuid != $currentComponent->get('uuid')) {
        $build['#links'][$componentUuid] = Link::fromTextAndUrl(
          $component->getPlugin($contexts)->label(),
          Url::fromUserInput('#' . $componentUuid)
        )
          ->toRenderable();

        $build['#links'][$componentUuid]['#weight'] = $component->getWeight();
      }
    }
    uasort($build['#links'], [SortArray::class, 'sortByWeightProperty']);
    return $build;
  }
}