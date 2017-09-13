# WordPress Redirect Posts Plugin

Allows for adding a redirect URL to a post without fully overwriting the permalink.
An additional "redirect" endpoint will be supported in the WP ecosystem that will
manage the inbound traffic and then redirect to the original URL.

## Continue Reading Link

In order to allow for customization of the "Continue Reading" link on individual
post renders, there are two filters you may leverage.

### `gw_redirectposts_link_text`

This filter allows you to easily modify the link text displayed within the post:

```php
function gw_custom_redirect_link_text($content) {
    return __('Custom Read More Text', 'mydomain');
}

add_filter('gw_redirectposts_link_text', 'gw_custom_redirect_link_text', 10, 1);
```

### `gw_redirectposts_link_widget`

This filter gives full control over the output of the widget. Both the URL and
text are provided to this filter along with the entire content.

```php
function gw_custom_redirect_widget($content, $url, $text) {
    $my_widget = '<div class="custom-wrapper"><a href="%s" target="_blank">%s</a></div>';
    return sprtintf($my_widget, $url, $text);
}

add_filter('gw_redirectposts_link_widget', 'gw_custom_redirect_widget', 10, 3);
```
