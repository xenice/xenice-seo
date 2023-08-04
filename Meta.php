<?php

namespace xenice\seo;

use xenice\seo\models\Records;

class Meta extends Box
{
    
    public $enable_post_title = false;
    public $enable_post_description = false;
    public $enable_post_keywords = false;
    
    public function __construct()
	{
	   $this->enable_post_title = get('enable_seo_title');
	   $this->enable_post_description = get('enable_meta_description');
	   $this->enable_post_keywords = get('enable_meta_keywords');
	   
	   if($this->enable_post_title || $this->enable_post_description || $this->enable_post_keywords){
	        parent::__construct([
    	       "key"=>"seo",
    	       "name"=>'Xenice SEO', 'xenice-seo',
    	       'type'=>['post','page'],
    	   ]);
	   }
	  
	   
	}
	
	public function handle($id, $options)
	{
	    $variable = '
	    <div style="display:flex;gap: 10px;"><div>'.__('Variables:', 'xenice-seo').'</div>
              <div style="color:blue">[post-title]</div>
              <div style="color:blue">[separator]</div>
              <div style="color:blue">[site-title]</div>
          </div>
	    ';
	    $row = (new Records)->where('object_id', $id)->and('type', 'post')->first();
	    
	    $options = [];
	    if($this->enable_post_title){
	        $options[] = [
                'id'   => 'seo_title',
                'name' => __('Seo title', 'xenice-seo'),
                'desc' => __('Set the seo title. Display the default title when empty.', 'xenice-seo') . $variable,
                'type'  => 'text',
                'value' => $row['seo_title']??''
            ];
	    }
	   
	    if($this->enable_post_description){
	        $options[] = [
                'id'   => 'meta_description',
                'name' => __('Meta description', 'xenice-seo'),
                'desc' =>  __('Set the meta description.', 'xenice-seo'),
                'rows' =>4,
                'type'  => 'textarea',
                'value' => $row['meta_description']??''
            ];
	    }
	    
	    if($this->enable_post_keywords){
	        $options[] = [
                'id'   => 'meta_keywords',
                'name' => __('Meta keywords', 'xenice-seo'),
                'desc' => __('Set the meta keywords. Multiple are separated by commas.', 'xenice-seo'),
                'rows' =>4,
                'type'  => 'textarea',
                'value' => $row['meta_keywords']??''
            ];
	    }
	    
	    
	    return $options;
	}
	
	public function update($post_id, $fields)
    {
        $data = [];
        isset($fields['seo_title']) && $data['seo_title'] = $fields['seo_title'];
        isset($fields['meta_description']) && $data['meta_description'] = $fields['meta_description'];
        isset($fields['meta_keywords']) && $data['meta_keywords'] = $fields['meta_keywords'];
        $records = new Records;
        $row = $records->where('object_id', $post_id)->and('type', 'post')->first();
        if($row){
            $records->where('object_id', $post_id)->update($data);
        }
        else{
            $data['object_id'] = $post_id;
            $data['type'] = 'post';
            $records->insert($data);
        }
    }
    
    

}