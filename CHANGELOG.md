# Pic Puller Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/) and this project adheres to [Semantic Versioning](http://semver.org/).

## 3.0.13.2 - 2018-08-20
### Fixed
- Added change log to previous release.
- Updated composer.json
- It's one of those days 

## 3.0.13.1 - 2018-08-20
### Fixed
- Fixed variable name for use in Stats Widget. 

## 3.0.13 - 2018-08-20
### Fixed
- Fixed reporting of Instagram API limit error. 

## 3.0.12 - 2018-08-20
### Fixed
- Feed service was failing when Instagram Authorization had been removed a user's Instagram account.
- Control panel fixed to removed incorrect Twig template display of errors. 

## 3.0.11 - 2018-08-02
### Fixed
- Users of Craft Pro were not seeing the additional options for multiple users. Pic Puller now checks.

## 3.0.10 - 2018-06-13
### Fixed
- Previous fix didn't address Instagram not responding to request at all. This updated addresses that.
- Added "code" returned to both successful and unsuccessful Pic Puller calls.

## 3.0.9 - 2018-04-04
### Fixed
- Instagram feed has been missing "meta" object for some users. This update checks for the presence of "meta". If missing it will attempt to serve stale cache if available. 

## 3.0.8 - 2018-03-15
### Fixed
- License text fix.

## 3.0.7 - 2018-03-15
### Fixed
- Applied the Craft license.

## 3.0.6 - 2018-03-07
### Fixed
- The /services/AppManagement.php wasn't using the table prefix for the `users` table and has been updated to fix this bug.

## 3.0.5 - 2018-02-19
### Fixed
- The /services/AppManagement.php wasn't using the table prefix for the `picpuller_authorizations` table and has been updated to fix this bug.

## 3.0.4 - 2017-12-08
### Fixed
- Removed unneeded 2nd copy of License file

## 3.0.3 - 2017-12-08
### Fixed
- Tweak to composer.json

## 3.0.2 - 2017-12-08
### Fixed
- A fix for the previously botched fixed for the authentication JS.

## 3.0.0 - 2017-08-02
### Added
- Initial release

## 2.3.5 (Aug 28, 2017)
### Fixed
- Fixed error where Users tab was showing up for non-Pro Craft editions, which caused a Twig error. Only Craft Pro Edition can share oAuth

## 2.3.4 (July 17, 2017)
### Fixed
- FeedService array syntax changed back to old array sytax for PHP 5.3.x compatibility

## 2.3.3 (June 24, 2017)
### Fixed
- The feed service now handled bad URLs calls to Instagram and returns errors instead of simply failing. 

## 2.3.2 (Aug 14, 2016)
### Fixed
- Data validation updated to catch additional error possibility. (Thanks, Marion Newlevant!)

## 2.3.1 (June 27, 2016)
### Fixed
- Fixed 404 errors on the settings page.

## 2.3.0 (June 21, 2016)
### Fixed
- Added back the unintentionally deleted "shared authorization" feature of Pic Puller. You can now share one Admin user's Instagram 
Authorization across all users of your site.

## 2.2.0 (May 25, 2016)
### Added 
- Pic Puller now supports [CSRF Protection](https://craftcms.com/support/csrf-protection), an optional security setting in Craft CMS, when making a request for Instagram access.

## 2.1.0 (April 11, 2016)
### Added 
- New "caption_only" variable has been added to "media_recent" and "media" (by ID) functions. It will eliminate captions that are stuffed with hashtags from being so damn ugly.
- New "tags" variable has been added to "media_recent" and "media" (by ID) functions. This is an array of the tags associated with a piece of media.

### Fixed
- The fieldtype talked too much in the console. It has had a stern talking to about this and will now be quiet. 

## 2.0.0 (March 10, 2016)
### Fixed
- Pic Puller 2 is released
- Instagram authorization is for the **Pic Puller 2** application on Instagram

### Fixed
- Access limited to an authorized Instagram user's media instead of the full scope of public media on Instagram as was the case in version 1 of Pic Puller

## 1.6.0 (Nov 28, 2015)
### Deprecated
- Instagram has removed the Popular Feed from their API and it has now been removed from Pic Puller. See: http://developers.instagram.com/post/133424514006/instagram-platform-update for more information.
- Instagram has removed the User Feed from their API and it has now been removed from Pic Puller. This is the feed you see in the Instagram app on your phone, **not** the *media\_recent* feed of images taken by a single user.

### Fixed
- Pic Puller has been updated for Craft 2.5 with new icons, links to the online documentation.

### Improved
- Some of the language in the app has been updated for clarity.

## 1.5.0 (Oct 11, 2015)
### Added
- An API change from Instagram allowing for non-square images and videos to be posted added height and width values to data it returns to developers. This data has been added to Pic Puller. See: http://developers.instagram.com/post/128288227716/api-migration-for-landscape-and-portrait-formats for more information.

### Added
- Added the low bandwidth video information to Pic Puller. Look for *video\_low\_bandwidth*, *video\_low\_bandwidth\_width* and *video\_low\_bandwidth\_height* in the documentation.

## 1.4.0 (June 5, 2015)
### Added
- "media\_recent" and "user". Passing in an Instagram ID in the 'ig\_user\_id' parameter will allow you to pull another user's *public* feed.

## 1.3.4 (April 24, 2015)
### Fixed
- Changed CURL cache options to address some users receiving time out errors when accessing Instagram feed.
- Improved the Authenticated User screen in Pic Puller to display the Instagram account info for the authorized account. This should help users spot when previously working oAuth tokens are deauthorized by Instagram.

## 1.3.3 (March 23, 2015)
### Fixed
- Fixed errors present in "free" and "client" versions of Craft seen when devMode was set to true. Now settings page of Pic Puller will only display list of admins for shared oAuth when using pro version of Craft.

## 1.3.2 (March 12, 2015)
### Fixed
- The Instagram feed has removed "webiste" from all but the user API call. The "website" value has been removed from Pic Puller as well since it is no longer available.

## 1.3.1 (Dec 6, 2014)
### Fixed
- Fixed various array syntax instances for compatibility with PHP 5.3
- Fixed error in "Lastest Instagram Image" widget that would cause errors when Instagram app was not authorized with Instagram
- Fixed PHP 5.3 issue where plugin name change was not saved to database

## 1.3.0 (Dec 2, 2014)
### Added
- Added new setting allowing a single Instagram authorization by an admin to be shared across all uses in the Pic Puller settings

### Fixed
- Fixed field type JSON error generated by image captions that included line breaks

## 1.2.2 (Nov 24, 2014)
### Fixed
- Fixed error in field type where JSON data wasn't being defined as JSON in header

## 1.2.1 (Nov 13, 2014)
### Added
- Initial release for sale
- Fixed issue where customized plug in names being blank did not default to the full name of the plug in.
- Cleaned up documentation

## 1.2 RC (Nov 9, 2014)
### Added
- Release candidate

## 1.1 (beta) (Nov 4, 2014)
### Added
- Initial beta release of Pic Puller
