<?php namespace Carbontwelve\Widgets\RecentPosts;

/*
Plugin Name:        Recent Posts Widget
Plugin URI:         http://www.photogabble.co.uk
Version:            1.0.0
Description:        A highly configurable widget with editable views
Author:             Simon Dann
Author URI:         http://www.photogabble.co.uk

License:            MIT
License URI:        http://opensource.org/licenses/MIT
*/

use Carbontwelve\Widgets\RecentPosts\Libs\View;

/**
 * Class RecentPostsWidget
 * @package Carbontwelve\Widgets
 * @todo add caching
 * @todo add setting of default image
 */
class RecentPostsWidget extends \WP_Widget {

    private $slug = 'Carbontwelve_RecentPosts_Widget';

    /**
     * Path where we are to look for views
     *
     * @var string
     */
    private $viewsPath;

    /**
     * Only a small subset of the available order columns is listed here as these are the useful ones
     *
     * @var array
     */
    private $orderBy = array(
        'date'          => 'Date',
        'comment_count' => 'Number of Comments',
        'rand'          => 'Random'
    );

    /**
     * Default order by column
     *
     * @var string
     */
    private $defaultOrderBy = 'date';

    /**
     * @var string
     */
    private $defaultTemplate;

    /**
     * Register widget with WordPress
     */
    public function __construct(){
        parent::__construct(
            $this->slug,
            __('Simple Recent Posts', 'carbontwelve'),
            array(
                'description' => __('Display recent posts.','carbontwelve')
            )
        );

        $this->viewsPath       = __DIR__ . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'recent-posts' . DIRECTORY_SEPARATOR;
        $this->defaultTemplate = base64_encode( $this->viewsPath . 'frontend.php');
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     */
    public function widget( $args, $instance )
    {

        $output = '';
        $cache  = wp_cache_get( $this->slug, 'widget' );

        // Check if we have a cached view generated that we can display
        if ( is_array($cache) )
        {
            if ( $this->is_preview() === false && isset($cache[ $args[ 'widget_id' ] ] ))
            {
                echo $cache[ $args[ 'widget_id' ] ];
                return;
            }
        }else{
            $cache = array();
        }

        $view   = new View( base64_decode($instance['template']) );

        // If the loaded view does not exist then roll back to a sane default
        if ( ! $view->exists() )
        {
            $view  = new View( base64_decode($this->defaultTemplate) );
        }

        $posts = new \WP_Query( apply_filters( 'widget_posts_args', array(
            'posts_per_page'      => $instance['numberOfPosts'],
            'no_found_rows'       => true,
            'post_status'         => 'publish',
            'ignore_sticky_posts' => true,
            'orderby'             => $instance['orderBy']
        )));

        // Reset the global $the_post as this query will have stomped on it
        wp_reset_postdata();

        $output .= $args['before_widget'];
        $output .= $view->render(array(
            'widget'        => $this,
            'title'         => $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'],
            'numberOfPosts' => $instance['numberOfPosts'],
            'posts'         => $posts
        ));
        $output .= $args['after_widget'];

        if ( ! $this->is_preview() ) {
            $cache[$args['widget_id']] = $output;
            wp_cache_set($this->slug, $cache, 'widget');
        }

        echo $output;
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     * @return void
     */
    public function form ( $instance )
    {
        $view = new View( $this->viewsPath . 'backend.php');
        echo $view->render(array(
            'widget'             => $this,
            'availableOrders'    => $this->orderBy,
            'title'              => ( isset( $instance[ 'title' ] ) ) ? $instance[ 'title' ] : __( 'Title', 'carbontwelve' ),
            'orderBy'            => ( isset( $instance[ 'orderBy' ] ) ) ? $instance[ 'orderBy' ] : $this->defaultOrderBy,
            'numberOfPosts'      => (int) ( isset( $instance[ 'numberOfPosts' ] ) ) ? $instance[ 'numberOfPosts' ] : 3,
            'template'           => ( isset( $instance[ 'template' ] ) ) ? $instance[ 'template' ] : $this->defaultTemplate,
            'availableTemplates' => $this->identifyWidgetViews()
        ));
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {

        $this->clearCache();

        return array(
            'title'         => ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '',
            'numberOfPosts' => (int) ( ! empty( $new_instance['numberOfPosts'] ) ) ? strip_tags( $new_instance['numberOfPosts'] ) : 3,
            'orderBy'       => ( isset( $new_instance[ 'orderBy' ] ) ) ? $new_instance[ 'orderBy' ] : $this->defaultOrderBy,
            'template'      => ( isset( $new_instance[ 'template' ] ) ) ? $new_instance[ 'template' ] : $this->defaultTemplate,
        );
    }

    /**
     * Clears the cache for this widget
     * @return bool
     */
    public function clearCache() {
        return wp_cache_delete( $this->slug, 'widget' );
    }

    /**
     * Identify the main image and return its src
     * @param \WP_Post $post
     * @param string $thumbSize
     * @return string
     */
    public function getPostThumbNailSrc( \WP_Post $post, $thumbSize = 'medium' )
    {

        if ( has_post_thumbnail($post->ID) ) {
            /** @var array $image */
            $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), $thumbSize, false);
            if ( is_array($image) && isset($image[0])) {
                return $image[0];
            }
        }else{
            // Attempt to get the first image from the post
            preg_match_all( '/<img[^>]+>/i', $post->post_content, $imgTags );

            if( isset($imgTags[0]) && isset($imgTags[0][0]))
            {
                $imgAttributes = array();
                preg_match_all('/(src|class)=("[^"]*")/i',$imgTags[0][0], $imgAttr);
                foreach ( $imgAttr[1] as $key => $attrName)
                {
                    $imgAttributes[$attrName] = str_replace('"', '', $imgAttr[2][$key]);
                }
                unset($imgAttr, $key, $attrName);

                // Attempt to identify the image src by the wp-image-{id} class
                if ( isset($imgAttributes['class']) ){
                    if ( $imageSrc = $this->identifyImageSrcByClass($imgAttributes['class'], $thumbSize) )
                    {
                        return $imageSrc;
                    }
                }

                if ( isset($imgAttributes['src']) ){
                    if ( $imageSrc = $this->identifyImageSrcBySrc($imgAttributes['src'], $thumbSize) )
                    {
                        return $imageSrc;
                    }
                }
            }
        }

        return 'http://placehold.it/330x200'; // default image

    }

    /**
     * Attempts to identify the attachments id by its class and returns its src or false if none found
     *
     * @param $class
     * @param $thumbSize
     * @return string|bool
     */
    private function identifyImageSrcByClass( $class, $thumbSize ){
        preg_match( '/wp-image-([\d]+)/i', $class, $imageID );
        if ( is_array($imageID) && isset($imageID[1]))
        {
            $imageID  = (int) $imageID[1];
            $image    = wp_get_attachment_image_src( $imageID, $thumbSize );
            if ( $image === false ){ return false;}
            return $image[0];
        }

        return false;
    }

    /**
     * Attempts to identify the attachments id by its src and returns its src or false if none found
     *
     * @param $src
     * @param $thumbSize
     * @return bool
     */
    private function identifyImageSrcBySrc( $src, $thumbSize ){

        /** @var \wpdb $wpdb */
        global $wpdb;

        // Strip any url query bits
        $src = preg_replace( '/([^?]+).*/', '\1', $src );

        // Strip any dimension info, we just want the original image path
        $src = preg_replace( '/(.+)-\d+x\d+\.(\w+)/', '\1.\2', $src );

        /** @noinspection SqlNoDataSourceInspection */
        if ( $imageID = $wpdb->get_var( $wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE guid = '%s'", $src) ) )
        {
            $image = wp_get_attachment_image_src( $imageID, $thumbSize );
            if ( $image === false ){ return false; }
            return $image[0];
        }

        return false;
    }

    private function identifyWidgetViews()
    {
        $output = array();

        $views = array_filter(scandir($this->viewsPath), function($value){
            if ($value === '.' || $value === '..' || strpos($value, 'backend') !== false ){ return false; }
            return true;
        });

        foreach ( $views as $view )
        {
            $fileContent = file_get_contents($this->viewsPath . $view );
            if ( $fileContent === false){ continue; }
            preg_match('/ViewName: (.*)/', $fileContent, $matches);
            $output[ base64_encode( $this->viewsPath . $view ) ] = isset($matches[1]) ? $matches[1] : 'Unknown';
        }

        return $output;
    }
}

add_action( 'widgets_init', function(){
    register_widget( __NAMESPACE__ . '\\RecentPostsWidget' );
});