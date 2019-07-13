<?php
/**
 * User: qian
 * Date: 2019/6/5 11:42
 */
namespace suframe\core\components\event;

use suframe\core\traits\Singleton;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;

class EventManager implements EventManagerAwareInterface {
	use Singleton;

	protected $events;
	/**
	 * Inject an EventManager instance
	 *
	 * @param  EventManagerInterface $eventManager
	 * @return static
	 */
	public function setEventManager(EventManagerInterface $eventManager) {
		$eventManager->setIdentifiers([
			__CLASS__,
			get_called_class(),
		]);
		$this->events = $eventManager;
		return $this;
	}

	/**
	 * Retrieve the event manager
	 *
	 * Lazy-loads an EventManager instance if none registered.
	 *
	 * @return EventManagerInterface
	 */
	public function getEventManager() {
		if (null === $this->events) {
			$this->setEventManager(new \Zend\EventManager\EventManager());
		}
		return $this->events;
	}

	public static function get(){
		return EventManager::getInstance()->getEventManager();
	}
}