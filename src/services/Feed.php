<?php
/**
 * Pic Puller plugin for Craft CMS 3.x
 *
 * stuff
 *
 * @link      https://picpuller.com
 * @copyright Copyright (c) 2017 John F Morton
 */

namespace jmx2\picpuller\services;

use Craft;
use craft\base\Component;
use craft\helpers\Json;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use jmx2\picpuller\PicPuller;

/**
 * Feed Service
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
class Feed extends Component
{
    // This is the URL for v1 of the Instagram API
    const IG_API_URL = 'https://api.instagram.com/v1/';

    private $_ig_picpuller_prefix = '';
    private $use_stale_cache = true;

    // $refresh stores the amount of time we'll keep cached data (urls, not actual images) from Instagram
    // private $refresh = 1440;    // Period between cache refreshes, in minutes. 1440 is 24 hours.

    // Public Methods
    // =========================================================================

    /**
     * user
     *
     * Get the user information from a specified Craft user that has authorized the Instagram application
     * https://www.instagram.com/developer/endpoints/users/#get_users
     *
     * @param null $tags
     *
     * @return array
     */
    public function user ($tags = null)
    {
        Craft::info ( "Pic Puller user function " );
        $variables = [];

        $user_id = $tags['user_id'] ?? null;

        // user_id is required so check for it before proceeding
        if (!$user_id) {
            return $this->_missinguser_idErrorReturn ();
        }
        $use_stale_cache = $tags['use_stale_cache'] ?? $this->use_stale_cache;

        $oauth = $this->_getUserOauth ( $user_id );
        if (!$oauth) {
            return $this->_unauthorizedUserErrorReturn ();
        }
        $ig_user_id = $this->_getInstagramId ( $user_id );

        // set up the USERS url used by Instagram
        $query_string = "users/$ig_user_id?access_token={$oauth}";

        $data = $this->_fetch_data ( $query_string , $use_stale_cache );

        if ($data['status'] === false) {
            // No images to return, even from cache, so exit the function and return the error
            // Set up the basic error messages returned by _fetch_data function
            $variables[] = [
                $this->_ig_picpuller_prefix.'error_type' => $data['error_type'] ,
                $this->_ig_picpuller_prefix.'error_message' => $data['error_message'] ,
                $this->_ig_picpuller_prefix.'status' => $data['status']
            ];
            return $variables;
        }

        $cacheddata = isset( $data['cacheddata'] ) ? true : false;

        $node = $data['data'];
        $variables[] = [
            $this->_ig_picpuller_prefix.'username' => $node['username'] ,
            $this->_ig_picpuller_prefix.'bio' => $node['bio'] ,
            $this->_ig_picpuller_prefix.'profile_picture' => $node['profile_picture'] ,
            $this->_ig_picpuller_prefix.'website' => $node['website'] ,
            $this->_ig_picpuller_prefix.'full_name' => $node['full_name'] ,
            $this->_ig_picpuller_prefix.'counts_media' => (string)$node['counts']['media'] ,
            $this->_ig_picpuller_prefix.'counts_followed_by' => (string)$node['counts']['followed_by'] ,
            $this->_ig_picpuller_prefix.'counts_follows' => (string)$node['counts']['follows'] ,
            $this->_ig_picpuller_prefix.'id' => $node['id'] ,
            $this->_ig_picpuller_prefix.'status' => $data['status'] ,
            $this->_ig_picpuller_prefix.'cacheddata' => $cacheddata ,
            $this->_ig_picpuller_prefix.'error_type' => $data['error_type'] ,
            $this->_ig_picpuller_prefix.'error_message' => $data['error_message']
        ];
        return $variables;
    }

    /**
     * Media
     *
     * Get information about a single media object.
     * https://www.instagram.com/developer/endpoints/media/#get_media
     *
     *
     * @param null $tags
     *
     * @return array
     *
     */
    public function media ($tags = null)
    {
        Craft::info ( "Pic Puller media function" );
        $variables = [];

        $user_id = $tags['user_id'] ?? null;

        // user_id is required so check for it before proceeding
        if (!$user_id) {
            return $this->_missinguser_idErrorReturn ();
        }
        $use_stale_cache = $tags['use_stale_cache'] ?? $this->use_stale_cache;

        $oauth = $this->_getUserOauth ( $user_id );
        if (!$oauth) {
            return $this->_unauthorizedUserErrorReturn ();
        }

        // media_id is required for this function
        $media_id = isset( $tags['media_id'] ) ? $tags['media_id'] : '';
        if ($media_id == '') {
            $variables[] = [
                $this->_ig_picpuller_prefix.'code' => '000' ,
                $this->_ig_picpuller_prefix.'error_type' => 'MissingReqParameter' ,
                $this->_ig_picpuller_prefix.'error_message' => 'No media_id set for this function' ,
                $this->_ig_picpuller_prefix.'status' => false
            ];
            return $variables;
        }

        // set up the MEDIA url used by Instagram
        $query_string = "media/{$media_id}?access_token={$oauth}";
        $data = $this->_fetch_data ( $query_string , $use_stale_cache );

        if ($data['status'] != true) {
            // No images to return, even from cache, so exit the function and return the error
            // Set up the basic error messages returned by _fetch_data function
            $variables[] = [
                $this->_ig_picpuller_prefix.'code' => $data['code'] ? $data['code'] : '000' ,
                $this->_ig_picpuller_prefix.'error_type' => $data['error_type'] ,
                $this->_ig_picpuller_prefix.'error_message' => $data['error_message'] ,
                $this->_ig_picpuller_prefix.'status' => $data['status'] ? 1 : 0
            ];
            return $variables;
        }
        $cacheddata = isset( $data['cacheddata'] ) ? true : false;
        $node = $data['data'];

        // This is preparing a special version of the caption to return
        // Take the caption and remove everything after the beginning of the first
        // hashtag. This will be returned in a "caption_only" variable below.
        if (isset( $node['caption']['text'] )) {
            $caption = $node['caption']['text'];
            $titletohashpattern = '/^[^#]*(?!#)/';
            preg_match ( $titletohashpattern , $caption , $captionTitle );
        }

        // these are the hashtags for an Instagram post
        $tags = $node['tags'];

        $variables[] = [
            $this->_ig_picpuller_prefix.'type' => $node['type'] ,
            $this->_ig_picpuller_prefix.'video_low_bandwidth' => $node['videos']['low_bandwidth']['url'] ?? '' ,
            $this->_ig_picpuller_prefix.'video_low_bandwidth_width' => $node['videos']['low_bandwidth']['width'] ?? '' ,
            $this->_ig_picpuller_prefix.'video_low_bandwidth_height' => $node['videos']['low_bandwidth']['height'] ?? '' ,
            $this->_ig_picpuller_prefix.'video_low_resolution' => $node['videos']['low_resolution']['url'] ?? '' ,
            $this->_ig_picpuller_prefix.'video_low_resolution_width' => $node['videos']['low_resolution']['width'] ?? '' ,
            $this->_ig_picpuller_prefix.'video_low_resolution_height' => $node['videos']['low_resolution']['height'] ?? '' ,
            $this->_ig_picpuller_prefix.'video_standard_resolution' => $node['videos']['standard_resolution']['url'] ?? '' ,
            $this->_ig_picpuller_prefix.'video_standard_resolution_width' => $node['videos']['standard_resolution']['width'] ?? '' ,
            $this->_ig_picpuller_prefix.'video_standard_resolution_height' => $node['videos']['standard_resolution']['height'] ?? '' ,
            $this->_ig_picpuller_prefix.'username' => $node['user']['username'] ,
            $this->_ig_picpuller_prefix.'user_id' => $node['user']['id'] ,
            $this->_ig_picpuller_prefix.'full_name' => $node['user']['full_name'] ,
            $this->_ig_picpuller_prefix.'profile_picture' => $node['user']['profile_picture'] ,
            $this->_ig_picpuller_prefix.'created_time' => $node['created_time'] ,
            $this->_ig_picpuller_prefix.'link' => $node['link'] ,
            $this->_ig_picpuller_prefix.'caption' => $caption ?? '' ,
            $this->_ig_picpuller_prefix.'caption_only' => $captionTitle[0] ?? '' ,
            $this->_ig_picpuller_prefix.'tags' => isset( $tags ) ? $tags : [] ,
            $this->_ig_picpuller_prefix.'low_resolution' => $node['images']['low_resolution']['url'] ,
            $this->_ig_picpuller_prefix.'low_resolution_width' => $node['images']['low_resolution']['width'] ?? '' ,
            $this->_ig_picpuller_prefix.'low_resolution_height' => $node['images']['low_resolution']['height'] ?? '' ,
            $this->_ig_picpuller_prefix.'thumbnail' => $node['images']['thumbnail']['url'] ,
            $this->_ig_picpuller_prefix.'thumbnail_width' => $node['images']['thumbnail']['width'] ?? '' ,
            $this->_ig_picpuller_prefix.'thumbnail_height' => $node['images']['thumbnail']['height'] ?? '' ,
            $this->_ig_picpuller_prefix.'standard_resolution' => $node['images']['standard_resolution']['url'] ,
            $this->_ig_picpuller_prefix.'standard_resolution_width' => $node['images']['standard_resolution']['width'] ?? '' ,
            $this->_ig_picpuller_prefix.'standard_resolution_height' => $node['images']['standard_resolution']['height'] ?? '' ,
            $this->_ig_picpuller_prefix.'latitude' => $node['location']['latitude'] ?? '' ,
            $this->_ig_picpuller_prefix.'longitude' => $node['location']['longitude'] ?? '' ,
            $this->_ig_picpuller_prefix.'comment_count' => $node['comments']['count'] ,
            $this->_ig_picpuller_prefix.'likes' => $node['likes']['count'] ,
            $this->_ig_picpuller_prefix.'cacheddata' => $cacheddata ,
            $this->_ig_picpuller_prefix.'error_type' => $data['error_type'] ,
            $this->_ig_picpuller_prefix.'error_message' => $data['error_message'] ,
            $this->_ig_picpuller_prefix.'status' => $data['status']
        ];
        return $variables;
    }

    /**
     * Media Recent
     *
     * Get the user information from a specified Craft user that has authorized the Instagram application
     * https://www.instagram.com/developer/endpoints/media/#get_media
     *
     * @param null $tags
     *
     * @return array
     */
    public function media_recent ($tags = null)
    {
        Craft::info ( 'Pic Puller: media_recent' );
        $variables = [];
        $user_id = $tags['user_id'] ?? null;

        // user_id is required so check for it before proceeding
        if (!$user_id) {
            return $this->_missinguser_idErrorReturn ();
        }

        $use_stale_cache = $tags['use_stale_cache'] ?? $this->use_stale_cache;

        $limit = isset( $tags['limit'] ) ? $tags['limit'] : '';
        $limit = $tags['limit'] ?? null;

        if ($limit) {
            $limit = "&count=$limit";
        }

        $min_id = $tags['min_id'] ?? null;

        if ($min_id) {
            $min_id = "&min_id=$min_id";
        }

        $max_id = $tags['max_id'] ?? null;

        if ($max_id) {
            $max_id = "&max_id=$max_id";
        }

        $oauth = $this->_getUserOauth ( $user_id );

        if (!$oauth) {
            return $this->_unauthorizedUserErrorReturn ();
        }

        $ig_user_id = $this->_getInstagramId ( $user_id );
        // set up the MEDIA/RECENT url used by Instagram
        $query_string = "users/{$ig_user_id}/media/recent/?access_token={$oauth}".$limit.$max_id.$min_id;

        $data = $this->_fetch_data ( $query_string , $use_stale_cache );

        if ($data['status'] === false) {
            // No images to return, even from cache, so exit the function and return the error
            // Set up the basic error messages returned by _fetch_data function
            $variables[] = [
                $this->_ig_picpuller_prefix.'error_type' => $data['error_type'] ,
                $this->_ig_picpuller_prefix.'error_message' => $data['error_message'] ,
                $this->_ig_picpuller_prefix.'status' => $data['status']
            ];
            return $variables;
        }

        $next_max_id = '';
        if (isset( $data['pagination']['next_max_id'] )) {
            $next_max_id = $data['pagination']['next_max_id'];
        }

        $cacheddata = isset( $data['cacheddata'] ) ? true : false;

        foreach ($data['data'] as $node) {
            if (isset( $node['caption']['text'] )) {
                $caption = $node['caption']['text'];
                $titletohashpattern = '/^[^#]*(?!#)/';
                preg_match ( $titletohashpattern , $caption , $captionTitle );
            }

            $tags = $node['tags'];

            $variables[] = [
                $this->_ig_picpuller_prefix.'type' => $node['type'] ,
                $this->_ig_picpuller_prefix.'video_low_bandwidth' => $node['videos']['low_bandwidth']['url'] ?? '' ,
                $this->_ig_picpuller_prefix.'video_low_bandwidth_width' => $node['videos']['low_bandwidth']['width'] ?? '' ,
                $this->_ig_picpuller_prefix.'video_low_bandwidth_height' => $node['videos']['low_bandwidth']['height'] ?? '' ,
                $this->_ig_picpuller_prefix.'video_low_resolution' => $node['videos']['low_resolution']['url'] ?? '' ,
                $this->_ig_picpuller_prefix.'video_low_resolution_width' => $node['videos']['low_resolution']['width'] ?? '' ,
                $this->_ig_picpuller_prefix.'video_low_resolution_height' => $node['videos']['low_resolution']['height'] ?? '' ,
                $this->_ig_picpuller_prefix.'video_standard_resolution' => $node['videos']['standard_resolution']['url'] ?? '' ,
                $this->_ig_picpuller_prefix.'video_standard_resolution_width' => $node['videos']['standard_resolution']['width'] ?? '' ,
                $this->_ig_picpuller_prefix.'video_standard_resolution_height' => $node['videos']['standard_resolution']['height'] ?? '' ,
                $this->_ig_picpuller_prefix.'created_time' => $node['created_time'] ,
                $this->_ig_picpuller_prefix.'link' => $node['link'] ,
                $this->_ig_picpuller_prefix.'caption' => $caption ?? '' ,
                $this->_ig_picpuller_prefix.'caption_only' => $captionTitle[0] ?? '' ,
                $this->_ig_picpuller_prefix.'tags' => $tags ?? [] ,
                $this->_ig_picpuller_prefix.'low_resolution' => $node['images']['low_resolution']['url'] ,
                $this->_ig_picpuller_prefix.'low_resolution_width' => $node['images']['low_resolution']['width'] ?? '' ,
                $this->_ig_picpuller_prefix.'low_resolution_height' => $node['images']['low_resolution']['height'] ?? '' ,
                $this->_ig_picpuller_prefix.'thumbnail' => $node['images']['thumbnail']['url'] ,
                $this->_ig_picpuller_prefix.'thumbnail_width' => $node['images']['thumbnail']['width'] ?? '' ,
                $this->_ig_picpuller_prefix.'thumbnail_height' => $node['images']['thumbnail']['height'] ?? '' ,
                $this->_ig_picpuller_prefix.'standard_resolution' => $node['images']['standard_resolution']['url'] ,
                $this->_ig_picpuller_prefix.'standard_resolution_width' => $node['images']['standard_resolution']['width'] ?? '' ,
                $this->_ig_picpuller_prefix.'standard_resolution_height' => $node['images']['standard_resolution']['height'] ?? '' ,
                $this->_ig_picpuller_prefix.'latitude' => $node['location']['latitude'] ?? '' ,
                $this->_ig_picpuller_prefix.'longitude' => $node['location']['longitude'] ?? '' ,
                $this->_ig_picpuller_prefix.'media_id' => $node['id'] ,
                $this->_ig_picpuller_prefix.'next_max_id' => $next_max_id ,
                $this->_ig_picpuller_prefix.'comment_count' => $node['comments']['count'] ,
                $this->_ig_picpuller_prefix.'likes' => $node['likes']['count'] ,
                $this->_ig_picpuller_prefix.'cacheddata' => $cacheddata ,
                $this->_ig_picpuller_prefix.'error_type' => $data['error_type'] ,
                $this->_ig_picpuller_prefix.'error_message' => $data['error_message'] ,
                $this->_ig_picpuller_prefix.'status' => $data['status']
            ];
        }
        return $variables;
    }

    /**
     * @param $url
     * @param $use_stale_cache
     * From any other plugin file, call it like this:
     *
     *     PicPuller::$plugin->feed->_fetch_data($url, $use_stale_cache);
     *
     * @return array
     */
    private function _fetch_data ($url , $use_stale_cache)
    {
        $requestUrl = self::IG_API_URL.$url;

        $options = [
            'debug' => false ,
            'exceptions' => true ,
            'http_errors' => true ,
            'CURLOPT_RETURNTRANSFER' => 1 ,
            'CURLOPT_SSL_VERIFYPEER' => true ,
            'CURLOPT_TIMEOUT_MS' => 1000 ,
            'CURLOPT_NOSIGNAL' => 1 ,
        ];

        $client = new Client();

        try {
            $response = $client->request ( 'GET' , $requestUrl , $options );
            $body = Json::decodeIfJson ( $response->getBody () );
            return $this->_validate_data ( $body , $url , $use_stale_cache );
        } catch (RequestException $exception) {
            if ($exception->hasResponse ()) {
                $failedResponse = Json::decodeIfJson ( $exception->getResponse ()->getBody ()->getContents () );
                $failedResponse = $failedResponse['meta'];
                $error['status'] = false;
                $error['code'] = $failedResponse['code'] ? $failedResponse['code'] : 'unknown';
                $error['error_type'] = $failedResponse['error_type'] ? $failedResponse['error_type'] : 'HTTP_error';
                $error['error_message'] = $failedResponse['error_message'] ? $failedResponse['error_message'] : 'The Instagram API did not return a response.';
            } else {
                $error['status'] = false;
                $error['code'] = '503';
                $error['error_type'] = 'HTTP_error';
                $error['error_message'] = $e->getMessage () ? $e->getMessage () : 'The Instagram API did not return a response.';
            }

            return $error;
        }
    }

    /**
     * Validate Data
     *
     * Validate that data coming in from an Instagram API call is valid data and respond with that data plus error_state details
     *
     * @access  private
     *
     * @param   string - the data to validate
     * @param   string - the URL that generated that data
     *
     * @return  array - the original data or cached data (if stale allowed) with the error array
     */

    private function _validate_data ($data , $url , $use_stale_cache)
    {
        $meta = $data['meta'];
        // meta > code equal to 200 means we have a successful request returned
        if ($meta['code'] == 200) {
            // There is an outlying chance that IG says 200, but the data array is empty.
            // Pic Puller considers that an error so it returns a custom error message
            if (count ( $data['data'] ) == 0) {
                $error_array = [
                    'code' => $meta['code'] ,
                    'status' => false ,
                    'error_message' => "There was no media to return for that user." ,
                    'error_type' => 'NoData'
                ];
            } else {
                $error_array = [
                    'code' => $meta['code'] ?? '000' ,
                    'status' => true ,
                    'error_message' => "Nothing wrong here. Move along." ,
                    'error_type' => 'NoError'
                ];
                // Fresher valid data was received, so update the cache to reflect that.
                $this->_write_cache ( $data , $url );
            }
        } else // meta code was not 200, there's something not quite right going on...
        {
            // can we use stale cache for this request? if so, let's proceed
            if ($use_stale_cache) {
                $data = $this->_check_cache ( $url );

                // were we were able to find cached data?
                if ($data) {
                    // since we retrieved cached data and are returning that cached data,
                    // set up the appropriate return values to let user know
                    $data['cacheddata'] = true;
                    $data['code'] = $meta['code'] ?? '000';
                    $error_array = [
                        'status' => true ,
                        'code' => $meta['code'] ?? '000' ,
                        'error_message' => $meta['error_message'] ?? 'No data returned from Instagram API. Check http://api-status.com/6404/174981/Instagram-API. Using cached data.' , //. ' Using stale data as back up if available.',
                        'error_type' => $meta['error_type'] ?? 'NoCodeReturned'
                    ];
                } else {
                    // we were not able to find cached data, set up the appropriate return values to let use know
                    $data = [];
                    $data['cacheddata'] = false;
                    $data['code'] = $meta['code'] ?? '000';
                    $error_array = [
                        'status' => false ,
                        'code' => $meta['code'] ?? '000' ,
                        'error_message' => $meta['error_message'] ?? 'No error message provided by Instagram. No cached data available.' ,
                        'error_type' => $meta['error_type'] ?? 'NoCodeReturned'
                    ];
                }
            } else // we can't use stale cache due to the user setting use_stale_cache to false
            {
                $data['cacheddata'] = false;
                $data['code'] = $meta['code'] ?? '000';
                $error_array = [
                    'status' => false ,
                    'code' => $meta['code'] ?? '000' ,
                    'error_message' => $meta['error_message'] ?? 'No error message provided by Instagram. No cached data available.' ,
                    'error_type' => $meta['error_type'] ?? 'NoCodeReturned'
                ];
            }
        }

        // merge the original data or cached data (if stale allowed) with the error array
        $returnedData = array_merge ( $data , $error_array );

        return $returnedData;
    }

    /**
     * This is a replacement for the old checking of cache using Yii2 built in
     * behavior
     *
     * @param $url     the URL is the $key that will be passed into the getOrSet
     *                 function
     */
    private function _check_cache ($url)
    {
        $cache = Craft::$app->getCache ();
        $key = md5 ( $url );
        $data = $cache->get ( $key );

        return Json::decodeIfJson ( $data );
    }


    private function _write_cache ($data , $url)
    {
        $cache = Craft::$app->getCache ();
        $key = md5 ( $url );
        $cache->set ( $key , $data );
    }

    /**
     * Get user oAuth by Craft user ID
     *
     * @param  INT $craft_user_id Craft user ID
     *
     * @return string     oAuth code for Instagram user for this app
     */
    private function _getUserOauth ($craft_user_id)
    {
        return PicPuller::$plugin->appManagement->getUserOauthValue ( $craft_user_id );
    }

    /**
     * @param INT $craft_user_id
     *
     * @return string
     */
    private function _getInstagramId ($craft_user_id)
    {
        return PicPuller::$plugin->appManagement->getInstagramId ( $craft_user_id );
    }

    /**
     * A single function to return a consistent error message when a Craft user_id has not been supplied to a function
     *
     * @return array error_type, error_message, and status
     */
    private function _missinguser_idErrorReturn ()
    {
        $variables[] = [
            $this->_ig_picpuller_prefix.'code' => '000' ,
            $this->_ig_picpuller_prefix.'error_type' => 'MissingReqParameter' ,
            $this->_ig_picpuller_prefix.'error_message' => 'No user ID set for this function' ,
            $this->_ig_picpuller_prefix.'status' => false
        ];
        return $variables;
    }

    /**
     * A single function to return a consistent error message when a Craft user hasn't authorized Pic Puller
     *
     * @return array error_type, error_message, and status
     */
    private function _unauthorizedUserErrorReturn ()
    {
        $variables[] = [

            $this->_ig_picpuller_prefix.'code' => '000' ,
            $this->_ig_picpuller_prefix.'error_type' => 'UnauthorizedUser' ,
            $this->_ig_picpuller_prefix.'error_message' => 'User has not authorized Pic Puller for access to Instagram.' ,
            $this->_ig_picpuller_prefix.'status' => false
        ];

        return $variables;
    }
}
