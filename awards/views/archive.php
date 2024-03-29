<h2 id="page_title"><?php echo lang('awards_archive_title');?></h2>
<h3><?php echo $month_year;?></h3>
<?php if (!empty($awards)): ?>
<?php foreach ($awards as $post): ?>
	<div class="awards_post">
		<!-- Post heading -->
		<div class="post_heading">
			<h2><?php echo  anchor('awards/' .date('Y/m', $post->created_on) .'/'. $post->slug, $post->title); ?></h2>
			<p class="post_date"><?php echo lang('awards_posted_label');?>: <?php echo format_date($post->created_on); ?></p>
			<?php if($post->category_slug): ?>
			<p class="post_category">
				<?php echo lang('awards_category_label');?>: <?php echo anchor('awards/category/'.$post->category_slug, $post->category_title);?>
			</p>
			<?php endif; ?>
		</div>
		<div class="post_body">
			<?php echo $post->intro; ?>          
		</div>
	</div>
<?php endforeach; ?>

<?php echo $pagination['links']; ?>

<?php else: ?>
	<p><?php echo lang('awards_currently_no_posts');?></p>
<?php endif; ?>