<?php

$data = $this->get('data');
?>
<div class="blockItem">
<?php if($data['show_title']) echo '<div class="blockTitle">',$data['title'],'</div>'; ?>
	<div class="blockContent">	
			<?php echo $data['text'];	?>
	</div>
</div>