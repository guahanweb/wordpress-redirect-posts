<?php
namespace GW\RedirectPosts;

if (!class_exists('\GW\RedirectPosts\Plugin')):

define('GW_REDIRECTPOSTS_PLUGIN_NAME', '\GW\RedirectPosts\Plugin');

class Plugin {
    static public function instance($base = null) {
        static $instance;
        if (null === $instance) {
            $instance = new Plugin();
            $instance->configure($base);
            $instance->listen();
            $instance->modules();
        }
        return $instance;
    }

    public function configure($base) {
        global $wpdb;

        if (null === $base) {
            $base = __FILE__;
        }

        $config = Config::instance(GW_REDIRECTPOSTS_PLUGIN_NAME);
        $config->add('domain', 'gw');
        $config->add('min_version', '4.1');

        $config->add('basename', \plugin_basename(\plugin_dir_path($base) . 'gw-redirect-posts.php'));
        $config->add('plugin_file', $base);
        $config->add('plugin_uri', \plugin_dir_url($base));
        $config->add('plugin_path', \plugin_dir_path($base));

        $config->add('meta', array(
            'redirect_url' => '_redirect_posts_redirect_url',
            'error_key' => '_redirect_posts_err_%s_%s'
        ));

        $this->config = $config;
    }

    public function install() {

    }

    public function uninstall() {

    }

    public function listen() {
        \register_activation_hook($this->config->plugin_file, array($this, 'install'));
        \register_deactivation_hook($this->config->plugin_file, array($this, 'uninstall'));

        // admin
        \add_action('admin_init', array($this, 'adminInit'));

        // init here
        \add_action('wp', array($this, 'redirectPost'));
    }

    public function adminInit() {
        $this->admin = Admin::instance();
    }

    public function redirectPost() {
        if (\is_singular()) {
            $post = $GLOBALS['wp_query']->posts[0];
            $redirect_url = \get_post_meta($post->ID, $this->config->meta['redirect_url'], true);
            if (!empty($redirect_url)) {
                // BE SURE THIS STAYS AS 302!!!
                // otherwise, browser cache will not pick up future changes
                \wp_redirect($redirect_url, 302); 
            }
        }
    }

    public function modules() {

    }
}
endif;
