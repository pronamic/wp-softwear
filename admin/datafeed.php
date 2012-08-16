<div class="wrap">
	<?php screen_icon('softwear'); ?>

	<h2>
		<?php _e('Softwear Datafeed', 'softwear'); ?>
	</h2>

	<?php 
	
	$data = Pronamic_Softwear_Plugin::getData();
	
	if($data === false): ?>
	
	<p>
		<?php 
		
		sprintf(
			__('The Softwear datafeed could not be loaded from the URL: %s', 'software') . 
			sprintf('<a href="%s">%s</a>', esc_attr($url), $url)
		);
		
		?>
	</p>

	<?php else: ?>

	<div class="tablenav top"></div>

	<table cellspacing="0" class="widefat fixed">
		<?php $firstRow = array_shift($data); ?>

		<?php foreach(array('thead', 'tfoot') as $tag): ?>

		<<?php echo $tag; ?>>
			<tr>
				<?php foreach($firstRow as $value): ?>
				<th scope="col" class="manage-column">
					<?php echo $value; ?>
				</th>
				<?php endforeach; ?>
			</tr>
		</<?php echo $tag; ?>>

		<?php endforeach; ?>

		<tbody>
			<?php foreach($data as $row): ?>
			<tr>
				<?php foreach($row as $column): ?>
				<td>
					<?php echo $column; ?>
				</td>
				<?php endforeach; ?>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	
	<?php endif; ?>
</div>