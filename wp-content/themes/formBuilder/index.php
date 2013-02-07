<?php


get_header(); ?>

		<div class="row-fluid">
			<div class="span12">

				<?php if ( have_posts() ) : ?>

					<?php /* Start the Loop */ ?>
					<?php while ( have_posts() ) : the_post(); ?>
						<?php the_content(); ?>
					<?php endwhile; ?>

				<?php else : ?>
					
				<?php endif; // end have_posts() check ?>

			</div>
		</div>

<?php get_footer(); ?>