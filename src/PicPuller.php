<?php
/**
 * Pic Puller plugin for Craft CMS 3.x
 *
 * Integrate InstagramService into Craft CMS.
 *
 * @link      https://picpuller.com
 * @copyright Copyright (c) 2017 John F Morton
 */

namespace jmx2\picpuller;

use jmx2\picpuller\services\AppManagement as AppManagementService;
use jmx2\picpuller\services\Feed as FeedService;
use jmx2\picpuller\variables\PicPullerVariable;
use jmx2\picpuller\models\Settings;
use jmx2\picpuller\widgets\LatestImage as LatestImageWidget;
use jmx2\picpuller\widgets\Stats as Stats;
use jmx2\picpuller\fields\Imagebrowser as Imagebrowser;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Elements;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use craft\services\Dashboard;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://craftcms.com/docs/plugins/introduction
 *
 * @author    John F Morton
 * @package   PicPuller
 * @since     3.0.0
 *
 * @property  AppManagementService $appManagement
 * @property  FeedService $feed
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class PicPuller extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * PicPuller::$plugin
     *
     * @var PicPuller
     */
    public static $plugin;

    public $schemaVersion = '3.0.0';
    public $hasCpSettings = true;
    public $hasCpSection = true;

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * PicPuller::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        // Return the customized name from settings for Pic Puller for Craft
        $this->name = $this->getName();

        $this->setComponents ( [
            'feed' => 'jmx2\picpuller\services\Feed',
            'appManagement' => 'jmx2\picpuller\services\AppManagement'
        ] );

        // Register our site routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['siteActionTrigger1'] = 'pic-puller/default';
            }
        );

        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['pic-puller/mediabyid/<mediaId:\S+>'] = ['template' => 'pic-puller/fields/mediabyid'];
                $event->rules['pic-puller/mediarecent/<nextMaxId:\S+>'] = ['template' => 'pic-puller/fields/mediarecent'];
                $event->rules['pic-puller/mediarecent'] = ['template' => 'pic-puller/fields/mediarecent'];
            }
        );

        // Register our elements
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
            }
        );

        // Register our fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = Imagebrowser::class;
            }
        );

        // Register our widgets
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = LatestImageWidget::class;
                $event->types[] = Stats::class;
            }
        );

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('picPuller', PicPullerVariable::class);
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                }
            }
        );

/**
 * Logging in Craft involves using one of the following methods:
 *
 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
 * Craft::info(): record a message that conveys some useful information.
 * Craft::warning(): record a warning message that indicates something unexpected has happened.
 * Craft::error(): record a fatal error that should be investigated as soon as possible.
 *
 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
 *
 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
 *
 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
 *
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */
        Craft::info(
            Craft::t(
                'pic-puller',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'pic-puller/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }

    /**
     * Returns the name of the plugin allowing user to override the name
     *
     * @return string The plugin name
     */
    protected function getName():string {
        Craft::trace('getName', self::className ());
        $settings = PicPuller::$plugin->getSettings();
        return $settings->shortName;
    }
}