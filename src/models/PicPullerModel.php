<?php
/**
 * Pic Puller plugin for Craft CMS 3.x
 *
 * Integrate Instagram into Craft CMS.
 *
 * @link      https://picpuller.com
 * @copyright Copyright (c) 2017 John F Morton
 */

namespace jmx2\picpuller\models;

use jmx2\picpuller\PicPuller;

use Craft;
use craft\base\Model;

/**
 * PicPullerModel Model
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
class PicPullerModel extends Model
{
    // Public Properties
    // =========================================================================
    public $craft_user_id;
    public $instagram_id;
    public $instagram_oauth;

    public function attributeLabels ()
    {
        return [
            'craft_user_id' => 'Craft CMS ID' ,
            'instagram_id' => 'Instagram ID' ,
            'instagram_oauth' => 'Instagram oAuth'
        ];
    }

    /**
     * Some model attribute
     *
     * @var string
     */
    // public $someAttribute = 'Some Default';

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
    public function rules ()
    {
        return [
            ['craft_user_id' , 'string'] ,
            ['instagram_id' , 'string'] ,
            ['instagram_oauth' , 'string'] ,
        ];
    }
}
