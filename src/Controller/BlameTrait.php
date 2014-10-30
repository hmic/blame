<?php

namespace Blame\Controller;

use Cake\Controller\Component\AuthComponent;
/**
 * Class BlameTrait
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
trait BlameTrait {

/**
 * {@inheritDoc}
 */
	public function loadModel($modelClass = null, $type = 'Table') {
		$model = parent::loadModel($modelClass, $type);
		if (
			$model->hasBehavior('Blame') &&
			(
				$this->Auth instanceof AuthComponent ||
				is_callable(array($this->Auth, 'user'))
			)
		) {
		    $model->setUserToBlame($this->Auth->user('id'));
		}
		return $model;
	}

}
