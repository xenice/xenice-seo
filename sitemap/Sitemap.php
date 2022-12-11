<?php

namespace xenice\seo\sitemap;

use xenice\seo\Route;

class Sitemap
{
	public function __construct()
	{
	    $route = new Route;
	    $c = new SitemapController;
        $route->add('sitemap.xml$',[$c, 'index']);
        $route->add('sitemap-(\d+).xml$',[$c, 'index'], 1);
	}
}