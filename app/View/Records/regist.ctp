<div class="records">
	<div class="row">
		<div class="span7">
			<h2>登録</h2>
			<?php echo $this->Form->create('Record', array('type'=>'post')); ?>
				<?php echo $this->Form->input('rss_url', array('label'=>'RSS URL')); ?>
				<?php echo $this->Form->input('password', array('label'=>'Password')); ?>
				<?php echo $this->Form->submit('次へ', array('style'=>'width: 100%;')); ?>
			<?php echo $this->Form->end(); ?>
		</div>

		<div class="span15liquid">
			<h2>Information</h2>
			<p>
				foursquare to GoogleCalenderでは、foursquareから出力されるRSSフィードを解析し、Googleカレンダーに自動で登録させることができます。
			</p>
			<p>
				foursquareには、icsファイルを出力してくれる機能があるので、Googleカレンダー上に自分のチェックインログを表示できます。しかしこれは、いつまでもデータを保存してくれるという保証がないものです。<br/>
				そこで、このサービスに登録するなら、毎日1回程度でRSSをチェックしに行くので、自動的にGoogleカレンダーへの反映を行うことができます。<br/>
				なお、RSSフィードは25件までしか出力されないので、予めicsファイルをインポートすることで、過去のチェックインをGoogleカレンダーへ登録しておくことをお勧めします。
				RSSフィードやicsファイルのリンクは、foursquareへログイン後、<?php echo $this->Html->link('こちら', 'https://ja.foursquare.com/feeds/'); ?> のページにアクセスしてください。
			</p>
		</div>
	</div>

	<br/>
	<br/>

	<div class="row">
		<div class="span7">
			<h2>解除</h2>
			<?php echo $this->Form->create('Record', array('type'=>'post', 'action'=>'deregist')); ?>
				<?php echo $this->Form->input('rss_url', array('label'=>'RSS URL')); ?>
				<?php echo $this->Form->input('password', array('label'=>'Password')); ?>
				<?php echo $this->Form->submit('次へ', array('style'=>'width: 100%;')); ?>
			<?php echo $this->Form->end(); ?>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(function() {
		$("#RecordRegistForm").bind('submit', function() {
			rss_url = $('#RecordRegistForm #RecordRssUrl');
			passwd = $('#RecordRegistForm #RecordPassword');

			isError = false;
			if (rss_url.val().length <= 0) {
				rss_url.parent().addClass('error');
				isError = true;
			}
			else if (!rss_url.val().match(/(http|ftp|https):\/\/.+/)) {
				rss_url.parent().addClass('error');
				isError = true;
			}
			else {
				rss_url.parent().removeClass('error');
			}

			if (passwd.val().length <= 0) {
				passwd.parent().addClass('error');
				isError = true;
			}
			else if (passwd.val().length <= 2) {
				passwd.parent().addClass('error');
				isError = true;
			}
			else {
				passwd.parent().removeClass('error');
			}

			if (isError) return false;
			else return true;
		});


		$("#RecordDeregistForm").bind('submit', function() {
			rss_url = $('#RecordDeregistForm #RecordRssUrl');
			passwd = $('#RecordDeregistForm #RecordPassword');

			isError = false;
			if (rss_url.val().length <= 0) {
				rss_url.parent().addClass('error');
				isError = true;
			}
			else if (!rss_url.val().match(/(http|ftp|https):\/\/.+/)) {
				rss_url.parent().addClass('error');
				isError = true;
			}
			else {
				rss_url.parent().removeClass('error');
			}

			if (passwd.val().length <= 0) {
				passwd.parent().addClass('error');
				isError = true;
			}
			else if (passwd.val().length <= 2) {
				passwd.parent().addClass('error');
				isError = true;
			}
			else {
				passwd.parent().removeClass('error');
			}

			if (isError) return false;
			else return true;
		});
	});
</script>