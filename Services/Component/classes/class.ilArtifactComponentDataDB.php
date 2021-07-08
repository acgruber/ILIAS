<?php declare(strict_types=1);

use ILIAS\Data;

/**
 * Repository for component data implemented over artifacts.
 */
class ilArtifactComponentDataDB implements ilComponentDataDB
{
    public const COMPONENT_DATA_PATH = "Services/Component/artifacts/component_data.php";
    public const PLUGIN_DATA_PATH = "Services/Component/artifacts/plugin_data.php";

    protected array $components;
    protected array $component_id_by_type_and_name;
    protected array $pluginslot_by_id;
    protected array $plugin_by_id;

    public function __construct(Data\Factory $data_factory)
    {
        $component_data = $this->readComponentData();
        $plugin_data = $this->readPluginData();
        $this->components = [];
        $this->component_id_by_type_and_name = [
            "Modules" => [],
            "Services" => []
        ];
        $this->pluginslot_by_id = [];
        $plugins_per_slot = [];
        foreach ($component_data as $comp_id => list($type, $comp_name, $slot_data)) {
            $slots = [];
            $component = new ilComponentInfo(
                $comp_id,
                $type,
                $comp_name,
                $slots
            );
            foreach ($slot_data as list($slot_id, $slot_name)) {
                $plugins_per_slot[$slot_id] = [];
                $slots[$slot_id] = new ilPluginSlotInfo(
                    $component,
                    $slot_id,
                    $slot_name,
                    $plugins_per_slot[$slot_id]
                );
                $this->pluginslot_by_id[$slot_id] = $slots[$slot_id];
            }
            $this->components[$comp_id] = $component;
            $this->component_id_by_type_and_name[$type][$comp_name] = $comp_id;
            unset($slots);
        }
        $this->plugin_by_id = [];
        foreach ($plugin_data as $plugin_id => $plugin_data) {
            list(
                $type, $comp_name, $slot_name, $plugin_name, $plugin_version,
                $ilias_min_version, $ilias_max_version, $responsible, $responsible_mail,
                $learning_progress, $supports_export, $supports_cli_setup
            ) = $plugin_data;
            if (!$this->hasComponent($type, $comp_name)) {
                throw new \InvalidArgumentException(
                    "Can't find component $type/$comp_name for plugin $plugin_name"
                );
            }
            $component = $this->getComponentByTypeAndName($type, $comp_name);
            if (!$component->hasPluginslotName($slot_name)) {
                throw new \InvalidArgumentException(
                    "Can't find slot $type/$comp_name/$slot_name for plugin $plugin_name"
                );
            }
            $slot = $component->getPluginSlotByName($slot_name);
            $this->plugin_by_id[$plugin_id] = new ilPluginInfo(
                $slot,
                $plugin_id,
                $plugin_name,
                false,
                $data_factory->version($plugin_version),
                0,
                $data_factory->version($plugin_version),
                0,
                $data_factory->version($ilias_min_version),
                $data_factory->version($ilias_max_version),
                $responsible,
                $responsible_mail,
                $learning_progress ?? false,
                $supports_export ?? false,
                $supports_cli_setup ?? true
            );
            $plugins_per_slot[$slot->getId()][] = $this->plugin_by_id[$plugin_id];
        }
    }

    protected function readComponentData() : array
    {
        return require self::COMPONENT_DATA_PATH;
    }

    protected function readPluginData() : array
    {
        return require self::PLUGIN_DATA_PATH;
    }

    /**
     * @inheritdocs
     */
    public function hasComponent(string $type, string $name) : bool
    {
        if (!in_array($type, ilComponentInfo::TYPES)) {
            throw new \InvalidArgumentException(
                "Unknown component type $type."
            );
        }

        return isset($this->component_id_by_type_and_name[$type][$name]);
    }

    /**
     * @inheritdocs
     */
    public function hasComponentId(string $id) : bool
    {
        return isset($this->components[$id]);
    }

    /**
     * @inheritdocs
     */
    public function getComponents() : Iterator
    {
        foreach ($this->components as $id => $comp) {
            yield $id => $comp;
        }
    }

    /**
     * @inheritdocs
     */
    public function getComponentById(string $id) : ilComponentInfo
    {
        if (!$this->hasComponentId($id)) {
            throw new \InvalidArgumentException(
                "Unknown component $id"
            );
        }
        return $this->components[$id];
    }

    /**
     * @inheritdocs
     */
    public function getComponentByTypeAndName(string $type, string $name) : ilComponentInfo
    {
        if (!$this->hasComponent($type, $name)) {
            throw new \InvalidArgumentException(
                "Unknown component $type/$name"
            );
        }
        return $this->components[$this->component_id_by_type_and_name[$type][$name]];
    }


    /**
     * @inheritdocs
     */
    public function hasPluginSlotId(string $id) : bool
    {
        return isset($this->pluginslot_by_id[$id]);
    }

    /**
     * @inheritdocs
     */
    public function getPluginSlots() : Iterator
    {
        foreach ($this->pluginslot_by_id as $id => $slot) {
            yield $id => $slot;
        }
    }

    /**
     * @inheritdocs
     */
    public function getPluginSlotById(string $id) : ilPluginSlotInfo
    {
        if (!$this->hasPluginslotId($id)) {
            throw new \InvalidArgumentException(
                "Unknown pluginslot $id"
            );
        }
        return $this->pluginslot_by_id[$id];
    }

    /**
     * Check if a plugin exists.
     */
    public function hasPlugin(string $id) : bool
    {
        return isset($this->plugin_by_id[$id]);
    }

    /**
     * Get all plugins.
     *
     * Keys are the ids.
     *
     * @return Iterator <string, ilPluginInfo>
     */
    public function getPlugins() : Iterator
    {
        foreach ($this->plugin_by_id as $id => $plugin) {
            yield $id => $plugin;
        }
    }

    /**
     * Get a plugin by id.
     *
     * @throws \InvalidArgumentException if plugin does not exist
     */
    public function getPlugin(string $id) : ilPluginInfo
    {
        if (!$this->hasPlugin($id)) {
            throw new \InvalidArgumentException(
                "Unknown plugin $id."
            );
        }
        return $this->plugin_by_id[$id];
    }
}
