<?php 

/* Template Name: Resources List */

get_header();
?>

<article <?php post_class(); ?>>


<?php rg_show_resource_filters(); ?>

<hr>

<?php rg_list_of_resources(); ?>

</article>
<?php
get_footer();