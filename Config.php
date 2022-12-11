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
                            'id'   => 'enable_default_post_description',
                            'name' => __('Default post description', 'xenice-seo'),
                            'label' => __('Use the post excerpt as the default post description', 'xenice-seo'),
                            'type'  => 'checkbox',
                            'value' => true,
                        ],
                        [
                            'id'   => 'enable_default_post_keywords',
                            'name' => __('Default post keywords', 'xenice-seo'),
                            'label' => __('Use the post tags as the default post keywords', 'xenice-seo'),
                            'type'  => 'checkbox',
                            'value' => true,
                        ],
                        [
                            'id'   => 'enable_post_title',
                            'name' => __('Post title', 'xenice-seo'),
                            'label' => __('Display the post title edit box', 'xenice-seo'),
                            'type'  => 'checkbox',
                            'value' => true,
                        ],
                        [
                            'id'   => 'enable_post_description',
                            'name' => __('Post description', 'xenice-seo'),
                            'label' => __('Display the post description edit box', 'xenice-seo'),
                            'type'  => 'checkbox',
                            'value' => true,
                        ],
                        [
                            'id'   => 'enable_post_keywords',
                            'name' => __('Post keywords', 'xenice-seo'),
                            'label' => __('Display the post keywords edit box', 'xenice-seo'),
                            'type'  => 'checkbox',
                            'value' => false,
                        ],
                        [
                            'id'   => 'title_separator',
                            'name' => __('Title Separator', 'xenice-seo'),
                            'type'  => 'select',
                            'value' => '',
                            'opts' => [
                                ' - '=>'-',
                                ' | '=>'|',
                            ]
                        ],
                        [
                            'id'   => 'enable_sitemap',
                            'name' => __('Enable sitemap', 'xenice-seo'),
                            'label' => __('Sitemap url:', 'xenice-seo') .  ' ' . home_url() . '/sitemap.xml',
                            'type'  => 'checkbox',
                            'value' => false,
                        ],
                        
                    ]
                ], // #tab
                
                [
                    'id' => 'home',
                    'title' => __('Home', 'xenice-seo'),
                    'fields'=>[
                        [
                            'id'   => 'home_title',
                            'name' => __('Title', 'xenice-seo'),
                            'type'  => 'text',
                            'value' => '',
                        ],
                        [
                            'id'   => 'home_description',
                            'name' => __('Description', 'xenice-seo'),
                            'type'  => 'textarea',
                            'rows' => 5,
                            'value' => '',
                        ],
                        [
                            'id'   => 'home_keywords',
                            'name' => __('Keywords', 'xenice-seo'),
                            'type'  => 'textarea',
                            'rows' => 5,
                            'value' => '',
                        ],

                    ]
                ], // #tab
                
            ]
        ];
	    parent::__construct();
    }


}