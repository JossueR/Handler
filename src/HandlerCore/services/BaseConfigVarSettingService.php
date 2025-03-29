<?php

namespace HandlerCore\services;

use HandlerCore\models\dao\ConfigVarDAO;

/**
 * Base service for handling configuration variable settings.
 * This class provides methods to retrieve, save, and confirm the state of configuration variables.
 */
class BaseConfigVarSettingService
{
    protected ConfigVarDAO $configDAO;

    private array $requiredKeys = [];

    /**
     * Constructor method for initializing the object with required keys.
     *
     * @param array $requiredKeys An array of keys that are required for configuration. If the array is non-associative, it is converted to an associative array with null values.
     * @return void
     */
    public function __construct(array $requiredKeys)
    {
        $this->configDAO = new ConfigVarDAO();

        // Convert non-associative $requiredKeys array to associative array
        if (array_is_list($requiredKeys)) {
            $this->requiredKeys = array_combine($requiredKeys, array_fill(0, count($requiredKeys), null));
        }else{
            $this->requiredKeys = $requiredKeys;
        }


    }

    /**
     * @return array
     */
    public function getRequiredKeys(): array
    {
        return $this->requiredKeys;
    }

    /**
     * Retrieves the configuration settings.
     *
     * @param bool $filled Determines whether to populate the settings with values from the data source.
     * @return array The array of configuration settings, possibly populated with fetched values.
     */
    public function getSettings(bool $filled = false): array {
        if($filled) {
            foreach ($this->requiredKeys as $var => $value) {
                $this->requiredKeys[$var] = $this->configDAO->getVar($var) ?? $value;
            }
        }
        
        return $this->requiredKeys;
    }

    /**
     * Saves the provided configuration prototype by updating relevant settings.
     *
     * @param array $proto An associative array representing the configuration prototype to save, where keys match configuration settings.
     * @return void
     */
    public function save(array $proto): void
    {
        $confDao = new ConfigVarDAO();

        $proto_keys = array_keys($this->getSettings());

        foreach ($proto as $key => $value) {
            if(in_array($key, $proto_keys)){
                $this->requiredKeys[$key] = $value;
                $confDao->setVar($key, $value);
            }
        }
    }

    /**
     * Checks if the configuration is properly set and all settings have non-empty values.
     *
     * @return bool Returns true if all settings are configured with non-empty values, otherwise false.
     */
    public function isConfigured(): bool
    {
        $settings = $this->getSettings(true);
        // Verificar que todos los valores no sean "empty"
        foreach ($settings as $value) {
            if (empty($value)) {
                return false;
            }
        }
        return true;
    }

}