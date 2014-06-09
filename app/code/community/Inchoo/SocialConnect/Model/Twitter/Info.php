<?php
/**
* Inchoo
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@magentocommerce.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Please do not edit or add to this file if you wish to upgrade
* Magento or this extension to newer versions in the future.
** Inchoo *give their best to conform to
* "non-obtrusive, best Magento practices" style of coding.
* However,* Inchoo *guarantee functional accuracy of
* specific extension behavior. Additionally we take no responsibility
* for any possible issue(s) resulting from extension usage.
* We reserve the full right not to provide any kind of support for our free extensions.
* Thank you for your understanding.
*
* @category Inchoo
* @package SocialConnect
* @author Marko Martinović <marko.martinovic@inchoo.net>
* @copyright Copyright (c) Inchoo (http://inchoo.net/)
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
*/

class Inchoo_SocialConnect_Model_Twitter_Info extends Varien_Object
{
    protected $params = array(
        'skip_status' => true
    );

    /**
     * Twitter client model
     *
     * @var Inchoo_SocialConnect_Model_Twitter_Oauth_Client
     */
    protected $client = null;

    public function __construct() {
        $this->client = Mage::getSingleton('inchoo_socialconnect/twitter_oauth_client');
        if(!($this->client->isEnabled())) {
            return $this;
        }
    }

    /**
     * Get Twitter client model
     *
     * @return Inchoo_SocialConnect_Model_Twitter_Oauth_Client
     */
    public function getClient()
    {
        return $this->client;
    }

    public function setClient(Inchoo_SocialConnect_Model_Twitter_Oauth_Client $client)
    {
        $this->client = $client;
    }

    public function setAccessToken($token)
    {
        $this->client->setAccessToken($token);
    }

    /**
     * Get Twitter client's access token
     *
     * @return stdClass
     */
    public function getAccessToken()
    {
        return $this->client->getAccessToken();
    }

    public function load($id = null)
    {
        $this->_load();
        
        // Twitter doesn't allow email access trough API
        $this->setEmail(sprintf('%s@twitter-user.com', strtolower($this->getScreenName())));

        return $this;
    }

    protected function _load()
    {
        try{
            $this->client->getAccessToken();
            
            $response = $this->client->api(
                '/account/verify_credentials.json',
                'GET',
                $this->params
            );

            foreach ($response as $key => $value) {
                $this->{$key} = $value;
            }

        } catch(Inchoo_SocialConnect_Twitter_Oauth_Exception $e) {
            $this->_onException($e);
        } catch(Exception $e) {
            $this->_onException($e);
        }
    }

    protected function _onException($e)
    {
        if($e instanceof Inchoo_SocialConnect_Twitter_Oauth_Exception) {
            Mage::getSingleton('core/session')->addNotice($e->getMessage());
        } else {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
    }

}