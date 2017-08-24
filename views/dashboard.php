<div class="wrap">
    <h2><?php esc_html_e('GW Redirect Posts :: Settings'); ?></h2>
    <div class="dashboard" id="dashboard">
        <form name="gw-redirect-posts-settings" action="" method="POST">
            <div class="region">
                <section class="content">
                    <h3>General Configuration</h3>
                    <div class="input-group">
                        <label for="gw-redirect-slug">Base redirect slug</label>
                        <input type="text" name="redirect_slug" id="gw-redirect-slug" value="<?php echo $redirect_slug; ?>" />
                        <p class="note">This is the base for the URL when linking to the "read more" page.</p>
                    </div>
                </section>
            </div>
            <div class="submission">
                <input type="submit" name="submit" value="Save Settings" class="btn btn-primary" />
            </div>
        </form>
    </div>
</div>
