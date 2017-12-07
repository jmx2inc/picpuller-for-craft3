<?php
/**
 * Pic Puller plugin for Craft CMS 3.x
 *
 * Integrate InstagramService into Craft CMS.
 *
 * @link      https://picpuller.com
 * @copyright Copyright (c) 2017 John F Morton
 */

namespace jmx2\picpuller\variables;

use craft\helpers\UrlHelper;
use jmx2\picpuller\PicPuller;

use Craft;

/**
 * Pic Puller Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.picPuller }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    John F Morton
 * @package   PicPuller
 * @since     3.0.0
 */
class PicPullerVariable
{
    // Public Methods
    // =========================================================================


    /**
     * Get the Instagram oAuth value stored in the Craft DB
     * for a specific Craft user based on their Craft user ID
     *
     * For example, in a Twig template, call it like this:
     * {{ craft.picPuller.getInstagramOAuth(42) }}
     *
     * @param int $craftUserId
     *
     * @return string
     */
    public function getInstagramOAuth (int $craftUserId): string
    {
        return PicPuller::$plugin->appManagement->getUserOauthValue ( $craftUserId );
    }

    /**
     * Get all users in Craft that have saved Pic Puller information
     * in the picpullerauthorization table
     *
     * @return array
     */
    public function getAllUsers (): array
    {
        return PicPuller::$plugin->appManagement->getAllUsers ();
    }


    /**
     * Get user info from Instagram
     *
     * @param  array $tags [user_id, the Craft user ID, is required, plus optional use_stale_cache ]
     *
     * @return array  An array of media and user data
     */
    public function user ($tags = null) : array
    {
        return PicPuller::$plugin->feed->user ($tags);
    }

    /**
     * Get recent media from a single user from Instagram
     * @param  array  $tags [description]
     * @return array  An array of media and user data
     */
    public function media_recent($tags = null) {
        return PicPuller::$plugin->feed->media_recent($tags);
    }

    /**
     * Get a single piece of media from Instagram
     * @param  Array  $tags [description]
     * @return Array  An array of media and user data
     */
    public function media($tags = null) {
        return PicPuller::$plugin->feed->media ($tags);
    }

    /**
     * Return the setting for whether the oAuth should be shared across all Craft users
     *
     * @return bool The default is false indicating each user should authorize their own account
     */
    public function getShareOauthSetting(){
        return PicPuller::$plugin->settings->sharedoauth;
    }

    /**
     * Return the setting for which Craft user should be shared across all Craft users
     *  - defaults to 1, and will return a user ID even if the sharedoauth is set to false
     *
     * @return string
     */
    public function getSharedOauthUser() {
        return PicPuller::$plugin->settings->sharedoauthuser;
    }

    public function getSettingsUrl() {
        return UrlHelper::cpUrl ('settings/plugins/'.PicPuller::$plugin->id);
    }

    /**
     * @return mixed
     */
    public function allUsers ()
    {
        $users = PicPuller::$plugin->appManagement->exampleService ();
        return $users;
    }

    /**
     * @return int|null
     */
    public function siteId ()
    {
        return Craft::$app->sites->currentSite->id;
    }
}
