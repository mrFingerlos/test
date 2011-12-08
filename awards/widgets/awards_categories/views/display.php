<?php if(is_array($categories)): ?>
<ul>
	<?php foreach($categories as $category): ?>
	<li>
		<?php echo anchor("awards/category/{$category->slug}", $category->title); ?>
	</li>
<?php endforeach; ?>
</ul>
<?php endif; ?>
