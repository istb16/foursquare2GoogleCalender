<?php
	// 表示用のデータに変換
	$calendarIds = array();
	foreach ($calendars as $c) {
		$accessLevel = strval($c->accessLevel);
		if ($accessLevel == 'read') continue;

		$userId = substr($c->id, strripos($c->id, '/') + 1);
		$calendarIds[$userId] = $c->title;
	}
?>
<div class="records">
	<h2>設定</h2>
	<?php echo $this->Form->create('Record', array('type'=>'post')); ?>
		<?php echo $this->Form->input('calendar_id', array('label'=>'カレンダー選択', 'type'=>'select', 'options'=>$calendarIds)); ?>
		<?php echo $this->Form->submit('確定', array('style'=>'width: 100%;')); ?>
	<?php echo $this->Form->end(); ?>
</div>