<?php declare(strict_types=1);

namespace ForWinterCms\BlogHub\Models;

use Winter\Storm\Database\Model;

class MetaSettings extends Model
{

    /**
     * Implement Interfaces
     *
     * @var array
     */
    public $implement = ['System.Behaviors.SettingsModel'];

    /**
     * Settings Mode
     *
     * @var string
     */
    public $settingsCode = 'forwn_bloghub_meta_settings';

    /**
     * Settings Fields
     *
     * @var string
     */
    public $settingsFields = 'fields.yaml';

}
