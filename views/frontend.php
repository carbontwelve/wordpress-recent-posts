<?php
/**
 * ViewName: List View
 * @var \Carbontwelve\Widgets\RecentPosts\RecentPostsWidget $widget
 * @var string $title
 * @var int $numberOfPosts
 * @var \WP_Query $posts
 */
?>

<?php echo $title; ?>
<ul class="list">
<?php
    while ( $posts->have_posts() ) :
        $posts->the_post();
?>

    <li>
        <a class="preview-image" href="<?php the_permalink(); ?>" style="background-image: url('<?php echo $widget->getPostThumbNailSrc( get_post() ); ?>');">
            <span><?php echo get_the_title() ? the_title('','',false) : get_the_ID(); ?></span>
        </a>
    </li>

<?php endwhile; ?>
</ul>