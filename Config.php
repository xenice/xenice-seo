<?php
/**
 * @name        xenice options
 * @author      xenice <xenice@qq.com>
 * @version     1.0.0 2019-09-26
 * @link        http://www.xenice.com/
 * @package     xenice
 */
 
namespace xenice\seo;


class Config extends Options
{
    protected $key = 'seo';
    protected $name = ''; // Database option name
    protected $defaults = [];
    
    public function __construct()
    {
        $this->name = 'xenice_' . $this->key;
        $this->defaults[] = [
            'id'=>'seo',
            //'pos'=>100,
            'name'=> __('SEO Settings', 'xenice-seo'),
            'title'=> __('SEO Settings', 'xenice-seo'),
            'tabs' => [
                [
                    'id' => 'general',
                    'title' => __('General', 'xenice-seo'),
                    'fields'=>[
                        [
                            'id'   => 'enable_seo_title',
                            'name' => __('SEO title', 'xenice-seo'),
                            'label' => __('Display the SEO title edit box', 'xenice-seo'),
                            'type'  => 'checkbox',
                            'value' => true,
                        ],
                        [
                            'id'   => 'enable_meta_description',
                            'name' => __('Meta description', 'xenice-seo'),
                            'label' => __('Display the meta description edit box', 'xenice-seo'),
                            'type'  => 'checkbox',
                            'value' => true,
                        ],
                        [
                            'id'   => 'enable_meta_keywords',
                            'name' => __('Meta keywords', 'xenice-seo'),
                            'label' => __('Display the meta keywords edit box', 'xenice-seo'),
                            'type'  => 'checkbox',
                            'value' => true,
                        ],
                        [
                            'id'   => 'title_separator',
                            'name' => __('Title Separator', 'xenice-seo'),
                            'type'  => 'select',
                            'value' => '',
                            'opts' => [
                                '-'=>'-',
                                '|'=>'|',
                            ]
                        ],
                        [
                            'id'   => 'enable_default_post_description',
                            'name' => __('Default post description', 'xenice-seo'),
                            'label' => __('Use the post excerpt as the default meta description', 'xenice-seo'),
                            'type'  => 'checkbox',
                            'value' => false,
                        ],
                        [
                            'id'   => 'enable_default_post_keywords',
                            'name' => __('Default post keywords', 'xenice-seo'),
                            'label' => __('Use the post tags as the default meta keywords', 'xenice-seo'),
                            'type'  => 'checkbox',
                            'value' => false,
                        ],
                        [
                            'id'   => 'enable_default_term_description',
                            'name' => __('Default tag&category description', 'xenice-seo'),
                            'label' => __('Use the tag&category description as the default meta description', 'xenice-seo'),
                            'type'  => 'checkbox',
                            'value' => false,
                        ],
                    ]
                ], // #tab
            ]
        ];
        
        // add home tab
        $variables = '
	    <div style="display:flex;gap: 10px;"><div>'.__('Variables:', 'xenice-seo').'</div>
              <div style="color:blue">[site-title]</div>
              <div style="color:blue">[separator]</div>
              <div style="color:blue">[site-description]</div>
          </div>
	    ';
        if($tab = $this->createTab('home', __('Home', 'xenice-seo'), $variables)){
             $this->defaults[0]['tabs'][] = $tab;
        }
        
        // add post_type archive tab
        $variables = '
	    <div style="display:flex;gap: 10px;"><div>'.__('Variables:', 'xenice-seo').'</div>
              <div style="color:blue">[name]</div>
              <div style="color:blue">[separator]</div>
              <div style="color:blue">[site-title]</div>
          </div>
	    ';
        $post_types = get_post_types(array('public'   => true,'_builtin' => false), 'objects');
        foreach($post_types as $obj){
            if($tab = $this->createTab($obj->name .'_post_type', $obj->label, $variables)){
                 $this->defaults[0]['tabs'][] = $tab;
            }
            
        }
        
	    parent::__construct();
    }
    
    public function createTab($key, $name, $variables){
        $fields = [];
        if(get('enable_seo_title')){
            $fields[] = [
                'id'   => $key . '_seo_title',
                'name' => __('SEO title', 'xenice-seo'),
                'desc' => __('Set the seo title. Display the default title when empty.', 'xenice-seo') . $variables,
                'type'  => 'text',
                'value' => '',
            ];
        }
        if(get('enable_meta_description')){
            $fields[] = [
                'id'   => $key . '_meta_description',
                'name' => __('Meta description', 'xenice-seo'),
                'desc' =>  __('Set the meta description.', 'xenice-seo'),
                'type'  => 'textarea',
                'rows' => 5,
                'value' => '',
            ];
        }
        if(get('enable_meta_keywords')){
            $fields[] = [
                'id'   => $key . '_meta_keywords',
                'name' => __('Meta keywords', 'xenice-seo'),
                'desc' => __('Set the meta keywords. Multiple are separated by commas.', 'xenice-seo'),
                'type'  => 'textarea',
                'rows' => 5,
                'value' => '',
            ];
        }
        
        if($fields){
            return  [
                    'id' => $key,
                    'title' => $name,
                    'fields'=>$fields
                ];
        }
    }

}