<?php
/**
 * Pic Puller plugin for Craft CMS 3.x
 *
 * Integrate InstagramService into Craft CMS.
 *
 * @link      https://picpuller.com
 * @copyright Copyright (c) 2017 John F Morton
 */

namespace jmx2\picpuller\models;

use jmx2\picpuller\PicPuller;

use Craft;
use craft\base\Model;

/**
 * PicPuller Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    John F Morton
 * @package   PicPuller
 * @since     3.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Some field model attribute
     *
     * @var string
     */
    public $shortName = 'Pic Puller for Craft';
    public $sharedoauth = false;
    public $sharedoauthuser = 1;

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
            ['shortName', 'string'],
            ['shortName', 'default', 'value' => 'Pic Puller for Craft'],
            ['sharedoauth', 'default', 'value' => 0],
            ['sharedoauthuser', 'default', 'value' => 1]
        ];
    }
}
