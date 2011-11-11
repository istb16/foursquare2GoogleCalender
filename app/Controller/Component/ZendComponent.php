<?php

App::uses('Component', 'Controller');

App::import('Vendor', 'Zend');
App::import('Vendor', 'Zend/Gdata/AuthSub');
App::import('Vendor', 'Zend/Gdata/Calendar');

class ZendComponent extends Component {

	// Controller reference
	protected $_controller = null;

	// Inner Exception
	public $InnerException = null;

	/**
	 * Constructor
	 *
	 * @param ComponentCollection $collection A ComponentCollection this component can use to lazy load its components
	 * @param array $settings Array of configuration settings.
	 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		$this->_controller = $collection->getController();
		parent::__construct($collection, $settings);
	}

	/**
	 * 現在のURLを取得
	 */
	public function getCurrentUrl() {
		// php_self をフィルタリングし、セキュリティを確保
		$php_request_uri = htmlentities(
			substr($_SERVER['REQUEST_URI'],
			0,
			strcspn($_SERVER['REQUEST_URI'], "\n\r")),
			ENT_QUOTES
		);

		if (isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on') {
			$protocol = 'https://';
		}
		else {
			$protocol = 'http://';
		}

		$host = $_SERVER['HTTP_HOST'];
		if ($_SERVER['SERVER_PORT'] != '' && (
			($protocol == 'http://' && $_SERVER['SERVER_PORT'] != '80') ||
			($protocol == 'https://' && $_SERVER['SERVER_PORT'] != '443'))
		) {
			$port = ':' . $_SERVER['SERVER_PORT'];
		}
		else {
			$port = '';
		}
		return $protocol . $host . $port . $php_request_uri;
	}

	/**
	 * AuthSub認証を行い、ワンタイムトークンを取得
	 * ワンタイムトークンは、リダイレクト後のページにQUERYで渡される
	 * @param redirectUrl リダイレクト後に遷移するページURL
 	 */
	public function getAuthSubToken($redirectUrl) {
		// AuthSub サーバーへのパラメータ
		$next = $redirectUrl;
		$scope = "http://www.google.com/calendar/feeds/";
		$secure = false;
		$session = true;

		// Auth サーバーへリダイレクト
		$authSubUrl = Zend_Gdata_AuthSub::getAuthSubTokenUri($next, $scope, $secure, $session);
		$this->_controller->redirect($authSubUrl);
	}

	/**
	 * ワンタイムトークンをセッショントークンへ変換
	 * @param token ワンタイムトークン
	 * @return セッショントークン
	 */
	public function getAuthSubSessionToken($token) {
		return Zend_Gdata_AuthSub::getAuthSubSessionToken($token);
	}

	/**
	 * トークンによるHTTP Clientの作成
	 * @param token トークン
	 * @return HTTP Client
	 */
	public function getHttpClient($token) {
		return Zend_Gdata_AuthSub::getHttpClient($token);
	}

	/**
	 * イベントの作成
	 * @param httpClient HTTP Client
	 * @param title タイトル
	 * @param where 場所
	 * @param content 説明
	 * @param startTime 開始日時
	 * @param endTime 終了日時
	 * @param userId userID (nullの場合はデフォルト）
	 * @return イベント
	 */
	public function createEvent($httpClient,
								$title, $where, $content,
								$startTime, $endTime,
								$userId = null) {
		try {
			// 新規エントリの作成
			$service = new Zend_Gdata_Calendar($httpClient);
			$entry = $service->newEventEntry();

			// イベントの情報を設定
			$entry->title = $service->newTitle($title);
			$entry->where = array($service->newWhere($where));
			$entry->content = $service->newContent($content);
			$when = $service->newWhen();
			$when->startTime = $this->date3339($startTime);
			$when->endTime = $this->date3339($endTime);
			$entry->when = array($when);

			// エントリをカレンダーサーバーにアップロード
			if ($userId) {
				$postUrl = "https://www.google.com/calendar/feeds/${userId}/private/full";
				return $service->insertEvent($entry, $postUrl);
			}
			else {
				return $service->insertEvent($entry);
			}
		}
		catch (Zend_Gdata_App_Exception $e) {
			$this->InnerException = $e;
			return false;
		}
	}

	/**
	 * カレンダーリストの取得
	 * @param httpClient HTTP Client
	 * @return カレンダーリスト
	 */
	public function getCalendarList($httpClient) {
		$service = new Zend_Gdata_Calendar($httpClient);
		try {
			return $service->getCalendarListFeed();
		}
		catch (Zend_Gdata_App_Exception $e) {
			$this->InnerException = $e;
			return false;
		}
	}

	/**
	 * Get date in RFC 3339
	 * @param timestamp datetime
	 * @return RFC 3339 format date string
	 */
	public function date3339($timestamp=0) {
		if (!$timestamp) $timestamp = time();

		$date = date('Y-m-d\TH:i:s', $timestamp);

		$matches = array();
		if (preg_match('/^([\-+])(\d{2})(\d{2})$/', date('O', $timestamp), $matches)) {
			$date .= $matches[1].$matches[2].':'.$matches[3];
		}
		else {
			$date .= 'Z';
		}

		return $date;
	}
}
