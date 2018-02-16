<?php
namespace GW\RedirectPosts;

if (!class_exists('\GW\RedirectPosts\Admin')):

class Admin {
    static public function instance() {
        static $instance;
        if (null === $instance) {
            $config = Config::instance(GW_REDIRECTPOSTS_PLUGIN_NAME);
            $instance = new Admin();
            $instance->config = $config;
            $instance->setup();
            $instance->listen();
        }
        return $instance;
    }

    public function setup() {
        $this->meta_box_name = 'redirect_posts_meta_box';
        $this->nonce_name = 'redirect_posts_nonce';
    }

    public function listen() {
        // admin  menu
        \add_action('admin_menu', array($this, 'setupAdminMenu'), 5);
        
        // render and process
        \add_action('add_meta_boxes', array($this, 'addPostMetaBox'), 10, 2);
        \add_action('save_post', array($this, 'savePostMetaBox'), 10, 2);
        \add_action('admin_notices', array($this, 'displayErrors'), 10);

        // alert if pages are redirecting
        \add_action('load-post.php', array($this, 'loadPost'), 10);
    }

    public function setupAdminMenu() {
        $this->addPluginMenu();
    }

    public function addPluginMenu() {
        add_menu_page(__('Redirect Posts', $this->config->domain), __('Redirect Posts', $this->config->domain), 'manage_options', 'gw-redirect-posts-home', array($this, 'renderDashboard'), 'dashicons-external', 30);
        add_submenu_page('gw-redirect-posts-home', __('Settings', $this->config->domain), __('Settings', $this->config->domain), 'manage_options', 'gw-redirect-posts-home', array($this, 'renderDashboard'));
    }

    public function renderDashboard() {
        $page = new Pages\Dashboard($this->config);
        $page->process();
        $page->render();
    }

    public function adminHelp() {

    }

    public function getPageUrl($page = 'config') {
        $args = array('page' => 'gw-redirect-posts-config');
        // if custom page is needed, modify query params here
        $url = \add_query_arg($args, \admin_url('options-general.php'));
        return $url;
    }

    public function getLink($post_id) {
        $url = \get_post_meta(absint($post_id), $this->config->meta['redirect_url'], true);
        return empty($url) ? false : $url;
    }

    public function loadPost() {
        if (isset($_GET['post']) && $this->getLink( (int) $_GET['post'] )) {
            \add_action('admin_notices', array($this, 'notifyOfExternalLink'));
        }
    }

    public function notifyOfExternalLink() {
        $tpl = <<<EOT
<div class="wrap">
    <div class="gw-redirectposts-notice">
        <h2>Referencing External Content</h2>
        <p>%s</p>
    </div>
</div>
EOT;
        $id = absint($_GET['post']);
        printf(
            $tpl,
            __('This content is pointing to a custom URL. Use the &#8221;Redirect Post Options&#8221; box to change this behavior.', $this->config->domain)
        );
    }

    public function displayErrors() {
        global $post;
        if ($post) {
            $user_id = \get_current_user_id();
            $key = sprintf($this->config->meta['error_key'], $post->ID, $user_id);

            if ($error = \get_transient($key)) {
                ?>
                <div class="error">
                    <p><?php echo $error->get_error_message(); ?></p>
                </div>
                <?php
                \delete_transient($key);
            }
        }
    }

    public function addPostMetaBox($post_type, $post) {
        // Filter out post types, if necessary
        \add_meta_box(
            $this->meta_box_name,
            __('Redirect Post Options', $this->config->domain),
            array($this, 'renderPostMetaBox'),
            'post',
            'side',
            'high'
        );
    }

    public function renderPostMetaBox($post) {
        \wp_nonce_field(basename(__FILE__), $this->nonce_name);
        $redirect_url = \get_post_meta($post->ID, $this->config->meta['redirect_url'], true);

        $new_tab = \get_post_meta($post->ID, $this->config->meta['new_tab'], true);
        $new_tab = empty($new_tab) ? '1' : $new_tab;

        ?>
        <p>
            <input type="text" name="redirect_url" value="<?php echo $redirect_url; ?>" placeholder="<?php _e('redirect url', $this->config->domain); ?>" class="widefat" />
        </p>
        <p class="gw-input-group-padded">
            <input type="checkbox" name="new_tab"<?php echo $new_tab == '1' ? ' checked="checked"' : ''; ?> id="gw-redirect-post-new-tab" />
            <label for="gw-redirect-post-new-tab"><?php _e('open link in new tab', $this->config->domain) ?></label>
        </p>
        <?php
    }

    public function savePostMetaBox($post_id) {
        if (!isset($_POST[$this->nonce_name]) || !\wp_verify_nonce($_POST[$this->nonce_name], basename(__FILE__))) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        $user_id = \get_current_user_id();
        $redirect_url = isset($_POST['redirect_url']) ? trim($_POST['redirect_url']) : '';
        $new_tab = isset($_POST['new_tab']) ? 1 : 0;
        if (!empty($redirect_url)) {
            /** Disabling due to issues with CloudFlare for now **/
            // validate the URL!
            /*
            $context = stream_context_create(array('http' => array('method' => 'GET')));
            $fd = fopen($redirect_url, 'rb', false, $context);
            $response = stream_get_meta_data($fd);
            $valid = false;

            foreach ($response['wrapper_data'] as $row) {
                if (preg_match('/^HTTP.*?([0-9]{3})/i', $row, $match)) {
                    // we will allow redirects or direct success (200, 301, 302)
                    $allowable = array(200, 301, 302);
                    if (in_array(intval($match[1]), $allowable)) {
                        \update_post_meta($post_id, $this->config->meta['redirect_url'], sanitize_text_field($redirect_url));
                        \update_post_meta($post_id, $this->config->meta['new_tab'], $new_tab);
                        $valid = true;
                    }
                }
            }
            fclose($fd);
             */

            $key = sprintf($this->config->meta['error_key'], $post_id, $user_id);
            $error = new \WP_Error(801, 'Could not validate redirect URL. Please check your address and try again.');
            \set_transient($key, $error, 20);
        } else {
            \delete_post_meta($post_id, $this->config->meta['redirect_url']);
            \delete_post_meta($post_id, $this->config->meta['new_tab']);
        }
    }
}

endif;
