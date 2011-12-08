<?php if ($this->method == 'create'): ?>
	<h3><?php echo lang('awards_create_title'); ?></h3>
<?php else: ?>
		<h3><?php echo sprintf(lang('awards_edit_title'), $post->title); ?></h3>
<?php endif; ?>

<?php echo form_open_multipart( uri_string(), array( 'class' => 'crud' ) ); ?>

<div class="tabs">

	<ul class="tab-menu">
		<li><a href="#awards-content-tab"><span><?php echo lang('awards_content_label'); ?></span></a></li>
		<li><a href="#awards-options-tab"><span><?php echo lang('awards_options_label'); ?></span></a></li>
	</ul>

	<!-- Content tab -->
	<div id="awards-content-tab">
		<ol>
			<li class="even">
				<label for="title"><?php echo lang('awards_title_label'); ?></label>
				<?php echo form_input('title', htmlspecialchars_decode($post->title), 'maxlength="100"'); ?>
				<span class="required-icon tooltip"><?php echo lang('required_label'); ?></span>
			</li>
			<li>
				<label for="intro"><?php echo lang('awards_intro_label'); ?></label>
				<?php echo form_input('intro', $post->intro, 'maxlenght="100"'); ?>			
			</li>
			<li class="even">
                <label for="image"><?php echo lang('awards_image_label'); ?></label>
                <?php echo form_upload('image'); ?>
                [Max <?php echo ini_get('upload_max_filesize'); ?>]
            </li>
			<li>
				<label for="status"><?php echo lang('awards_status_label'); ?></label>
				<?php echo form_dropdown('status', array('draft' => lang('awards_draft_label'), 'live' => lang('awards_live_label')), $post->status); ?><br>
				<label for="on_frontpage"><?php echo lang('awards_show_on_frontpage_label'); ?></label>
				<?php 
				$options = array(
                  '0'  => 'Hide Award',
                  '1'    => 'Show Award',
                );
				?>
				<?php echo form_dropdown('on_frontpage', $options, $post->on_frontpage); ?>
			</li>
			<li class="even">
				<label for="slug"><?php echo lang('awards_slug_label'); ?></label>
				<?php echo form_input('slug', $post->slug, 'maxlength="100" class="width-20"'); ?>
				<span class="required-icon tooltip"><?php echo lang('required_label'); ?></span>
			</li>
			<li>
				<label for="body"><?php echo lang('awards_content_label'); ?></label>
				<?php echo form_textarea(array('id' => 'body', 'name' => 'body', 'value' => $post->body, 'rows' => 2)); ?>
			</li>
	
		</ol>
	</div>

	<!-- Options tab -->
	<div id="awards-options-tab">
		<ol>
			<li>
				<label for="category_id"><?php echo lang('awards_category_label'); ?></label>
				<?php echo form_dropdown('category_id', array(lang('awards_no_category_select_label')) + $categories, @$post->category_id) ?>
					[ <?php echo anchor('admin/awards/categories/create', lang('awards_new_category_label'), 'target="_blank"'); ?> ]
			</li>
			<li class="even date-meta">
				<label><?php echo lang('awards_date_label'); ?></label>
				<div style="float:left;">
					<?php echo form_input('created_on', date('Y-m-d', $post->created_on), 'maxlength="10" id="datepicker" class="text width-20"'); ?>
				</div>
				<label class="time-meta"><?php echo lang('awards_time_label'); ?></label>
				<?php echo form_dropdown('created_on_hour', $hours, date('H', $post->created_on)) ?>
				<?php echo form_dropdown('created_on_minute', $minutes, date('i', ltrim($post->created_on, '0'))) ?>
			</li>
			<li>
				<label for="comments_enabled"><?php echo lang('awards_comments_enabled_label');?></label>
				<?php echo form_checkbox('comments_enabled', 1, $post->comments_enabled == 1); ?>
			</li>
		</ol>
	</div>

</div>

<div class="buttons float-right padding-top">
	<?php $this->load->view('admin/partials/buttons', array('buttons' => array('save', 'save_exit', 'cancel'))); ?>
</div>

<?php echo form_close(); ?>
