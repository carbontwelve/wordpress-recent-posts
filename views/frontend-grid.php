<?php
/**
 * ViewName: Grid View
 * @var \Carbontwelve\Widgets\RecentPosts\RecentPostsWidget $widget
 * @var string $title
 * @var int $numberOfPosts
 * @var \WP_Query $posts
 */
?>

<?php echo $title; ?>
<ul class="grid">
<?php
    while ( $posts->have_posts() ) :
        $posts->the_post();

        if ( has_post_thumbnail() ) {
            /** @var array $image */
            $image    = wp_get_attachment_image_src(get_post_thumbnail_id(), 'medium', false);
            $imageSrc = $image[0];
        }else{
            $imageSrc = 'http://placehold.it/330x200'; // default image
        }
?>

    <li>
        <a class="preview-image" href="<?php the_permalink(); ?>" style="background-image: url('<?php echo $imageSrc; ?>');" title="<?php echo get_the_title() ? the_title('','',false) : get_the_ID(); ?>"></a>
    </li>

<?php endwhile; ?>
</ul>