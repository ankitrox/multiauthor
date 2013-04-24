<?php
/**
 * class multiauthorAdmin for adding Metaboxes to the posts
 */
class multiauthorAdmin {

   /**
    * Constuctor for the class multiauthorAdmin
    */
    public function __construct() {

        add_action( 'init', array( $this, 'action_init_late' ), 100 );        

        add_action( 'add_meta_boxes', array( $this, 'multiauthor_metabox' ) );
        add_action( 'save_post', array( $this, 'multiauthor_save_data' ), 20, 2 );

    }

    /**
     * Register the 'author' taxonomy and add post type support
     */
    public function action_init_late() {

        // Register new taxonomy so that we can store all of the relationships
        $args = array(

            'hierarchical' => true,
            'update_count_callback' => '',
            'rewrite' => true,
            'query_var' => 'multiauthor',
            'public' => false,
            'show_ui' => null,
            'show_tagcloud' => null,
            '_builtin' => false,
            'labels' => array(
            'name' => _x( 'MultiAuthor', 'MultiAuthor Taxonomy' ),
            'singular_name' => _x( 'MultiAuthor', 'MultiAuthor Taxonomy' ),
            'search_items' => __( 'Search MultiAuthor' ),
            'all_items' => __( 'All MultiAuthor' ),
            'parent_item' => array( null, __( 'Parent MultiAuthor' ) ),
            'parent_item_colon' => array( null, __( 'ParentMultiAuthor:' ) ),
            'edit_item' => __( 'Edit MultiAuthor' ),
            'view_item' => __( 'View MultiAuthor' ),
            'update_item' => __( 'Update MultiAuthor' ),
            'add_new_item' => __( 'Add New MultiAuthor' ),
            'new_item_name' => __( 'New MultiAuthor Name' ) ),
            'capabilities' => array(),
            'show_in_nav_menus' => null,
            'label' => __( 'MultiAuthors' ),
            'sort' => true,
            'args' => array( 'orderby' => 'term_order' ) 
        );

        $post_types_with_authors = get_post_types();

        $buitInPostTypes = array( 'attachment', 'revision', 'nav_menu_item' );

        $diff = array_diff( $post_types_with_authors, $buitInPostTypes );

        $diff = array_values($diff);

        register_taxonomy( 'multiauthor', $diff, $args );
    }

    /**
     * call function add_meta_box for adding metabox to all post types.
    */
    public function multiauthor_metabox() {

        $post_types_with_authors = get_post_types();

        $buitInPostTypes = array( 'attachment', 'revision', 'nav_menu_item' );

        $diff = array_diff( $post_types_with_authors, $buitInPostTypes );

        foreach ( $diff as $difference ){

             add_meta_box( 'multiaurhor_metabox_id', __( 'Contributors', 'multiauthor' ), array( $this, 'muliauthor_apply_box' ), $difference, 'side', 'high' );

        }
    }

    /**
     * Metabox for multiple authors.
     * 
     * @param type $post - object for the current post.
     */
    public function muliauthor_apply_box( $post ){

        wp_nonce_field( plugin_basename( __FILE__ ), $post->post_type . '_noncename' );

        $multiauthors = get_post_meta( $post->ID, 'multiauthors', true );

        $multiauthors = empty( $multiauthors ) ? array() : $multiauthors;

        $users = get_users( array( 'who'=>'authors' ) );

        if( !empty( $users ) ): ?>

            <div class="form-table">
                <ul class="multi-author-list"><?php

                    foreach( $users as $key => $val ){ ?>

                        <li>
                            <input type="checkbox" <?php echo in_array( $val->data->ID , $multiauthors ) ?  'checked="checked"' : ''; ?> name="multiauthor[]" id="multiauthor_<?php echo $val->data->ID; ?>" value="<?php echo $val->data->ID; ?>" />
                            <label for="multiauthor_<?php echo $val->data->ID; ?>"><?php echo $val->data->user_nicename; ?></label>
                        </li><?php

                   } ?>

                </ul>          
            </div><?php

        endif;
    }

   /**
    * Save associated authors for the post
    * @param type $post_id
    * @return type
   */
    public function multiauthor_save_data( $post_id ){

        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return;

        $post_id  = $_POST['post_ID'];
        $multiauthors = $_POST['multiauthor'];

        if( empty( $multiauthors ) ){
            
            $author = $_POST['post_author'];
            
            $multiauthors = array( 0 => $author );

        }

        update_post_meta( $post_id, 'multiauthors', $multiauthors );

        wp_set_object_terms( $post_id , $multiauthors, 'multiauthor' );
    }

}