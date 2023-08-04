<?php

namespace xenice\seo;

use xenice\seo\models\Records;

class TaxMeta
{
    public function __construct()
	{
	    //add_action( 'edited_category', 'xg_save_tax_meta', 10, 2 );
        //add_action( 'category_add_form_fields', [$this, 'form_fields']);
        
        $taxonomies = get_taxonomies(['public'=> true,'_builtin' => false]); 
        $taxonomies[] = 'category';
        $taxonomies[] = 'post_tag';
        foreach($taxonomies as $taxonomy){
            add_action( $taxonomy . '_edit_form_fields', [$this, 'form_fields']);
            add_action( 'edited_' . $taxonomy, [$this, 'save_fields'], 10, 1 );
        }
        
	}
	
	

	public function form_fields( $term ){
        $term_id = $term->term_id;
        $term_meta = get_option( "taxonomy_$term_id" );
        
        $row = (new Records)->where('object_id', $term_id)->and('type', 'term')->first();
        
    ?>
    <?php if(get('enable_seo_title')):?>
    <tr class="form-field">
      <th scope="row">
        <label for="term_seo_title"><?php echo __('SEO title', 'xenice-seo') ?></label>
      </th>
      <td>
          <input type="text" name="term_seo_title" id="term_seo_title" class="large-text" value="<?php echo $row['seo_title']??''?>"></input>
          <p><?php echo __('Set the seo title. Display the default title when empty.', 'xenice-seo') ?></p>
          <div style="display:flex;gap: 10px;">
              <div><?php echo __('Variables:', 'xenice-seo') ?></div>
              <div style="color:blue">[term-name]</div>
              <div style="color:blue">[separator]</div>
              <div style="color:blue">[site-title]</div>
          </div>
	  </td>
    </tr>
    <?php endif; ?>
    <?php if(get('enable_meta_description')):?>
    <tr class="form-field">
      <th scope="row">
        <label for="term_meta_description"><?php echo __('Meta description', 'xenice-seo') ?></label>
      </th>
      <td>
          <textarea name="term_meta_description" id="term_meta_description" rows="5" cols="50" class="large-text"><?php echo $row['meta_description']??''?></textarea>
          <p><?php echo __('Set the meta description.', 'xenice-seo') ?></p>
	  </td>
    </tr>
    <?php endif; ?>
    <?php if(get('enable_meta_keywords')):?>
    <tr class="form-field">
      <th scope="row">
        <label for="term_meta_keywords"><?php echo __('Meta keywords', 'xenice-seo') ?></label>
      </th>
      <td>
          <textarea name="term_meta_keywords" id="term_meta_keywords" rows="5" cols="50" class="large-text"><?php echo $row['meta_keywords']??''?></textarea>
          <p><?php echo __('Set the meta keywords. Multiple are separated by commas.', 'xenice-seo') ?></p>
	  </td>
    </tr>
    <?php endif; ?>
    <?php
    } 
    
    public function save_fields($term_id){
        
        $data = [];
        isset($_POST['term_seo_title']) && $data['seo_title'] = $_POST['term_seo_title'];
        isset($_POST['term_meta_description']) && $data['meta_description'] = $_POST['term_meta_description'];
        isset($_POST['term_meta_keywords']) && $data['meta_keywords'] = $_POST['term_meta_keywords'];
        if(empty($data)) return;
        $records = new Records;
        $row = $records->where('object_id', $term_id)->and('type', 'term')->first();
        if($row){
            $records->where('object_id', $term_id)->update($data);
        }
        else{
            $data['object_id'] = $term_id;
            $data['type'] = 'term';
            $records->insert($data);
        }
    }
	
}
