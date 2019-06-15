<?php
/**
 * Plugin Name: WebP Express
 * Plugin URI: https://github.com/rosell-dk/webp-express
 * Description: Serve autogenerated WebP images instead of jpeg/png to browsers that supports WebP. Works on anything (media library images, galleries, theme images etc).
 * Version: 0.14.1
 * Author: Bjørn Rosell
 * Author URI: https://www.bitwise-it.dk
 * License: GPL2
 * Network: true
 */

/*
Note: Perhaps create a plugin page on my website?, ie https://www.bitwise-it.dk/software/wordpress/webp-express
*/

use \WebPExpress\AdminInit;
use \WebPExpress\Option;

define('WEBPEXPRESS_PLUGIN', __FILE__);
define('WEBPEXPRESS_PLUGIN_DIR', __DIR__);

// Autoloading rules!
spl_autoload_register('webpexpress_autoload');
function webpexpress_autoload($class) {
    if (strpos($class, 'WebPExpress\\') === 0) {
        require_once WEBPEXPRESS_PLUGIN_DIR . '/lib/classes/' . substr($class, 12) . '.php';
    }
}

if (is_admin()) {
    \WebPExpress\AdminInit::init();
}

function webp_express_process_post() {
    // strip query string
    $requestUriNoQS = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

    if (!preg_match('/webp-express-web-service$/', $requestUriNoQS)) {
        return;
    }
    include __DIR__ . '/web-service/wpc.php';
    die();
}
add_action( 'init', 'webp_express_process_post' );

if (Option::getOption('webp-express-alter-html', false)) {
    require_once __DIR__ . '/lib/classes/AlterHtmlInit.php';
    \WebPExpress\AlterHtmlInit::setHooks();
}

/*
$filters = [];
add_action('all', function() {
    global $filters;
    $filter = current_filter();
    if (!in_array($filter, $filters)) {
        $filters[] = $filter;
        error_log($filter);
    }
});
*/

// When images are uploaded with Gutenberg, is_admin() returns false, so, hook needs to be added here
add_filter('wp_handle_upload', array('\WebPExpress\HandleUploadHooks', 'handleUpload'), 10, 2);
add_filter('image_make_intermediate_size', array('\WebPExpress\HandleUploadHooks', 'handleMakeIntermediateSize'), 10, 1);
add_filter('wp_delete_file', array('\WebPExpress\HandleDeleteFileHook', 'deleteAssociatedWebP'), 10, 2);

/*
add_action('wp_handle_upload', function($a) {
    error_log('yes, it is called'. (is_admin() ? 'admin' : 'not admin'));
    \WebPExpress\HandleUploadHooks::handleUpload($a, true);
    return $a;
});*/


/*
function webpexpress_addWebPJs() {
    $url = plugins_url('webpjs/webpjs-0.0.2.min.js', __FILE__);
    $script = <<<EOD
<script>
    (function(){
        var WebP=new Image();
        WebP.onload=WebP.onerror=function(){
            if(WebP.height!=2){
                var sc=document.createElement('script');
                sc.type='text/javascript';
                sc.async=true;
                var s=document.getElementsByTagName('script')[0];
                sc.src='$url';
                s.parentNode.insertBefore(sc,s);
            }
        };
        WebP.src='data:image/webp;base64,UklGRjoAAABXRUJQVlA4IC4AAACyAgCdASoCAAIALmk0mk0iIiIiIgBoSygABc6WWgAA/veff/0PP8bA//LwYAAA';
    })();
</script>
EOD;
    echo $script;
}

add_action( 'wp_head', 'webpexpress_addWebPJs');
*/
//add_action( 'template_redirect', 'webp_express_template_redirect' );
