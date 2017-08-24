<?php
namespace GW\RedirectPosts;

class View {
    static public function render($name, array $args = array()) {
        $args = apply_filters('gw_redirect_posts-view_arguments', $args, $name);

        $config = Config::instance(GW_REDIRECTPOSTS_PLUGIN_NAME);
        foreach ($args as $key => $val) {
            $$key = $val;
        }

        \load_plugin_textdomain($config->domain);

        $file = $config->plugin_path . 'views/' . $name . '.php';
        include $file;
    }

    static public function instance() {
        static $instance;
        if (null === $instance) {
            $instance = new View();
            $config = Config::instance(GW_REDIRECTPOSTS_PLUGIN_NAME);
            $instance->config = $config;

            $instance->listen();
        }
        return $instance;
    }

    public function loadResources() {
        wp_register_style('font-awesome', $this->config->plugin_uri . 'assets/font-awesome/css/font-awesome.min.css', array(), $this->config->version);
        wp_register_style('gw_redirectposts.css', $this->config->plugin_uri . 'assets/css/main.css', array('font-awesome'), $this->config->version);
        wp_enqueue_style('gw_redirectposts.css');

        wp_register_script('gw_redirectposts_admin.js', $this->config->plugin_uri . 'assets/js/admin.js', array(), $this->config->version);
        wp_enqueue_script('gw_redirectposts_admin.js');
    }

    private function listen() {
        add_action('admin_enqueue_scripts', array($this, 'loadResources'));
    }
}
