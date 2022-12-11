<?php

namespace xenice\seo\sitemap;

use xenice\seo\Cache;

class SitemapController
{
    public $url;
    public $dir;
    
    public function index($n = 0)
    {
        $dir = WP_CONTENT_DIR . '/uploads/xenice/seo/sitemap';
        $this->dir = $dir;
        is_dir($dir) || mkdir($dir, 0777, true);
        
        // Make sure to run a single process
        $lock = $dir . '/request.lock';
        if(is_file($lock)){
            echo "The sitemap is being generated, please refresh the page later ...";
            return;
        }

        // Show sitemap
        $index = $dir . '/sitemap.xml';
        $cache = new Cache($dir);
        if(is_file($index) && $cache->get('sitemap')){
            $file = $n?'sitemap-'.$n.'.xml':'sitemap.xml';
            $file = $dir . '/'. $file;
            if(is_file($file)){
                header("Content-type: text/xml");
                header('HTTP/1.1 200 OK');
                echo file_get_contents($file);
            }
            else{
                header("HTTP/1.1 404 Not Found");
                header("Status: 404 Not Found");
            }
            return;
        }
        
        // Create sitemap
        echo "Start generating sitemap ...";
        file_put_contents($lock, getmypid());
        fastcgi_finish_request();
        set_time_limit(0);
        ini_set ('memory_limit', '1024M');
        $this->url = __DIR__ . '/static/css';
        $this->create();
        $cache->set('sitemap', true, time() + 24*3600); // save 24 hours
        unlink($lock);

    }
    
    //limit 10000 per file
    private function create($num = 10000)
    {
        $path = $this->dir;

        $urls = array_merge([$this->homeUrl()], $this->urls(), $this->termUrls());

        $count = count($urls);
        if($count>$num){
            $chunk = array_chunk($urls, $num); 
            unset($urls);
            $i = 0;
            foreach($chunk as $urls){
                $i++;
                $file = $path . '/sitemap-' . strval($i) . '.xml';
                $this->createFile($file, $urls);
            }
            //create sitemap index
            $file = $path . '/sitemap.xml';
            $this->createIndex($file, $i);
        }
        else{ //only one sitemap file
            $file = $path . '/sitemap.xml';
            $this->createFile($file, $urls);
        }
    }
    
    public function fields()
    {
        global $wpdb;
        $p = $wpdb->posts;
        return "$p.ID, $p.post_author, $p.post_date, $p.post_date_gmt, $p.post_title, $p.post_status, $p.post_name, $p.post_modified, $p.post_modified_gmt, $p.post_parent, $p.post_type, $p.comment_count";
    }
    
    function urls()
    {
        global $wp_query;
        add_filter('posts_fields', [$this, 'fields']);
        $args = [
            'post_type' => 'any',
            'post_status' => 'publish',
            'posts_per_page' => 1000, // limit 1000 posts
        ];
        $urls = [];
        do{
            $query = $wp_query;
            wp_reset_postdata();
            isset($page) or $page = 1;
            $args['paged'] = $page;
            $query->query($args);
            //return $query->query_vars;
            $page ++;
            
            while ($query->have_posts()) {
                $query->the_post();
                $url['loc'] = get_permalink();
                $url['lastmod'] = get_the_time('c');
                $url['changefreq'] = 'monthly';
                $url['priority'] = '0.6';
                $urls[] = $url;
            }
    
        }while($page <= $query->max_num_pages);

        remove_filter('posts_fields', [$this, 'fields']);
        
        return $urls;
        
    }

    function termUrls()
    {
        $args = array(
            'taxonomy' => array('category','post_tag','doc_category','source_category'),
            'number' => 0 //show all
        );
        $urls = [];
        $query = new \WP_Term_Query();
        $query->query($args);
        foreach ( $query ->terms as $term ) {
            $url = [];
            $url['loc'] = get_term_link($term, $term->slug);
            $url['changefreq'] = 'weekly';
            $url['priority'] = '0.3';
            $urls[] = $url;
        }
        return $urls;
        
    }
    
    function homeUrl()
    {
        $time = get_lastpostmodified('GMT');
        $time = gmdate('Y-m-d\TH:i:s+00:00', strtotime($time));
        
        $url = [];
        $url['loc'] = get_home_url();
        $url['lastmod'] = $time;
        $url['changefreq'] = 'daily';
        $url['priority'] = '1.0';
        return $url;
    }
    

    
    function createFile($file, $urls)
    {
        $fp = fopen($file, 'w');
        fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n");
        fwrite($fp, '<?xml-stylesheet type="text/css" href="' . $this->url .'/sitemap.css"?>' . "\r\n");
        fwrite($fp, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:mobile="http://www.baidu.com/schemas/sitemap-mobile/1/">' . "\r\n");
        foreach($urls as $url){
            $str = "\t<url>\r\n";
            foreach($url as $key=>$val){
                $str .= "\t\t<$key>$val</$key>\r\n";
            }
            $str .= "\t</url>\r\n";
            fwrite($fp, $str);
        }
        fwrite($fp, "</urlset>\r\n");
        fclose($fp);
    }
    
    function createIndex($file, $i)
    {
        $fp = fopen($file,'w');
        fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>' . "\r\n");
        fwrite($fp, '<?xml-stylesheet type="text/css" href="' . $this->url .'/sitemap.css"?>' . "\r\n");
        fwrite($fp, '<sitemapindex>' . "\r\n");
        for($j = 1; $j<=$i; $j++){
            fwrite($fp, "\t<sitemap>\r\n");
            fwrite($fp, "\t\t<loc>" . get_home_url() . "/sitemap-$j.xml</loc>\r\n");
            fwrite($fp, "\t</sitemap>\r\n");
        }
        fwrite($fp, "</sitemapindex>\r\n");
        fclose($fp);
    }
}