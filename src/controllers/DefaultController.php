<?php
/**
 * Pic Puller plugin for Craft CMS 3.x
 *
 * Integrate Instagram into Craft CMS.
 *
 * @link      https://picpuller.com
 * @copyright Copyright (c) 2017 John F Morton
 */

namespace jmx2\picpuller\controllers;

use craft\web\Response;
use jmx2\picpuller\models\PicPullerModel;
use jmx2\picpuller\PicPuller;

use Craft;
use craft\web\Controller;
use jmx2\picpuller\services\AppManagement;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    John F Morton
 * @package   PicPuller
 * @since     3.0.0
 */
class DefaultController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = ['index' , 'save-credentials'];

    // Public Methods
    // =========================================================================

    /**
     * Handle a request going to our plugin's index action URL,
     * e.g.: actions/pic-puller/default
     *
     * @return mixed
     */
    public function actionIndex ()
    {

        $result = 'Welcome to the DefaultController actionIndex() method';

        return $result;
    }


    /**
     * Handle a request going to our plugin's saveCredentials URL,
     * e.g.: actions/pic-puller/default/save-credentials
     *
     * @return mixed
     */
    public function actionSaveCredentials ()
    {
        $this->requirePostRequest ();
        Craft::trace ( 'PPfC: Saving oAuth Credentials' , __METHOD__ );
        $model = new PicPullerModel();
        $attributes = Craft::$app->request->getBodyParams ();
        $formattedReturnedData = [
            'craft_user_id' => $attributes['user_id'] ,
            'instagram_oauth' => $attributes['instagram_oauth'] ,
            'instagram_id' => $attributes['instagram_id'] ,
        ];

        Craft::$app->response->format = Response::FORMAT_JSON;
        $model->setAttributes ( $formattedReturnedData ); //  setAttributes
        if ($model->validate ()) {

            if (PicPuller::$plugin->appManagement->saveCredentials ( $model )) {
                $message = 'Your Instagram credentials were saved to the database.';
                $return = [
                    'success' => true ,
                    'message' => $message
                ];
            } else {
                $message = 'An error occurred. Your Instagram credentials were *not* saved to the database.';
                $return = [
                    'success' => false ,
                    'message' => $message
                ];
            };
        } else {
            $message = 'The data was in unexpected format and did not validate properly.';
            $return = [
                'success' => false ,
                'message' => $message
            ];
        }
        return $return;
    }


    /**
     * actions/pic-puller/default/remove-oauth
     *
     * @return bool
     */
    public function actionRemoveOauth() {
        $this->requirePostRequest ();
        $attributes = Craft::$app->request->getBodyParams ();
        PicPuller::$plugin->appManagement->deleteAuthorizationByCraftUserId ($attributes['user_id']);
    }
}
