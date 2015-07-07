<?php
/**
 *
 * @package   Fundamentet API
 * @author    Fröjd
 * @license   Fröjd Interactive AB (All Rights Reserved).
 * @link      http://frojd.se
 * @copyright Fröjd Interactive AB (All Rights Reserved).
 *
 * Plugin Name: Fundamentet API
 * Plugin URI:  http://frojd.se
 * Description: Fundament API methods and connections.
 * Version:     1.0.0
 * Author:      Fröjd - Martin Sandström
 * Author URI:  http://frojd.se
 * License:     Fröjd Interactive AB (All Rights Reserved).
 */

namespace Frojd\Plugin\FundamentetApi;

// Import OAuth library
if (! class_exists('OAuthStore')) {
    include dirname(__FILE__).'/libs/oauth-php/library/OAuthStore.php';
    include dirname(__FILE__).'/libs/oauth-php/library/OAuthRequester.php';
}

if (! class_exists("Frojd\Plugin\FundamentetApi\Admin")) {
    include dirname(__FILE__).'/admin.php';
}



class FundamentetApi {
    const VERSION = '1.0';

    protected $pluginSlug = 'fundament_api';
    protected static $instance = null;

    protected $pluginBase;
    protected $pluginRelBase;

    private $settingKeys = array('url', 'key', 'secret', 'use_proxy',
        'proxy_url', 'proxy_port');

    protected $settings = null;

    private function __construct() {
        $this->pluginBase = rtrim(dirname(__FILE__), '/');
        $this->pluginRelBase = dirname(plugin_basename(__FILE__));

        register_activation_hook(__FILE__, array(&$this, 'activationHook'));
        register_activation_hook(__FILE__, array(&$this, 'deactivationHook'));
        register_uninstall_hook(__FILE__, array(get_class(), 'uninstallHook'));

        add_action('init', array($this, 'initHook'));
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /*------------------------------------------------------------------------*
     * Hooks
     *------------------------------------------------------------------------*/

    public function activationHook($networkWide) {

    }

    public function deactivationHook($networkWide) {
    }

    public static function uninstallHook($networkWide) {
        if (! defined('WP_UNINSTALL_PLUGIN')) {
            die();
        }
    }

    public function initHook() {
        new Admin($this);
    }

    /*------------------------------------------------------------------------*
     * Public
     *------------------------------------------------------------------------*/

    public function getRequest($uri, $params=array(), $options=array()) {
        return $this->request($uri, "GET", $params, null, null, null, $options);
    }

    public function postRequest($uri, $params=array(), $json=false, $body=null,
        $files=null, $options=array()) {

        return $this->request($uri, "POST", $params, $body, $files, $json, $options);
    }

    // TODO: Add PUT method

    public function deleteRequest($uri, $options=array()) {
        return $this->request($uri, "DELETE", null, null, null, null, $options);
    }

    public function setSettings($data) {
        $settings = array();

        foreach ($data as $key => $value) {
            if (! in_array($key, $this->settingKeys)) {
                continue;
            }

            update_site_option($this->pluginSlug."_".$key, $value);
            $settings[$key] = $value;
        }

        $this->settings = $settings;
    }

    public function getSettings() {
        if (! empty($this->settings)) {
            return $this->settings;
        }

        $settings = array();

        foreach ($this->settingKeys as $key) {
            $settings[$key] = get_site_option($this->pluginSlug."_".$key, "");
        }

        $this->settings = $settings;
        return $settings;
    }

    /*------------------------------------------------------------------------*
     * Private
     *------------------------------------------------------------------------*/

    private function request($uri, $method, $params=null, $body=null, $files=null,
        $json=false, $options=array()) {

        $settings = $this->getSettings();
        $url = rtrim($settings['url'], '/');
        $url = $url."/".$uri;
        $curlOpt = array();

        // Run pre hook, check if data is available, and return it.
        if (has_filter('pre_fundamentet_api_request')) {
            $pre_data = apply_filters("pre_fundamentet_api_request",
                $uri, $method, $params, $body, $files, $json, $options);

            if ($pre_data) {
                return $pre_data;
            }
        }

        $oauthOptions = array(
            'consumer_key' => $settings['key'],
            'consumer_secret' => $settings['secret']
        );

        \OAuthStore::instance('2Leg', $oauthOptions);

        try {
            $curlOpt = array();

            if ($settings["use_proxy"]) {
                $curlOpt[CURLOPT_PROXY] = $settings["proxy_url"];
                $curlOpt[CURLOPT_PROXYPORT] = $settings["proxy_port"];
            }

            if ($json) {
                $body = json_encode($body);

                $curlOpt[CURLOPT_HTTPHEADER] = array(
                    "Content-Type: application/json",
                );
            }

            // Obtain a request object for the request we want to make
            $request = new \OAuthRequester($url, $method, $params, $body, $files);

            // Sign the request, perform a curl request and return the results,
            // throws OAuthException2 exception on an error
            // $result is an array of the form: array
            // ('code'=>int, 'headers'=>array(), 'body'=>string)
            $response = $request->doRequest(0, $curlOpt);

            $result = $response["body"];

            $json_result = json_decode($result, true);

            $api_response = array(
                'success' => true,
                'response' => $response,
                'data' => $json_result
            );

            // Run post request hook
            if (has_filter('post_fundamentet_api_request')) {
                apply_filters("post_fundamentet_api_request", $uri, $method,
                    $params, $body, $files, $options, $api_response);
            }

            return $api_response;

        } catch(\OAuthException2 $e) {
            return array(
                'success' => false,
                'exception' => array(
                    'type' => $e->getMessage()
                )
            );
        }

        return true;
    }
}

FundamentetApi::getInstance();

