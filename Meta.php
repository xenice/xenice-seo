<?php

namespace xenice\seo;


class Meta extends Box
{
    
    public $enable_post_title = false;
    public $enable_post_description = false;
    public $enable_post_keywords = false;
    
    public function __construct()
	{
	   $this->enable_post_title = get('enable_post_title');
	   $this->enable_post_description = get('enable_post_description');
	   $this->enable_post_keywords = get('enable_post_keywords');
	   
	   if($this->enable_post_title || $this->enable_post_description || $this->enable_post_keywords){
	        parent::__construct([
    	       "key"=>"seo",
    	       "name"=>__('SEO Settings', 'xenice-seo'),
    	       'type'=>['post','page'],
    	   ]);
	   }
	  
	   
	}
	
	public function handle($id, $options)
	{
	    $fields = get_post_meta($id, 'xenice-seo', true);
	    
	    $options = [];
	    if($this->enable_post_title){
	        $options[] = [
                'id'   => 'title',
                'name' => __('Title', 'xenice-seo'),
                'desc' => '',
                'type'  => 'text',
                'value' => $fields['title']??''
            ];
	    }
	    
	    if($this->enable_post_description){
	        $options[] = [
                'id'   => 'description',
                'name' => __('Description', 'xenice-seo'),
                'desc' => '',
                'rows' =>4,
                'type'  => 'textarea',
                'value' => $fields['description']??''
            ];
	    }
	    
	    if($this->enable_post_keywords){
	        $options[] = [
                'id'   => 'keywords',
                'name' => __('Keywords', 'xenice-seo'),
                'desc' => '',
                'rows' =>4,
                'type'  => 'textarea',
                'value' => $fields['keywords']??''
            ];
	    }
	    
	    
	    return $options;
	}
	
	public function update($post_id, $fields)
    {
        update_post_meta($post_id, 'xenice-seo', $fields);
    }
    
    

}