<?php

require_once 'Mage/Customer/controllers/AccountController.php';
class Chalkfly_OAuthRedirect_AccountController extends Mage_Customer_AccountController
{
    protected function _loginPostRedirect()
    {
        $session = $this->_getSession();
        //if this redirect is a result of the OAuth process, force the redirect
        if(stristr($session->getBeforeAuthUrl(),"oauth/authorize?oauth_token=") == 0){
            $this->_redirectUrl($session->getBeforeAuthUrl(true));
        } else {
            parent::_loginPostRedirect();
        }
    } 
}