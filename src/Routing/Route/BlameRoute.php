<?php
namespace Blame\Routing\Route;

use Cake\Routing\Route\InflectedRoute;

class BlameRoute extends InflectedRoute {
	public function match(array $url, array $context = []) {
		if($url['controller'] == 'Creators') {
			$url['controller'] = 'Users';
		}
		if($url['controller'] == 'Modifiers') {
			$url['controller'] = 'Users';
		}
/*
		if($url['controller'] == 'Deleters') {
			$url['controller'] = 'Users';
		}
*/
		return parent::match($url, $context);
	}
}
