<div class="awards_post">
		<!-- Post Image-->
	    <figure>
	        <img src="<?php echo base_url();?>/uploads/awards/<?php echo $post->image; ?>" alt="" title="" />
	    </figure>
	   	<!-- Post heading -->
	<div class="post_heading">
		<h2><?php echo $post->title; ?></h2>
		<?php if($post->category->slug): ?>
		<p class="post_category">
			<?php echo lang('awards_category_label');?>: <?php echo anchor('awards/category/'.$post->category->slug, $post->category->title);?>
		</p>
		<?php endif; ?>
		<p>
			<?php echo $post->body;?>
		</p>
		
	</div>
</div>

<?php if ($post->comments_enabled): ?>
	<?php echo display_comments($post->id); ?>
<?php endif; ?>
