<?php
namespace xenice\seo;


class SEO
{
    public function __construct()
    {
        
        add_action('get_header', [$this, 'header']);
        add_filter('document_title_separator', function(){
            $separator = get('title_separator')?:' - ';
            return $separator;
        });
    }
    
    public function header()
    {
       
        if(is_home()){
            if($title = get('home_title')){
                add_filter('document_title_parts', function($parts)use($title){
                    $parts['title'] = $title;
                    $parts['tagline'] = '';
                    $parts['site'] = '';
                    return $parts;
                });
            }
            if($description = get('home_description')){
                add_action('wp_head', function()use($description){
                    echo '<meta name="description" content="'.$description.'" />' . "\r\n";
                },1);
            }
            if($keywords = get('home_keywords')){
                add_action('wp_head', function()use($keywords){
                    echo '<meta name="keywords" content="'.$keywords.'" />' . "\r\n";
                },1);
            }
        }
        elseif(is_single()){
            global $post;
            $fields = get_post_meta($post->ID, 'xenice-seo', true);
            // title
            if(!empty($fields['title'])){
                add_filter('document_title_parts', function($parts)use($fields){
                    $parts['title'] = $fields['title'];
                    $parts['tagline'] = '';
                    $parts['site'] = '';
                    return $parts;
                });
            }
            
            // description
            if(!empty($fields['description'])){
                add_action('wp_head', function()use($fields){
                    $description = $fields['description'];
                    echo '<meta name="description" content="'.$description.'" />' . "\r\n";
                },1);
            }
            elseif(get('enable_default_post_description')){
                add_action('wp_head', function(){
                    echo '<meta name="description" content="'.get_the_excerpt().'" />' . "\r\n";
                },1);
            }
            
            // keywords
            if(!empty($fields['keywords'])){
                add_action('wp_head', function()use($fields){
                    $keywords = $fields['keywords'];
                    echo '<meta name="keywords" content="'.$keywords.'" />' . "\r\n";
                },1);
            }
            elseif(get('enable_default_post_keywords')){
                add_action('wp_head', function(){
                    echo '<meta name="keywords" content="'.$this->getPostTags().'" />' . "\r\n";
                },1);
            }
        }
        
    }
    
    public function getPostTags()
    {
        $str = '';
        $post_tags = get_the_tags();
        if ( $post_tags ) {
            foreach( $post_tags as $tag ) {
                $str .= $tag->name . ','; 
            }
        }
        return trim($str, ',');
    }
    
}