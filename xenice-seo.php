<?php

/**
 * Plugin Name: Xenice SEO
 * Plugin URI: https://www.xenice.com
 * Description: Simple SEO 
 * Version: 1.8.4
 * Author: Xenice
 * Author URI: https://www.xenice.com
 * Text Domain: xenice-seo
 * Domain Path: /languages
 */


namespace xenice\seo;

 /**
 * autoload class
 */
function __autoload($classname){
    $classname = str_replace('\\','/',$classname);
    $namespace = 'xenice/seo';
    if(strpos($classname, $namespace) === 0){
        $filename = str_replace($namespace, '', $classname);
        require  __DIR__ .  $filename . '.php';
    }
}




 /**
 * get option
 */
function get($name, $key='xenice_seo')
{
    
    static $option = [];
    if(!$option || !isset($option[$key])){
        $option[$key] = [];
        $options = get_option($key)?:[];
        foreach($options as $o){
            $option[$key] = array_merge($option[$key], $o);
        }
    }
    return $option[$key][$name]??'';
}


 /**
 * set option
 */
function set($name, $value, $key='xenice_seo')
{
    $options = get_option($key)?:[];
    foreach($options as $id=>&$o){
        if(isset($o[$name])){
            $o[$name] = $value;
            update_option($key, $options);
            return;
        }
    }
}

/**
* auto execute when active this plugin
*/
register_activation_hook( __FILE__, function(){
    spl_autoload_register('xenice\seo\__autoload');
    (new Config)->active();
    (new ConfigTools)->active();
    
    $records = new models\Records;
    $records->create();

});


add_action( 'plugins_loaded', function(){
    spl_autoload_register('xenice\seo\__autoload');
    $plugin_name = basename(__DIR__);
    load_plugin_textdomain($plugin_name, false , $plugin_name . '/languages/' );
    
    // Add setting menus
    add_action( 'admin_menu', function(){
        add_menu_page(__('Xenice SEO','xenice-seo'), __('Xenice SEO','xenice-seo'), 'manage_options', 'xenice-seo', '', 'dashicons-search');
        add_submenu_page('xenice-seo', __('Settings','xenice-seo'), __('Settings','xenice-seo'), 'manage_options', 'xenice-seo', function(){
            (new Config)->show();
        });
		/*
        add_submenu_page('xenice-seo', __('Links','xenice-seo'), __('Links','xenice-seo'), 'manage_options', 'xenice-seo-permalink', function(){
           (new permalink\Config)->show();
        });*/
        add_submenu_page('xenice-seo', __('Tools','xenice-seo'), __('Tools','xenice-seo'), 'manage_options', 'xenice-seo-tool', function(){
            (new ConfigTools)->show();
        });
    });
    
    // Add setting button
    $plugin = plugin_basename (__FILE__);
    add_filter("plugin_action_links_$plugin" , function($links)use($plugin_name){
        $settings_link = '<a href="admin.php?page=xenice-seo&tab=general">' . __( 'Settings', 'xenice-seo') . '</a>' ;
        array_push($links , $settings_link);
        return $links;
    });
    
    //new permalink\Permalink;
});


add_action('plugins_loaded', function(){
    get('enable_sitemap', 'xenice_seo_tools') && new sitemap\Sitemap;
});


add_action('init', function(){
    new Meta;
    new TaxMeta;
    new SEO;
    
},999);

