<?php
/**
 * Pic Puller plugin for Craft CMS 3.x
 *
 * Integrate Instagram into Craft CMS.
 *
 * @link      https://picpuller.com
 * @copyright Copyright (c) 2017 John F Morton
 */

namespace jmx2\picpuller\widgets;

use jmx2\picpuller\PicPuller;
use jmx2\picpuller\assetbundles\latestimagewidget\LatestImageWidgetAsset;

use Craft;
use craft\base\Widget;

/**
 * Pic Puller Widget
 *
 * Dashboard widgets allow you to display information in the Admin CP Dashboard.
 * Adding new types of widgets to the dashboard couldn’t be easier in Craft
 *
 * https://craftcms.com/docs/plugins/widgets
 *
 * @author    John F Morton
 * @package   PicPuller
 * @since     3.0.0
 */
class Stats extends Widget
{

    // Public Properties
    // =========================================================================

    /**
     * @var string The message to display
     */
    public $message = 'Hello, world.';

    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('pic-puller', 'Stats');
    }

    /**
     * Returns the path to the widget’s SVG icon.
     *
     * @return string|null The path to the widget’s SVG icon
     */
    public static function iconPath()
    {
        return Craft::getAlias("@jmx2/picpuller/assetbundles/latestimagewidget/dist/img/LatestImage-icon.svg");
    }

    /**
     * Returns the widget’s maximum colspan.
     *
     * @return int|null The widget’s maximum colspan, if it has one
     */
    public static function maxColspan()
    {
        return null;
    }

    // Public Methods
    // =========================================================================


    /**
     * Returns the widget's body HTML.
     *
     * @return string|false The widget’s body HTML, or `false` if the widget
     *                      should not be visible. (If you don’t want the widget
     *                      to be selectable in the first place, use {@link isSelectable()}.)
     */
    public function getBodyHtml()
    {
        Craft::$app->getView()->registerAssetBundle(LatestImageWidgetAsset::class);

        return Craft::$app->getView()->renderTemplate(
            'pic-puller/_components/widgets/Stats_body',
            [
                'message' => $this->message
            ]
        );
    }
}
