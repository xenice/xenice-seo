<?php

namespace xenice\seo\models;


use xenice\seo\Model;

class Records extends Model
{
    protected $table = 'xenice_seo_records';
    
    
    protected $fields = [
        'id'=>['type'=>'bigint','range'=>'20','primary'=>true,'unique'=>true,'auto'=>true],
        'object_id'=>['type'=>'bigint','range'=>'20'], // post ID， term id
        'type'=>['type'=>'varchar','range'=>'20'], // post or term
        'core_keywords'=>['type'=>'text','value'=>''],
        'seo_title'=>['type'=>'text','value'=>''], // reserve
        'meta_description'=>['type'=>'text','value'=>''],
        'meta_keywords'=>['type'=>'text','value'=>''],
        
    ];
    
    public function __construct()
    {
        parent::__construct();
    }
    
    public function add($data)
    {
        $row = [];
        $row['key'] = 'ac' . uniqid();
        
        // user
        $user = wp_get_current_user();
        $row['user_id'] = $data['user_id']??$user->ID;
        $row['user_name'] = $data['user_name']??$user->user_login;
        $row['user_email'] = $data['user_email']??$user->user_email;
        
        // product
        $row['product_id'] = $data['product_id']??0;
        $row['product_name'] = $data['product_name'];
   
        // order
        $row['order_id'] = $data['order_id']??0;
        $row['order_key'] = $data['order_key']??'';
        
        $row['auth_limit'] = $data['auth_limit']??1;
        $row['auth_domains'] = $data['auth_domains']??'';
        $row['remarks'] = $data['remarks']??'';
        
        if(isset($data['expire_time'])){
            $row['expire_time'] = $data['expire_time'];
        }
        else{
            $now = date('Y-m-d H:i:s',time()); 
            $row['expire_time'] = date("Y-m-d H:i:s", strtotime("+10 years",strtotime($now)));
        }
        
        $row['update_time'] = date('Y-m-d H:i:s',time());
        return $this->insert($row);
    }
    
    public function active($key, $name, $domain)
    {
        $row = $this->where('key',$key)->and('product_name', $name)->first();
        if(!$row) return;
        
        $domains = [];
        if($row['auth_domains']){
            $domains = unserialize($row['auth_domains']);
        }
        
        $data = [
            'domain' => $domain,
            'expire' => $row['expire_time'],
            'time'=>time()
        ];
        
        // if domain exist
        if(in_array($domain, $domains)){
            return $data;
        }
        
        // if domain not exist
        $count = count($domains);
        if($row['auth_limit']>$count){
            $domains[] = $domain;
            $this->where('key',$key)->and('product_name', $name)->update(['auth_domains'=>serialize($domains), 'update_time'=>date('Y-m-d H:i:s', time())]);
            return $data;
        }
    }
}