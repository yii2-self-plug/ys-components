<?php
	$view->registerJs('
		var content = "'.$value.'";
		var ue = UE.getEditor("'.$name.'",{
			serverUrl:"'.$server.'",
			toolbars:'.$toolbars.'
		});
		ue.addListener("ready", function () {
        	ue.setContent(content);
        });
	');
?>
<script id="<?php echo $name;?>" name="<?php echo $name;?>" type="text/plain"></script>