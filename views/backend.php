<?php
/**
 * @var \Carbontwelve\Widgets\RecentPosts\RecentPostsWidget $widget
 * @var string $title
 * @var int $numberOfPosts
 * @var string $orderBy
 * @var array $availableOrders
 * @var array $availableTemplates
 * @var string $template
 */
?>
<p>
    <label for="<?php echo $widget->get_field_id( 'title' ); ?>"><?php echo __('Title:'); ?>
        <input type="text" id="<?php echo $widget->get_field_id( 'title' ); ?>" name="<?php echo $widget->get_field_name('title') ?>" value="<?php echo esc_attr( $title ); ?>" class="widefat" />
    </label>
</p>

<p>
    <label for="<?php echo $widget->get_field_id( 'numberOfPosts' ); ?>"><?php echo __('Number of posts to show:'); ?>
        <input type="text" id="<?php echo $widget->get_field_id( 'numberOfPosts' ); ?>" name="<?php echo $widget->get_field_name('numberOfPosts') ?>" value="<?php echo esc_attr( $numberOfPosts ); ?>" class="widefat" />
    </label>
</p>

<p>
    <label for="<?php echo $widget->get_field_id( 'orderBy' ); ?>"><?php echo __('Order By:'); ?>
        <select id="<?php echo $widget->get_field_id( 'orderBy' ); ?>" name="<?php echo $widget->get_field_name('orderBy') ?>" class="widefat">
            <?php foreach ($availableOrders as $orderKey => $orderTitle){ ?>
            <option value="<?php echo $orderKey; ?>" <?php if ($orderKey === $orderBy){ echo 'selected'; } ?>><?php echo $orderTitle; ?></option>
            <?php } ?>
        </select>
    </label>
</p>

<p>
    <label for="<?php echo $widget->get_field_id( 'template' ); ?>"><?php echo __('Template:'); ?>
        <select id="<?php echo $widget->get_field_id( 'template' ); ?>" name="<?php echo $widget->get_field_name('template') ?>" class="widefat">
            <?php foreach ($availableTemplates as $templateKey => $templateTitle){ ?>
                <option value="<?php echo $templateKey; ?>" <?php if ($templateKey === $template){ echo 'selected'; } ?>><?php echo $templateTitle; ?></option>
            <?php } ?>
        </select>
    </label>
</p>
