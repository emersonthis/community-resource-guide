<?php
get_header();

/* Start the Loop */
while ( have_posts() ) :
	the_post();

    echo rg_resource(get_post());


endwhile; // End of the loop.

get_footer();