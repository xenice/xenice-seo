<?php

/**
 * Plugin Name: Xenice SEO
 * Plugin URI: https://www.xenice.com/downloads/xenice-seo
 * Description: A simple SEO plugin
 * Version: 2.0.3
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
    
    $records = new models\Records;
    $records->create();

});


add_action( 'plugins_loaded', function(){
    spl_autoload_register('xenice\seo\__autoload');
    $plugin_name = basename(__DIR__);
    
    // add setting menus
    add_action( 'admin_menu', function(){
        add_options_page(__('Xenice SEO','xenice-seo'), __('Xenice SEO','xenice-seo'), 'manage_options', 'xenice-seo', function(){
            (new Config)->show();
        });
        
    });
    
    // Add setting button
    $plugin = plugin_basename (__FILE__);
    add_filter("plugin_action_links_$plugin" , function($links)use($plugin_name){
        $settings_link = '<a href="options-general.php?page='.$plugin_name.'">' . __( 'Settings', 'xenice-seo') . '</a>' ;
        array_push($links , $settings_link);
        return $links;
    });
    
    
    
});



add_action('init', function(){
    $plugin_name = basename(__DIR__);
    load_plugin_textdomain('xenice-seo', false , $plugin_name . '/languages/' );
    
    new Meta;
    new TaxMeta;
    new SEO;
    
},999);

