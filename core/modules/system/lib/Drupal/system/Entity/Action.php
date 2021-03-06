<?php

/**
 * @file
 * Contains \Drupal\system\Entity\Action.
 */

namespace Drupal\system\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\EntityWithPluginBagInterface;
use Drupal\system\ActionConfigEntityInterface;
use Drupal\Core\Action\ActionBag;
use Drupal\Component\Plugin\ConfigurablePluginInterface;

/**
 * Defines the configured action entity.
 *
 * @ConfigEntityType(
 *   id = "action",
 *   label = @Translation("Action"),
 *   admin_permission = "administer actions",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   }
 * )
 */
class Action extends ConfigEntityBase implements ActionConfigEntityInterface, EntityWithPluginBagInterface {

  /**
   * The name (plugin ID) of the action.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the action.
   *
   * @var string
   */
  public $label;

  /**
   * The action type.
   *
   * @var string
   */
  protected $type;

  /**
   * The configuration of the action.
   *
   * @var array
   */
  protected $configuration = array();

  /**
   * The plugin ID of the action.
   *
   * @var string
   */
  protected $plugin;

  /**
   * The plugin bag that stores action plugins.
   *
   * @var \Drupal\Core\Action\ActionBag
   */
  protected $pluginBag;

  /**
   * {@inheritdoc}
   */
  protected $pluginConfigKey = 'configuration';

  /**
   * {@inheritdoc}
   */
  public function getPluginBag() {
    if (!$this->pluginBag) {
      $this->pluginBag = new ActionBag(\Drupal::service('plugin.manager.action'), $this->plugin, $this->configuration);
    }
    return $this->pluginBag;
  }

  /**
   * {@inheritdoc}
   */
  public function getPlugin() {
    return $this->getPluginBag()->get($this->plugin);
  }

  /**
   * {@inheritdoc}
   */
  public function setPlugin($plugin_id) {
    $this->plugin = $plugin_id;
    $this->getPluginBag()->addInstanceId($plugin_id);
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinition() {
    return $this->getPlugin()->getPluginDefinition();
  }

  /**
   * {@inheritdoc}
   */
  public function execute(array $entities) {
    return $this->getPlugin()->executeMultiple($entities);
  }

  /**
   * {@inheritdoc}
   */
  public function isConfigurable() {
    return $this->getPlugin() instanceof ConfigurablePluginInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return $this->type;
  }

  /**
   * {@inheritdoc}
   */
  public static function sort($a, $b) {
    $a_type = $a->getType();
    $b_type = $b->getType();
    if ($a_type != $b_type) {
      return strnatcasecmp($a_type, $b_type);
    }
    return parent::sort($a, $b);
  }

  /**
   * {@inheritdoc}
   */
  public function getExportProperties() {
    $properties = parent::getExportProperties();
    $names = array(
      'type',
      'plugin',
      'configuration',
    );
    foreach ($names as $name) {
      $properties[$name] = $this->get($name);
    }
    return $properties;
  }

}
