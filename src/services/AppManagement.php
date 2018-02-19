<?php
/**
 * Pic Puller plugin for Craft CMS 3.x
 *
 * Fixing stuff
 *
 * @link      https://picpuller.com
 * @copyright Copyright (c) 2017 John F Morton
 */

namespace jmx2\picpuller\services;

use jmx2\picpuller\models\PicPullerModel;
use jmx2\picpuller\PicPuller;

use Craft;
use craft\db\Query;
use craft\base\Component;
use jmx2\picpuller\records\Authorizations;

/**
 * AppManagement Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    John F Morton
 * @package   PicPuller
 * @since     3.0.0
 */
class AppManagement extends Component
{
    const IG_CLIENT_ID = '55b44fb02bd146a491edeb0e5dd9ef67';

    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     PicPuller::$plugin->appManagement->exampleService()
     *
     * @return mixed
     */
    public function exampleService ()
    {
        $result = 'app management service';
        return $result;
    }


    /**
     * Save Instagram ID and oAuth credentials in the database along with the craft_user_id they are associated with.
     *
     * PicPuller::$plugin->appManagement->saveCredentials()
     *
     * @param PicPullerModel $model
     */
    public function saveCredentials (PicPullerModel $model)
    {
        // If a record for the Craft user exists in this siteId, retrieve it
        $record = Authorizations::findOne ( ['craft_user_id' => $model->craft_user_id , 'siteId' => Craft::$app->sites->currentSite->id] );

        // If this Craft user has no record yet, create a new one
        if (!$record) {
            $record = new Authorizations();
        }

        // fill the record with the data
        $record->craft_user_id = $model->craft_user_id;
        $record->instagram_id = $model->instagram_id;
        $record->instagram_oauth = $model->instagram_oauth;
        $record->siteId = Craft::$app->sites->currentSite->id;
        // Save the data
        $record->save ();

        Craft::info ( 'Saving credentials.' , __METHOD__ );

        return true;
    }

    /**
     * Return the oAuth value for a user based on the user's Craft User ID
     * PicPuller::$plugin->appManagement->getUserOauthValue( INT $craftUserId)
     *
     * @param INT $craftUserId the ID of the Craft User
     *
     * @return STR The oAuth valuse for the user
     */
    public function getUserOauthValue (INT $craftUserId)
    {
        $record = Authorizations::findOne ( ['craft_user_id' => $craftUserId] );
        if ($record) {
            return $record['instagram_oauth'];
        } else {
            return false;
        }
    }

    /**
     * Return the Instagram ID of for a user based on the user's Craft ID
     *
     * @param  INT $id The ID of the Craft user
     *
     * @return STR     The Instagram ID for the user
     */
    public function getInstagramId ($craft_user_id)
    {
        $query = (new Query())
            ->select ( 'instagram_id' )
            ->from ( 'picpuller_authorizations' )
            ->where ( 'craft_user_id='.$craft_user_id )
            ->one ();
        return $query['instagram_id'];
    }

    /**
     * Delete an AuthorizationRecord by its ID
     *
     * @param INT $id the ID of the authorization to delete
     *
     * @return BOOL true or false
     */
    public function deleteAuthorizationByCraftUserId ($id)
    {
        Craft::info ( 'Deleting oAuth with id of '.$id );
        $userToDelete = Authorizations::find ()->where ( ['craft_user_id' => $id] )->one ();
        if ($userToDelete->delete ()) {
            return true;
        }
    }

    /**
     * Retrieve all the users with Pic Puller authorizations in the database
     *
     * @return array
     */
    public function getAllUsers ()
    {
        $allUsers = (new Query())
            ->select ( 'craft_user_id, instagram_id, instagram_oauth, u.firstName, u.lastName, u.username' )
            ->from ( 'picpuller_authorizations oauth' )
            ->join ( 'INNER JOIN' , 'users u' , 'oauth.craft_user_id=u.id' )
            ->orderBy ( 'u.id' )
            ->all ();
        return $allUsers;
    }

}
