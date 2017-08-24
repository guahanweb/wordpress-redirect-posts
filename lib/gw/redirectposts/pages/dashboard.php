<?php
namespace GW\RedirectPosts\Pages;

use GW\RedirectPosts;

class Dashboard {
    public function __construct($config) {
        $this->config = $config;
    }

    public function process() {
        // process any possible data payloads
        $redirect_slug = isset($_POST['redirect_slug']) ? trim($_POST['redirect_slug']) : $this->config->redirect_slug;
        if (substr($redirect_slug, -1) != '/') {
            $redirect_slug .= '/';
        }

        \update_option($this->config->opt_redirect, $redirect_slug);
        $this->redirect_slug = $redirect_slug;
    }

    public function render() {
        $data = array(
            'redirect_slug' => $this->redirect_slug
        );
        RedirectPosts\View::render('dashboard', $data);
    }
}
