<?php
namespace Blame\Model\Behavior;

use ArrayObject;
use Cake\Event\Event;
use Cake\ORM\Behavior;
use Cake\ORM\Entity;
use Cake\ORM\Table;
use Cake\Routing\Router;

/**
 * Class BlameBehavior
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 */
class BlameBehavior extends Behavior {

/**
 * Default config
 *
 * These are merged with user-provided config when the behavior is used.
 *
 * events - an event-name keyed array of which fields to update, and when, for a given event
 * possible values for when a field will be updated are "always", "new" or "existing", to set
 * the field value always, only when a new record or only when an existing record.
 *
 * @var array
 */
	protected $_defaultConfig = [
		'implementedFinders' => [],
		'implementedMethods' => [
			'setUserToBlame' => 'setUserToBlame',
			'blameSetUser' => 'blameSetUser'
		],
		'events' => [
			'Model.beforeSave' => [
				'created_by' => 'new',
				'modified_by' => 'always'
			]
/* this is nice for e.g. a softdelete implementation too
			'Model.beforeDelete' => [
				'deleted_by' => 'existing',
			]
*/
		],
		'belongsTo' => [
			'Creators' => [
				'className' => 'Users',
				'foreignKey' => 'created_by',
			],
			'Modifiers' => [
				'className' => 'Users',
				'foreignKey' => 'modified_by',
			]
/*
			'Deleters', [
				'className' => 'Users',
				'foreignKey' => 'deleted_by',
			]
*/
		]
	];

/**
 * Current user_id, to be used. Will be set to the currently authed user by the trait.
 *
 * @var integer
 */
	protected $_user = null;

/**
 * Constructor
 *
 * If events are specified - do *not* merge them with existing events,
 * overwrite the events to listen on
 *
 * @param \Cake\ORM\Table $table The table this behavior is attached to.
 * @param array $config The config for this behavior.
 */
	public function __construct(Table $table, array $config = []) {
		parent::__construct($table, $config);
		if (isset($config['events'])) {
			$this->config('events', $config['events'], false);
		}
/*
		if (isset($config['belongsTo'])) {
			$this->config('belongsTo', $config['belongsTo'], false);
		}
*/
		if($this->config('belongsTo')) {
			foreach($this->config('belongsTo') as $model => $options) {
				if(!$table->association($model)) {
					$table->belongsTo($model, $options);
				}
			}
		}
	}

/**
 * There is only one event handler, it can be configured to be called for any event
 *
 * @param \Cake\Event\Event $event Event instance.
 * @param \Cake\ORM\Entity $entity Entity instance.
 * @throws \UnexpectedValueException if a field's when value is misdefined
 * @return true (irrespective of the behavior logic, the save will not be prevented)
 * @throws \UnexpectedValueException When the value for an event is not 'always', 'new' or 'existing'
 */
	public function handleEvent(Event $event, Entity $entity) {
		$eventName = $event->name();
		$events = $this->config('events');
		$new = $entity->isNew() !== false;

		foreach ($events[$eventName] as $field => $when) {
			if (!in_array($when, ['always', 'new', 'existing'])) {
				throw new \UnexpectedValueException(
					sprintf('When should be one of "always", "new" or "existing". The passed value "%s" is invalid', $when)
				);
			}
			if (
				$when === 'always' ||
				($when === 'new' && $new) ||
				($when === 'existing' && !$new)
			) {
				$this->_updateField($entity, $field);
			}
		}

		return true;
	}

/**
 * implementedEvents
 *
 * The implemented events of this behavior depend on configuration
 *
 * @return array
 */
	public function implementedEvents() {
		return array_fill_keys(array_keys($this->config('events')), 'handleEvent');
	}

/**
 * Get and optionally set the user to blame.
 *
 * @param $user user_id to use
 * @return void
 */
	public function setUserToBlame($user = null) {
		if ($user) {
			$this->_user = $user;
		}
		return $this->_user;
	}

/**
 * Blame the previously through setUserToBlame() set user for an entity
 *
 * For any fields configured to be updated "always" or "existing", update the value.
 * This method will overwrite any pre-existing value.
 *
 * @param \Cake\ORM\Entity $entity Entity instance.
 * @param string $eventName Event name.
 * @return bool true if a field is updated, false if no action performed
 */
	public function blameSetUser(Entity $entity, $eventName = 'Model.beforeSave') {
		$events = $this->config('events');
		if (empty($events[$eventName])) {
			return false;
		}

		$return = false;

		foreach ($events[$eventName] as $field => $when) {
			if (in_array($when, ['always', 'existing'])) {
				$return = true;
				$entity->dirty($field, false);
				$this->_updateField($entity, $field);
			}
		}

		return $return;
	}

/**
 * Update a field, if it hasn't been updated already
 *
 * @param \Cake\ORM\Entity $entity Entity instance.
 * @param string $field Field name
 * @param bool $authUser Whether to use currently authed user.
 * @return void
 */
	protected function _updateField(Entity $entity, $field) {
		if ($entity->dirty($field)) {
			return;
		}
		if ($this->_user !== null) {
			$entity->set($field, $this->_user);
		}
	}
}
