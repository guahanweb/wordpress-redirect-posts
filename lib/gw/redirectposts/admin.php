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
        \add_action('add_meta_boxes', array($this, 'addPostMetaBox'), 10, 2);
        \add_action('save_post', array($this, 'savePostMetaBox'), 10, 2);
        \add_action('admin_notices', array($this, 'displayErrors'));
    }

    public function displayErrors() {
        global $post;
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
        ?>
        <p>
            <input type="text" name="redirect_url" value="<?php echo $redirect_url; ?>" placeholder="<?php _e('redirect url', $this->config->domain); ?>" class="widefat" />
        </p>
        <?php
    }

    public function savePostMetaBox($post_id) {
        if (!isset($_POST[$this->nonce_name]) || !\wp_verify_nonce($_POST[$this->nonce_name], basename(__FILE__))) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

        $user_id = \get_current_user_id();
        $redirect_url = isset($_POST['redirect_url']) ? trim($_POST['redirect_url']) : '';
        if (!empty($redirect_url)) {
            // validate the URL!
            $context = stream_context_create(array('http' => array('method' => 'HEAD')));
            $fd = fopen($redirect_url, 'rb', false, $context);
            $response = stream_get_meta_data($fd);
            $valid = false;

            foreach ($response['wrapper_data'] as $row) {
                if (preg_match('/^HTTP.*?([0-9]{3})/i', $row, $match)) {
                    // we will allow redirects or direct success (200, 301, 302)
                    $allowable = array(200, 301, 302);
                    if (in_array(intval($match[1]), $allowable)) {
                        \update_post_meta($post_id, $this->config->meta['redirect_url'], sanitize_text_field($redirect_url));
                        $valid = true;
                    }
                }
            }
            fclose($fd);

            if (!$valid) {
                $key = sprintf($this->config->meta['error_key'], $post_id, $user_id);
                $error = new \WP_Error(801, 'Could not validate redirect URL. Please check your address and try again.');
                \set_transient($key, $error, 20);
            }
        } else {
            \delete_post_meta($post_id, $this->config->meta['redirect_url']);
            \delete_post_meta($post_id, 'redirect_output');
        }
    }
}

endif;
