<?php

App::import('Utility', 'Security');

/**
 * Records Controller
 *
 * @property Record $Record
 */
class RecordsController extends AppController {
	var $uses = array('Record');
	var $components = array('Zend');

	/**
	 * regist method
	 *
	 * @return void
	 */
	public function regist() {
		if ($this->request->is('post')) {
			$rss_url = $this->request->data['Record']['rss_url'];
			$password = Security::hash($this->request->data['Record']['password']);

			// すでに登録済みか判断する
			$rd = $this->Record->find('first', array('conditions'=>array('rss_url'=>$rss_url, 'password'=>$password)));
			if ($rd) {
				// カレンダー選択に遷移する
				$this->Session->write('RecordRssUrl', $rd['Record']['rss_url']);
				$this->Session->write('RecordPassword', $rd['Record']['password']);
				$this->Session->write('RecordToken', $rd['Record']['token']);

				$this->redirect(array('action'=>'calendar'));
			}
			else {
				// AuthSub認証に遷移する
				$this->Session->write('RecordRssUrl', $rss_url);
				$this->Session->write('RecordPassword', $password);
				$registedUrl = Router::url(array('action'=>'calendar'), true);
				$this->Zend->getAuthSubToken($registedUrl);
			}
		}
	}

	/**
	 * calendar method
	 *
	 * @return void
	 */
	public function calendar() {
		// Auth Sub認証ページからのリダイレクトとみなし、セッションへの登録処理を行う
		$rss_url = $this->Session->read('RecordRssUrl');
		if (!empty($rss_url) &&
			isset($this->request->query['token'])
		) {
			$token = $this->request->query['token'];

			// ワンタイムトークンをセッショントークンへ変換
			$token = $this->Zend->getAuthSubSessionToken($token);

			// セッションへ登録する
			$this->Session->write('RecordRssUrl', $rss_url);
			$this->Session->write('RecordToken', $token);

			$this->redirect(array('action'=>'calendar'));
		}
		// AuthSub認証後の登録処理が終わった状態とみなし、ページ描画を行う
		else if (!$this->request->is('post')) {
			$token = $this->Session->read('RecordToken');

			// Googleカレンダーのリストを取得する
			$calendars = $this->Zend->getCalendarList($this->Zend->getHttpClient($token));

			$this->set(compact('calendars'));
		}
		// カレンダー選択後とみなし、カレンダーの登録処理を行う
		else {
			try {
				// データ取得
				$rss_url = $this->Session->read('RecordRssUrl');
				$token = $this->Session->read('RecordToken');
				$password = $this->Session->read('RecordPassword');

				if (empty($rss_url) || empty($token)) {
					throw new Exception(__('エラー： システムエラーが発生しました。'));
				}

				// すでに登録されているかチェックする
				$rd = $this->Record->find('first', array('conditions'=>array('rss_url'=>$rss_url, 'password'=>$password)));
				if ($rd) {
					// カレンダーを上書き登録する
					$rd['Record']['token'] = $token;
					$rd['Record']['calendar_id'] = $this->data['Record']['calendar_id'];
					if (!$this->Record->save($rd)) {
						throw new Exception(__('エラー： システムエラーが発生しました。'));
					}
				}
				else {
					// カレンダーを新規登録する
					$rd = $this->Record->create();
					$rd['Record']['rss_url'] = $rss_url;
					$rd['Record']['password'] = $password;
					$rd['Record']['token'] = $token;
					$rd['Record']['calendar_id'] = $this->data['Record']['calendar_id'];
					if (!$this->Record->save($rd)) {
						throw new Exception(__('エラー： システムエラーが発生しました。'));
					}
				}

				$this->redirect(array('action'=>'registed'));
			}
			catch(Exception $e) {
				$this->Session->setFlash($e->getMessage());
				$this->redirect(array('action'=>'regist'));
			}

			$this->Session->delete('RecordPassword');
			$this->Session->delete('RecordRssUrl');
			$this->Session->delete('RecordToken');
		}
	}

	/**
	 * registed method
	 *
	 * @return void
	 */
	public function registed() {
	}

	/**
	 * regist method
	 *
	 * @return void
	 */
	public function deregist() {
		if ($this->request->is('post')) {
			$rss_url = $this->request->data['Record']['rss_url'];
			$password = Security::hash($this->request->data['Record']['password']);

			// すでに登録済みか判断する
			$rd = $this->Record->find('first', array('conditions'=>array('rss_url'=>$rss_url, 'password'=>$password)));
			if ($rd) {
				if ($this->Record->delete($rd['Record']['id'])) {
					$this->Session->setFlash(__('登録を解除しました。'));
				}
			}
			else {
				$this->Session->setFlash(__('エラー： 該当するデータが見つかりませんでした。'));
			}
		}
		$this->redirect(array('action'=>'regist'));
	}
}