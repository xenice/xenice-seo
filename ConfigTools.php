<?php
/**
 * @name        xenice options
 * @author      xenice <xenice@qq.com>
 * @version     1.0.0 2019-09-26
 * @link        http://www.xenice.com/
 * @package     xenice
 */
 
namespace xenice\seo;

class ConfigTools extends Options
{
    protected $key = 'seo_tools';
    protected $name = ''; // Database option name
    protected $defaults = [];
    
    public function __construct()
    {
        $sitemap_url = home_url() . '/sitemap.xml';
        $sitemap_link = sprintf('<a href="%s" target="_blank">%s</a>', $sitemap_url, $sitemap_url);
        $this->name = 'xenice_' . $this->key;
        $this->defaults[] = [
            'id'=>'seo',
            //'pos'=>100,
            'name'=> __('SEO Tools', 'xenice-seo'),
            'title'=> __('SEO Tools', 'xenice-seo'),
            'fields'=>[
                [
                    'id'   => 'enable_sitemap',
                    'name' => __('Enable sitemap', 'xenice-seo'),
                    'label' => __('Sitemap url:', 'xenice-seo') .  ' ' . $sitemap_link,
                    'type'  => 'checkbox',
                    'value' => true,
                ],
            ]

        ];
	    parent::__construct();
    }


}