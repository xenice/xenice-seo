<?php
/**
 * @name        Xenice Route
 * @author      xenice <xenice@qq.com>
 * @version     1.0.0 2019-09-07
 * @link        http://www.xenice.com/
 * @package     xenice
 */
 
namespace xenice\seo;

class Route
{
    public $key = 'xenice_seo';
    public $rules = [];
    
	public function __construct()
	{
	    add_action( 'init', [$this,'addRules']);
	    add_filter('query_vars', [$this, 'addQuery'], 1);
	    add_filter('template_redirect', [$this,'generate'],1);
	}
	
	public function generate()
	{
	    // custom page
	    global $wp_query;
		$key = $wp_query->query_vars[$this->key]??'';
        if ($key && isset($this->rules[$key])){
			$args = $wp_query->query_vars[$this->key . '_args']??[];
			$args = empty($args)?[]:explode(' ', $args);
			call_user_func_array($this->rules[$key]['callback'],$args);
			exit;
		}
	}

    public function add($slug, $callback, $count = 0, $level = 'top')
	{
		$this->rules[md5($slug)] = ['slug'=>$slug, 'callback'=>$callback, 'count'=>$count, 'level'=>$level];
	}
	
    public function addRules()
    {
		foreach($this->rules as $key=>$arr){
		    $args = '';
		    if($arr['count']>0){
		        for($i=1; $i<=$arr['count']; $i++){
		            $args .= '+$matches['.$i.']';
		        }
		        $args = '&'.$this->key.'_args=' . ltrim($args,'+');
		    }
			add_rewrite_rule($arr['slug'], 'index.php?'.$this->key.'=' . $key . $args, $arr['level']);
		}
        flush_rewrite_rules();
    }
    
    function addQuery($vars)
    {
        $vars[] = $this->key;
		$vars[] = $this->key . '_args';
        return $vars;
    }
}