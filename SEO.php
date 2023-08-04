<?php
namespace xenice\seo;

use xenice\seo\models\Records;

class SEO
{
    public function __construct()
    {
        
        add_action('get_header', [$this, 'header']);
        add_filter('document_title_separator', function(){
            $separator = get('title_separator')?:'-';
            $separator = ' ' . $separator . ' ';
            return $separator;
        });
    }
    
    public function header()
    {
       
        if(is_home()){
            if($title = get('home_seo_title')){
                $arr = [
                    '[site-title]' => get_bloginfo('name'),
                    '[separator]' => get('title_separator')?:' - ',
                    '[site-description]' => get_bloginfo('description'),
                ];
                $title = strtr($title, $arr);
                add_filter('document_title_parts', function($parts)use($title){
                    $parts['title'] = $title;
                    $parts['tagline'] = '';
                    $parts['site'] = '';
                    return $parts;
                });
            }
            if($description = get('home_meta_description')){
                add_action('wp_head', function()use($description){
                    echo '<meta name="description" content="'.$description.'" />' . "\r\n";
                    
                },1);
            }
            if($keywords = get('home_meta_keywords')){
                add_action('wp_head', function()use($keywords){
                    echo '<meta name="keywords" content="'.$keywords.'" />' . "\r\n";
                },1);
            }
        }
        elseif(is_singular()){
            global $post;
            $row = (new Records)->where('object_id', $post->ID)->and('type', 'post')->first();
            // title
            if(!empty($row['seo_title'])){
                $arr = [
                    '[post-title]' => get_the_title(),
                    '[separator]' => get('title_separator')?:' - ',
                    '[site-title]' => get_bloginfo('name'),
                ];
                $row['seo_title'] = strtr($row['seo_title'], $arr);
                add_filter('document_title_parts', function($parts)use($row){
                    $parts['title'] = $row['seo_title'];
                    $parts['tagline'] = '';
                    $parts['site'] = '';
                    return $parts;
                });
            }
            
            // description
            if(!empty($row['meta_description'])){
                add_action('wp_head', function()use($row){
                    $description = $row['meta_description'];
                    echo '<meta name="description" content="'.$description.'" />' . "\r\n";
                },1);
            }
            elseif(get('enable_default_post_description')){
                add_action('wp_head', function(){
                    echo '<meta name="description" content="'.get_the_excerpt().'" />' . "\r\n";
                },1);
            }
            
            // keywords
            if(!empty($row['meta_keywords'])){
                add_action('wp_head', function()use($row){
                    $keywords = $row['meta_keywords'];
                    echo '<meta name="keywords" content="'.$keywords.'" />' . "\r\n";
                },1);
            }
            elseif(get('enable_default_post_keywords')){
                add_action('wp_head', function()use($post){
                    echo '<meta name="keywords" content="'.$this->getPostTags($post->ID, $post->post_type .'_tag').'" />' . "\r\n";
                },1);
            }
        }
        elseif(is_category()){
            $id = get_queried_object_id();
            $taxonomy = 'category';
            $this->addTaxSeo($id, $taxonomy);
        }
        elseif(is_tag()){
            $id = get_queried_object_id();
            $taxonomy = 'post_tag';
            $this->addTaxSeo($id, $taxonomy);
        }
        elseif(is_tax()){
            global $wpdb;
		    $id = get_queried_object_id();
		    $taxonomy = $wpdb->get_var("SELECT taxonomy FROM $wpdb->term_taxonomy WHERE term_id=".$id);
		    $this->addTaxSeo($id, $taxonomy);
            
        }
        elseif(is_post_type_archive()){
            $post_types = get_post_types(array('public'   => true,'_builtin' => false));
            foreach($post_types as $post_type){
    	        if(is_post_type_archive($post_type)){
    	            $this->addPostTypeSeo($post_type);
    	            break;
    	        }
    	    }
        }

    }
    
    
    public function addTaxSeo($id, $taxonomy){
        $row = (new Records)->where('object_id', $id)->and('type', 'term')->first();
        $term = get_term($id, $taxonomy);
        // seo title
        if(!empty($row['seo_title'])){
            $arr = [
                '[term-name]' => $term->name,
                '[separator]' => get('title_separator')?:' - ',
                '[site-title]' => get_bloginfo('name'),
            ];
            $row['seo_title'] = strtr($row['seo_title'], $arr);
            
            add_filter('document_title_parts', function($parts)use($row){
                $parts['title'] = $row['seo_title'];
                $parts['tagline'] = '';
                $parts['site'] = '';
                return $parts;
            });
        }
        
        // description
        if(!empty($row['meta_description'])){
            add_action('wp_head', function()use($row){
                $description = $row['meta_description'];
                echo '<meta name="description" content="'.$description.'" />' . "\r\n";
            },1);
        }
        elseif(get('enable_default_term_description')){
            add_action('wp_head', function()use($term){
                echo '<meta name="description" content="'.$term->description.'" />' . "\r\n";
            },1);
        }
        
        // keywords
        if(!empty($row['meta_keywords'])){
            add_action('wp_head', function()use($row){
                $keywords = $row['meta_keywords'];
                echo '<meta name="keywords" content="'.$keywords.'" />' . "\r\n";
            },1);
        }
    }
    
    public function addPostTypeSeo($post_type){
        $row = [
            'seo_title'=>get($post_type . '_post_type_seo_title'),
            'meta_description'=>get($post_type . '_post_type_meta_description'),
            'meta_keywords'=>get($post_type . '_post_type_meta_keywords'),
        ];
        
        // seo title
        if(!empty($row['seo_title'])){
            add_filter('document_title_parts', function($parts)use($row){
                $arr = [
                    '[name]' => $parts['title'],
                    '[separator]' => get('title_separator')?:' - ',
                    '[site-title]' => get_bloginfo('name'),
                ];
                $row['seo_title'] = strtr($row['seo_title'], $arr);
                
                $parts['title'] = $row['seo_title'];
                $parts['tagline'] = '';
                $parts['site'] = '';
                return $parts;
            });
        }
        
        // description
        if(!empty($row['meta_description'])){
            add_action('wp_head', function()use($row){
                $description = $row['meta_description'];
                echo '<meta name="description" content="'.$description.'" />' . "\r\n";
            },1);
        }
        
        // keywords
        if(!empty($row['meta_keywords'])){
            add_action('wp_head', function()use($row){
                $keywords = $row['meta_keywords'];
                echo '<meta name="keywords" content="'.$keywords.'" />' . "\r\n";
            },1);
        }
    }
    
    public function getPostTags($post_id, $post_type="post_tag")
    {
        $str = '';
        $post_tags = get_the_terms($post_id, $post_type);
        if ( !is_wp_error($post_tags) && $post_tags ) {
            foreach( $post_tags as $tag ) {
                $str .= $tag->name . ','; 
            }
        }
        if($str){
             return trim($str, ',');
        }
       
    }
    
}