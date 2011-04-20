<?php

App::import('Component', 'Search.Prg');

class SearchComponent extends PrgComponent {

	public $defaultOptions = array(
		'modelName' => null,
		'findOptions' => 'search',
		'commonType' => 'common',
		'scope' => array(),
	);

	public function initialize(&$controller, $settings = array()) {

		$this->defaultOptions = array_merge($this->defaultOptions, $settings);
		parent::initialize($controller);

	}

	public function process($type, $options = array()) {

		$options = $this->_mergeOptions($options);
		extract($options);

		$Model = $this->_getModel($modelName);

		$this->controller->paginate = Set::merge((array)$this->controller->paginate, $this->prepare($type, $options));

		if ($this->controller->data || $this->controller->passedArgs) {

			$this->commonProcess($modelName, $options);

			$this->controller->paginate = Set::merge((array)$this->controller->paginate, $this->modelProcess($type, $options));

			return true;
		}

		return false;

	}

	public function prepare($type, $options = array()) {

		extract($this->_mergeOptions($options));

		$Model = $this->_getModel($modelName);


		if ($commonType && isset($Model->searchPresets[$commonType])) {
			$this->controller->presetVars = array_merge($Model->searchPresets[$commonType], $Model->searchPresets[$type]);
		} else {
			$this->controller->presetVars = $Model->searchPresets[$type];
		}

		return $Model->options($findOptions);

	}

	public function modelProcess($type, $options = array()) {

		extract($this->_mergeOptions($options));

		$Model = $this->_getModel($modelName);

		if ($commonType && isset($Model->searchFilters[$commonType])) {
			$Model->filterArgs = array_merge($Model->searchFilters[$commonType], $Model->searchFilters[$type]);
		} else {
			$Model->filterArgs = $Model->searchFilters[$type];
		}

		$especialMethod = $type . 'Search';
		if (method_exists($Model, $especialMethod)) {
			return $Model->$especialMethod($this->controller->passedArgs, $options);
		} elseif (method_exists($Model, 'search')) {
			return $Model->search($this->controller->passedArgs, $options + compact('type'));
		}

		return $this->_defaultModelProcess($Model, $this->controller->passedArgs);

	}

	protected function _defaultModelProcess($Model, $params) {

		if (!$Model->Behaviors->attached('Searchable')) {
			$Model->Behaviors->attach('Search.Searchable');
			$notAttached = true;
		}

		$query = array('conditions' => $Model->parseCriteria($params));

		if (isset($notAttached)) {
			$Model->Behaviors->detach('Search.Searchable');
		}

		return $query;

	}

	protected function _mergeOptions($options) {

		if (is_string($options)) {
			$options = array('findOptions' => $options);
		}

		return array_merge($this->defaultOptions, $options);
	}

	protected function _getModel($modelName) {

		if ($modelName === null) {
			$modelName = $this->controller->modelClass;
		}

		if (isset($this->controller->{$modelName})) {
			$Model = $this->controller->{$modelName};
		} elseif (isset($this->controller->{$this->controller->modelClass}->{$modelName})) {
			$Model = $this->controller->{$this->controller->modelClass}->{$modelName};
		} else {
			$Model = ClassRegistry::init($modelName);
		}

		return $Model;
	}
}