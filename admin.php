<?php
/**
 * Admin settings view for Fundamentet Api.
 */

namespace Frojd\Plugin\FundamentetApi;


class Admin {
    protected $plugin;

    protected $pluginBase;
    protected $pluginRelBase;

    function __construct ($plugin) {
        $this->plugin = $plugin;
        $this->pluginBase = rtrim(dirname(__FILE__), '/');
        $this->pluginRelBase = dirname(plugin_basename(__FILE__));

        if (is_multisite()) {
            add_action('network_admin_menu', array($this, 'networkAdminMenuHook'));
        } else {
            add_action('admin_menu', array($this, 'adminMenuHook'));
        }
    }

    /*------------------------------------------------------------------------*
     * Hooks
     *------------------------------------------------------------------------*/

    public function networkAdminMenuHook() {
        add_submenu_page('settings.php',
            __('VisitSweden Fundament API'),
            __('VisitSweden Fundament API'),
            'manage_options',
            'visitsweden-fundament-api-settings',
            array($this, 'settings_page')
        );
    }

    public function adminMenuHook() {
        add_options_page(
            __('Fundament API'),
            __('Fundament API'),
            'manage_options',
            'fundamentet-api-settings',
            array($this, 'settingsPage')
        );
    }

    /*------------------------------------------------------------------------*
     * Public
     *------------------------------------------------------------------------*/

    public function settingsPage() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && ! empty($_POST)) {
            if (! wp_verify_nonce($_POST['nonce'], 'fundamentet-api-settings')) {
                wp_die("Save failed");
            }

            // Add
            $this->plugin->setSettings(array(
                'url' => $_POST['url'],
                'key' => $_POST['key'],
                'secret' => $_POST['secret'],
                'use_proxy' => isset($_POST['use_proxy']) ? "1" : "0",
                'proxy_url' => $_POST['proxy_url'],
                'proxy_port' => $_POST['proxy_port'],
            ));
        }

        $settings = $this->plugin->getSettings();
        $this->renderTemplate("admin", array(
            'settings' => $settings
        ));
    }

    /*------------------------------------------------------------------------*
     * Private
     *------------------------------------------------------------------------*/

    public function renderTemplate($name, $vars=array()) {
        foreach ($vars as $key => $val) {
            $$key = $val;
        }


        $pluginBase = rtrim(dirname(__FILE__), '/');

        $templatePath = $this->pluginBase.'/templates/'.$name.'.php';
        if (file_exists($templatePath)) {
            include($templatePath);
        } else {
            echo '<p>Rendering of admin template failed</p>';
        }
    }

}
