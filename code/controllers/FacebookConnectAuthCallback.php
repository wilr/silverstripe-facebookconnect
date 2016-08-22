<?php

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;

/**
 * @package facebookconnect
 */
class FacebookConnectAuthCallback extends Controller
{

    private static $allowed_actions = array(
        'connect'
    );

    public function connect()
    {
        // check we have a valid session
        $appId = Config::inst()->get(
            'FacebookControllerExtension', 'app_id'
        );

        $secret = Config::inst()->get(
            'FacebookControllerExtension', 'api_secret'
        );

        $session = $this->getFacebookHelper()->getSessionFromRedirect();

        if ($session) {
            $token = $session->getAccessToken();

            // get a long lived token by default. Access token is saved in
            // session.
            try {
                $long = $token->extend($appId, $secret);

                if ($long) {
                    $accessTokenValue = (string) $long;
                } else {
                    $accessTokenValue = (string) $token;
                }
            } catch (Exception $e) {
                $accessTokenValue = (string) $token;
            }

            try {
                Session::set(
                    FacebookControllerExtension::FACEBOOK_ACCESS_TOKEN,
                    $accessTokenValue
                );


                $fields = Config::inst()->get(
                    'FacebookControllerExtension', 'facebook_fields'
                );

                $user = (new FacebookRequest(
                    $session, 'GET', '/me', array('fields' => implode(',', $fields))
                ))->execute()->getGraphObject(GraphUser::className());

                if (!$member = Member::currentUser()) {
                    // member is not currently logged into SilverStripe. Look up
                    // for a member with the UID which matches first.
                    $member = Member::get()->filter(array(
                        "FacebookUID" => $user->getId()
                    ))->first();

                    if (!$member) {
                        // see if we have a match based on email. From a
                        // security point of view, users have to confirm their
                        // email address in facebook so doing a match up is fine
                        $email = $user->getProperty('email');

                        if ($email) {
                            $member = Member::get()->filter(array(
                                'Email' => $email
                            ))->first();
                        }
                    }

                    if (!$member) {
                        $member = Injector::inst()->create('Member');
                    }
                }

                $member->syncFacebookDetails($user);
                $member->logIn();

                // redirect the user to the provided url, otherwise take them
                // back to the route of the website.
                if ($url = Session::get(FacebookControllerExtension::SESSION_REDIRECT_URL_FLAG)) {
                    return $this->redirect($url);
                } else {
                    return $this->redirect(Director::absoluteBaseUrl());
                }
            } catch (Exception $e) {
                SS_Log::log($e, SS_Log::ERR);
            }
        } else {
            return $this->httpError(400);
        }

        return $this->httpError(400);
    }
}
