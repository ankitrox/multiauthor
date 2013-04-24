<?php
/**
 * class multiauthor for adding author names below excerpts and contents
 */
if ( ! defined( 'ABSPATH' ) )
	exit;

class multiauthor {

    var $multiauthor_taxonomy = 'multiauthor';

    /**
     * Constructor for class multiauthor
    */
    public function __construct() {

        add_action( 'the_excerpt', array( $this,'show_multi_authors' ) );
        add_action( 'the_content', array( $this,'show_multi_authors' ) );
        
        add_filter( 'posts_join',  array( $this, 'posts_join_filter'  ), 10, 2 );
        add_filter( 'posts_where', array( $this, 'posts_where_filter' ), 10, 2 );
    }

    function activator() {

        global $multiauthorAdmin;

        $multiauthorAdmin->action_init_late();

        $query = new WP_Query( array( 'post_type'=>'any', 'posts_per_page'=>-1, 'post_status'=>'publish' ) );

        if( $query->have_posts() ){

            while( $query->have_posts() ){

                $query->the_post();

                $isPresent = wp_get_object_terms( get_the_ID() , $this->multiauthor_taxonomy );

                if( empty( $isPresent ) ){

                    $authrdata = get_the_author();

                    $userData = get_user_by( 'slug', $authrdata );

                    $userDataStr = (string)$userData->data->ID;
                    $userDataNum = $userData->data->ID;

                    wp_set_object_terms( get_the_ID(), $userDataStr, $this->multiauthor_taxonomy );
                    
                    update_post_meta( get_the_ID(), 'multiauthors', array( 0 => $userDataNum ) );

                }
            }
        }        
        
    }

    /**
     * Function for displaying authors on front end.
     * @param type $content
     * @return type
     */
    public function show_multi_authors( $content ) {

        $authors = get_post_meta( get_the_ID(), 'multiauthors', true );

        /* If there are not multiple authors associated , then don't do anything */
        if( empty($authors) )
            return $content;

        $markup = '<div class="multi-author-box">'.__( 'Authors of post: ', 'multiauthor' );

        $count = count( $authors );

        $counter = 1;

        foreach( $authors as $author ){

            $authorInfo = get_user_by( 'id', $author );
            $author_url = get_author_posts_url($author);
            $authorName = $authorInfo->data->user_nicename;

            $comma = ( $counter == $count ) ? '' : ', ';

            $markup.= "<span><a href=".$author_url.">$authorName</a>$comma</span>  ";
            
            $counter++;
        }

        $markup .= '</div>';

        return $content.$markup;
    }

    function posts_where_filter( $where, $query ){

        global $wpdb;

        if ( $query->is_author() ){

            $authorSlug = get_query_var('author_name');
            $authorData = get_user_by( 'slug', $authorSlug );

            $termSlug = (string)$authorData->data->ID;
            $getTerm = get_term_by( 'slug', $termSlug, $this->multiauthor_taxonomy );

            $getTerm = $getTerm->term_id;

            $where = str_replace('AND (wp_posts.post_author = '.$authorData->data->ID.')', '', $where);
            
            $where = 'AND ( wp_term_relationships.term_taxonomy_id IN ('.$getTerm.') )'.$where;
            
            //$this->db($where);
        }

        return $where;
    }

    /**
     * Modify the author query posts SQL to include posts
     */
    function posts_join_filter( $join, $query ){

            global $wpdb;

            $authorSlug = get_query_var('author_name');
            $authorData = get_user_by( 'slug', $authorSlug );

            if( $query->is_author() ) {

                    if ( !empty( $query->query_vars['post_type'] ) && !is_object_in_taxonomy( $query->query_vars['post_type'], $this->multiauthor_taxonomy ) )
                            return $join;

                    // Check to see that JOIN hasn't already been added. Props michaelingp and nbaxley
                    $term_relationship_join = " INNER JOIN {$wpdb->term_relationships} ON ({$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id)";
                    //$term_taxonomy_join = " INNER JOIN {$wpdb->term_taxonomy} ON ( {$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id )";

                    if( strpos( $join, trim( $term_relationship_join ) ) === false ) {
                            $join .= str_replace( "INNER JOIN", "LEFT JOIN", $term_relationship_join );
                    }
            }

            return $join;
    }
}