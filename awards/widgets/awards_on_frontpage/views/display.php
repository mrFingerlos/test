<?php if(is_array($awards_frontpage)): ?>
<ul>
	<?php foreach($awards_frontpage as $award): ?>
		<li>
			<a href="<?php echo base_url();?>awards/<?php echo date('Y/m', $award->created_on) .'/'.$award->slug;?>">
				<span><?php echo $award->title;?></span>
				<span><?php echo $award->intro;?></span>
			</a>
		</li>
	<?php endforeach; ?>
</ul>
<?php endif; ?>
