<?php

class RequestLog extends AppModel {

	public $dispalyField = 'url';

	public $options = array(
		'index' => array(
			'fields' => array(
				'RequestLog.id',
				'RequestLog.url',
				'RequestLog.status',
				'RequestLog.created',
			),
			'order' => array(
				'RequestLog.id' => 'DESC',
			),
		),
	);

	public function write($url, $request, $response) {

		$data = compact('url') + array(
			'status' => @$response['raw']['status-line'],
			'header' => @$response['raw']['header'],
			'post' => @$request['body'],
			'body' => @mb_convert_encoding($response['body'], 'UTF-8', 'UTF-8, EUC-JP, SJIS-win'),
		);

		foreach ($data as $key => $val) {
			if (empty($val)) {
				$data[$key] = '';
			}
		}

		$this->create(false);
		$this->save($data);

		return $this->getInsertId();

	}

}