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
            'new_tab' => '_redirect_posts_new_tab',
            'error_key' => '_redirect_posts_err_%s_%s'
        ));

        $config->add('opt_redirect', '_gw_redirect_posts_slug');
        $config->add('redirect_slug', \get_option($config->opt_redirect, 'partner-posts/'));
        $config->add('shortcode', 'redirect_widget');
        $this->config = $config;
    }

    public function install() {
        $redirect_slug = \get_option($this->config->opt_redirect);
        if (!$redirect_slug) {
            \update_option($this->config->opt_redirect, $this->config->redirect_slug);
        }
    }

    public function uninstall() {

    }

    public function listen() {
        \register_activation_hook($this->config->plugin_file, array($this, 'install'));
        \register_deactivation_hook($this->config->plugin_file, array($this, 'uninstall'));

        // init here
        \add_action('wp', array($this, 'redirectPost'), 10);
        \add_action('init', array($this, 'addShortcode'), 10);
        \add_action('wp_enqueue_scripts', array($this, 'addAssets'), 10);

        // handle the permalink
        \add_action('post_link', array($this, 'link'), 20, 2);
        \add_action('post_type_link', array($this, 'link'), 20, 2);

        // append to content on post page
        \add_filter('the_content', array($this, 'addContent'));
    }

    public function getLink($post_id) {
        $url = \get_post_meta(absint($post_id), $this->config->meta['redirect_url'], true);
        return empty($url) ? false : $url;
    }

    public function addContent($content) {
        $post = $GLOBALS['post'];
        $redirect_url = \get_post_meta($post->ID, $this->config->meta['redirect_url'], true);

        if (\is_singular() && $redirect_url) {

            $output = '<div class="gw-redirectposts-widget">';
            $output .= <<<EOF
<div class="redirect-link">
    <a href="%s" target="_blank" class="btn btn-primary">%s</a>
</div>
EOF;
            $output = sprintf($output, $redirect_url, __('Continue Reading', $this->config->domain));
            $output .= '</div>';
            $content .= $output;
        }

        return $content;
    }

    public function link($link, $post) {
        $post = \get_post($post);
        $meta_link = $this->getLink($post->ID);
        $new_tab = \get_post_meta($post->ID, $this->config->meta['new_tab'], true);

        if ($meta_link) {
            if (!is_admin()) {
                $link = \add_query_arg('follow', '1', $link);
                if ($new_tab) {
                    $link .= '#new_tab';
                }
            }
        }

        return $link;
    }


    public function redirectPost() {
        if (\is_singular()) {
            $follow = isset($_GET['follow']) && $_GET['follow'] == 1;
            $post = $GLOBALS['wp_query']->posts[0];
            $redirect_url = \get_post_meta($post->ID, $this->config->meta['redirect_url'], true);
            if (!empty($redirect_url) && $follow) {
                // BE SURE THIS STAYS AS 302!!!
                // otherwise, browser cache will not pick up future changes
                \wp_redirect($redirect_url, 302); 
            }
        }
    }

    public function addShortcode() {
        \add_shortcode($this->config->shortcode, array($this, 'renderShortcode'));
    }

    public function addAssets() {
        wp_register_style('gw_redirectposts_widget.css', $this->config->plugin_uri . 'assets/css/widget.css', array(), $this->config->version);
        wp_enqueue_style('gw_redirectposts_widget.css');

        wp_register_script('gw_redirectposts.js', $this->config->plugin_uri . 'assets/js/main.js', array('jquery'), $this->config->version);
        wp_enqueue_script('gw_redirectposts.js');
    }

    public function renderShortcode($attr) {
        // we depend on post ID being in the query string
        $post_id = isset($_GET['post']) ? \absint($_GET['post']) : null;
        $redirect_url = \get_post_meta($post_id, $this->config->meta['redirect_url'], true);

        $output = '<div class="gw-redirectposts-widget">';
        if (null !== $post_id && !empty($redirect_url)) {
            $output .= <<<EOF
<div class="redirect-link">
    <a href="%s" target="_blank" class="btn btn-primary">%s</a>
</div>
EOF;
            $output = sprintf($output, $redirect_url, __('Continue Reading', $this->config->domain));
        } else {
            $output .= <<<EOF
<div class="no-post">
    <p>Invalid request</p>
</div>
EOF;
        }
        $output .= '</div>';

        return $output;
    }

    public function modules() {
        $this->admin = Admin::instance();
        $this->view = View::instance();
    }
}
endif;
