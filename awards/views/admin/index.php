<?php if ($awards): ?>

	<?php echo form_open('admin/awards/action'); ?>

	<table border="0" class="table-list">
		<thead>
			<tr>
				<th width="20"><?php echo form_checkbox(array('name' => 'action_to_all', 'class' => 'check-all')); ?></th>
				<th><?php echo lang('awards_award_label'); ?></th>
				<th><?php echo lang('awards_category_label'); ?></th>
				<th><?php echo lang('awards_date_label'); ?></th>
				<th><?php echo lang('awards_written_by_label'); ?></th>
				<th><?php echo lang('awards_status_label'); ?></th>
				<th width="320" class="align-center"><span><?php echo lang('awards_actions_label'); ?></span></th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="7">
					<div class="inner"><?php $this->load->view('admin/partials/pagination'); ?></div>
				</td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($awards as $post): ?>
				<tr>
					<td><?php echo form_checkbox('action_to[]', $post->id); ?></td>
					<td><?php echo $post->title; ?></td>
					<td><?php echo $post->category_title; ?></td>
					<td><?php echo format_date($post->created_on); ?></td>
					<td>
					<?php if ($post->author): ?>
						<?php echo anchor('user/' . $post->author_id, $post->author->display_name, 'target="_blank"'); ?>
					<?php else: ?>
						<?php echo lang('awards_author_unknown'); ?>
					<?php endif; ?>
					</td>
					<td><?php echo lang('awards_'.$post->status.'_label'); ?></td>
					<td class="align-center buttons buttons-small">
						<?php echo anchor('admin/awards/preview/' . $post->id, lang($post->status == 'live' ? 'awards_view_label' : 'awards_preview_label'), 'rel="modal-large" class="iframe button preview" target="_blank"'); ?>
						<?php echo anchor('admin/awards/edit/' . $post->id, lang('awards_edit_label'), 'class="button edit"'); ?>
						<?php echo anchor('admin/awards/delete/' . $post->id, lang('awards_delete_label'), array('class'=>'confirm button delete')); ?>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>

	<div class="buttons align-right padding-top">
		<?php $this->load->view('admin/partials/buttons', array('buttons' => array('delete', 'publish'))); ?>
	</div>

	<?php echo form_close(); ?>

<?php else: ?>
	<div class="blank-slate">
		<h2><?php echo lang('awards_currently_no_posts'); ?></h2>
	</div>
<?php endif; ?>