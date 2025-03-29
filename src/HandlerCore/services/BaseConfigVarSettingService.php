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

    private array $allKeys = [];
    private array $optionalKeys = [];

    /**
     * Constructor method for initializing the object with required keys.
     *
     * @param array $requiredKeys An array of keys that are required for configuration. If the array is non-associative, it is converted to an associative array with null values.
     * @return void
     */
    public function __construct(array $requiredKeys, ?array $optionalKeys = null)
    {
        $this->configDAO = new ConfigVarDAO();

        // Convert non-associative $requiredKeys array to associative array
        if (array_is_list($requiredKeys)) {
            $requiredKeys = array_combine($requiredKeys, array_fill(0, count($requiredKeys), null));
        }

        if(is_null($optionalKeys)){
            $optionalKeys = [];
        }else{
            // Convert non-associative $optionalKeys array to associative array
            if (array_is_list($optionalKeys)) {
                $optionalKeys = array_combine($optionalKeys, array_fill(0, count($optionalKeys), null));
            }
        }
        $this->optionalKeys = $optionalKeys;

        $this->allKeys = [
            ...$requiredKeys,
            ...$optionalKeys,
        ];



    }

    /**
     * @return array
     */
    public function getAllKeys(): array
    {
        return $this->allKeys;
    }

    /**
     * Retrieves the configuration settings.
     *
     * @param bool $filled Determines whether to populate the settings with values from the data source.
     * @return array The array of configuration settings, possibly populated with fetched values.
     */
    public function getSettings(bool $filled = false): array {
        if($filled) {
            foreach ($this->allKeys as $var => $value) {
                $this->allKeys[$var] = $this->configDAO->getVar($var) ?? $value;
            }
        }
        
        return $this->allKeys;
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
                $this->allKeys[$key] = $value;
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
        foreach ($settings as $key => $value) {
            if (!$this->isOptional($key) && empty($value)) {
                return false;
            }
        }
        return true;
    }

    function isOptional(string $key): bool
    {
        return in_array($key, $this->optionalKeys);
    }

}