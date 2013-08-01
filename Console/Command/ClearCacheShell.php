<?php

App::uses('AppShell', 'Console/Command');

/**
 * ClearCacheShell
 *
 * Base Shell class to clear caches.
 */

class ClearCacheShell extends AppShell {

/**
 * all sub command
 *
 * @return void
 */
	public function all() {
		$this->basic();

		$this->out();
		$this->out(__d('ninja', 'Clearing cache done.'));
	}

/**
 * basic sub command
 *
 * @return void
 */
	public function basic() {
		foreach (Cache::configured() as $name) {
			if (Cache::clear(!empty($this->params['force']), $name)) {
				$this->out(__d('ninja', 'Caches of [%s] config were cleared.', $name));
			} else {
				$this->err(__d('ninja', 'Failed clearing caches of [%s] config.', $name));
			}
		}
	}

/**
 * Helper method to clear files fetched from glob()
 *
 * @return void
 */
	protected function _clearGlobFiles($format) {
		foreach (glob($format) as $file) {
			if (unlink($file)) {
				$this->out(__d('ninja', 'File [%s] was deleted.', $file), 1, Shell::VERBOSE);
			} else {
				$this->err(__d('ninja', 'Deleting file [%s] was failed.', $file));
			}
		}
	}

/**
 * get the option parser
 *
 * @return void
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		return $parser->description(__d('ninja', 'Base Shell class to clear caches'))
			->addSubcommand('all', [
				'help' => __d('ninja', 'Clears all caches.'),
			])
			->addSubcommand('basic', [
				'help' => __d('ninja', 'Clears caches what CakePHP handles.'),
			])
			->addOption('force', [
				'short' => 'f',
				'help' => __d('ninja', 'Forcibly clears caches no matter when they were not expired.'),
				'boolean' => true,
			])
		;
	}

}
