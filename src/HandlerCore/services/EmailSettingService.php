<?php

namespace HandlerCore\services;

use HandlerCore\services\BaseConfigVarSettingService;

/**
 * The EmailSettingService class is responsible for managing email-related configurations.
 * It extends the BaseConfigVarSettingService to inherit base functionality for handling settings.
 *
 * This service initializes configuration variables for email settings
 * and provides a method to retrieve these settings as a Data Transfer Object (DTO).
 */
class EmailSettingService extends BaseConfigVarSettingService
{

    public function __construct()
    {
        parent::__construct([
            "EMAIL:HOST"=>null,
            "EMAIL:USER"=>null,
            "EMAIL:PASS"=>null,
            "EMAIL:PROTOCOL"=>null,
            "EMAIL:PORT"=>null,
        ]);
    }

    public function getSettingDTO(): EmailSettingDTO {
        $settings = $this->getSettings(true);

        return new EmailSettingDTO(
            $settings['EMAIL:HOST'] ?? '',
            $settings['EMAIL:USER'] ?? '',
            $settings['EMAIL:PASS'] ?? '',
            $settings['EMAIL:PROTOCOL'] ?? '',
            $settings['EMAIL:PORT'] ?? 0
        );
    }

}