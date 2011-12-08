<?php echo form_open('admin/awards/action');?>

<h3><?php echo lang('awards_list_title');?></h3>

<?php if (!empty($awards)): ?>

	<table border="0" class="table-list">
		<thead>
			<tr>
				<th><?php echo form_checkbox(array('name' => 'action_to_all', 'class' => 'check-all'));?></th>
				<th><?php echo lang('awards_post_label');?></th>
				<th class="width-10"><?php echo lang('awards_category_label');?></th>
				<th class="width-10"><?php echo lang('awards_date_label');?></th>
				<th class="width-5"><?php echo lang('awards_status_label');?></th>
				<th class="width-10"><span><?php echo lang('awards_actions_label');?></span></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="6">
					<div class="inner filtered"><?php $this->load->view('admin/partials/pagination'); ?></div>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($awards as $post): ?>
				<tr>
					<td><?php echo form_checkbox('action_to[]', $post->id);?></td>
					<td><?php echo $post->title;?></td>
					<td><?php echo $post->category_title;?></td>
					<td><?php echo format_date($post->created_on);?></td>
					<td><?php echo lang('awards_'.$post->status.'_label');?></td>
					<td>
						<?php echo anchor('admin/awards/preview/' . $post->id, lang($post->status == 'live' ? 'awards_view_label' : 'awards_preview_label'), 'rel="modal-large" class="iframe" target="_blank"') . ' | '; ?>
						<?php echo anchor('admin/awards/edit/' . $post->id, lang('awards_edit_label'));?> |
						<?php echo anchor('admin/awards/delete/' . $post->id, lang('awards_delete_label'), array('class'=>'confirm')); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div class="buttons float-right padding-top">
		<?php $this->load->view('admin/partials/buttons', array('buttons' => array('delete', 'publish'))); ?>
	</div>

<?php else: ?>
	<p><?php echo lang('awards_no_posts');?></p>
<?php endif; ?>

<?php echo form_close();?>