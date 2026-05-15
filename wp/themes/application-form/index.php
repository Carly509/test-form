<?php
get_header();
?>

<main id="primary" class="application-page">
    <div class="application-shell">
        <?php
        if ( have_posts() ) :
            while ( have_posts() ) :
                the_post();
                the_content();
            endwhile;
        endif;
        ?>
    </div>
</main>

<?php
get_footer();
