<?php

App::import('Core', 'Controller');
App::import('Controller', 'App');
App::import('Controller', 'ComponentCollection');
App::import('Component', 'Zend');

App::import('Utility', 'Xml');

class RssCrawlShell extends Shell {

	var $uses = array('Record');

	public function startup() {
		$this->controller = new Controller();
		$this->Zend = new ZendComponent($this->controller->Components);
		$this->Zend->startup($this->controller);
	}

	/**
	 * メイン処理
	 */
	public function main() {
		// レコードを取得する
		$records = $this->Record->find('all', array('conditions'=>array('not'=>array('rss_url'=>null, 'token'=>null))));
		foreach($records as $rd) {
			// 念のため、インターバルを挟む
			usleep(500000);

			// データを取得する
			$rss_url = $rd['Record']['rss_url'];
			$calendar_id = $rd['Record']['calendar_id'];
			$checked_guid = $rd['Record']['checked_guid'];
			$check_guid = null;
			$httpClient = $this->Zend->getHttpClient($rd['Record']['token']);
			$isError = false;
			$isRequiredDelete = false;

			// RSS解析
			echo __('Checking: ') . $rss_url . "\n";
			try {
				$xml = @Xml::build($rss_url);
			}
			catch (Exception $e) {
				$isError = true;

				$code = $e->getCode();
				var_dump($code);
				if (($code == 0) ||
					(($code >= 400) && ($code <= 499))) {
					$isRequiredDelete = true;
				}
			}

			if (!$isError) {
				foreach($xml->channel->item as $checkin) {
					// すでにチェック済みのGUIDまで処理を行う
					$guid = strval($checkin->guid);
					if ($checked_guid == $guid) break;

					// Googleカレンダーにイベントを登録する
					$pubDate = strtotime($checkin->pubDate);
					$event = $this->Zend->createEvent(
						$httpClient,
						strval($checkin->title), strval($checkin->title), strval($checkin->description),
						$pubDate, $pubDate,
						$calendar_id
					);
					if (!$event) {
						$isError = true;

						$status = $this->Zend->InnerException->getResponse()->getStatus();
						// 無効トークンのため削除
						if (($status == 0) ||
							(($status >= 400) && ($status <= 499))) {
							$isRequiredDelete = true;
						}
						break;
					}

					if (empty($check_guid)) $check_guid = $guid;
				}
			}

			if ($isError) {
				if ($isRequiredDelete) {
					$this->Record->delete($rd['Record']['id']);
				}
			}
			else {
				// データを更新（必要な場合のみ）
				if (!empty($check_guid)) {
					$rd['Record']['checked_guid'] = $check_guid;
					if (!$this->Record->save($rd)) {
						echo __('Faild checking: error dagabase update.') . "\n";
					}
				}
			}
		}
	}
}