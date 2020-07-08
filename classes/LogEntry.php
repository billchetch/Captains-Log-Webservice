<?php
class LogEntry extends \chetch\db\DBObject{
		
	const EVENT_DUTY_CHANGE = "DUTY_CHANGE";
	const EVENT_RAISE_ANCHOR = "RAISE_ANCHOR";
	const EVENT_SET_ANCHOR = "SET_ANCHOR";
	const EVENT_COMMENT = "COMMENT";
	const EVENT_ALERT = "ALERT";

	const STATE_MOVING = "MOVING";
	const STATE_IDLE = "IDLE";

	static public function initialise(){
		$t = \chetch\Config::get('LOG_TABLE', 'log_entries');
		self::setConfig('TABLE_NAME', $t);

		$tzo = self::tzoffset();
		$sql = "SELECT *, ROUND(latitude,5) AS latitude, ROUND(longitude,5) AS longitude, CONCAT(created,' ', '$tzo') AS created FROM $t";
		self::setConfig('SELECT_SQL', $sql);
		
	}

	static public function getLatestEntry($params, $beforeID = null){
		$filter = $beforeID ? "id < $beforeID" : null;
		$rows = self::createCollection($params, $filter, "created DESC", "0,1");
		return count($rows) ? $rows[0] : null;
	}

	static public function getCrewStats($year = null){
		if(!$year)$year = date('Y');
		$stats = array();
		$filter = "YEAR(created)=$year";
		$rows = self::createCollectionAsRows(null, $filter, "created ASC");

		$prevEntry = null;
		foreach($rows as $entry){
			$eid = $entry['employee_id'];
			if(!isset($stats[$eid])){
				$data = array();
				$data['entries_count'] = 0;
				$data['duration_total_'.strtolower(self::STATE_IDLE)] = 0;
				$data['duration_total_'.strtolower(self::STATE_MOVING)] = 0;
				$data['last_state'] = null;
				$data['last_event'] = null;
				$data['started_duty'] = null;
				$data['ended_duty'] = null;
				$stats[$eid] = $data;
			}

			$stats[$eid]['entries_count']++;
			$stats[$eid]['last_state'] = self::getStateForAfterEvent($entry['event'], $entry['state']);
			$stats[$eid]['last_event'] = $entry['event'];

			$peid = $prevEntry ? $prevEntry['employee_id'] : null;
			$stateChange = $stats[$eid]['last_state'] != $entry['state'];
			$crewChange = $peid != $eid;
			if($stateChange || $crewChange){
				$stats[$eid]['started_duty'] = $entry['created'];
				$stats[$eid]['ended_duty'] = null;
				if($peid && $crewChange)$stats[$peid]['ended_duty'] = $entry['created'];
			}						

			if($peid){
				$state = $entry['state'];
				$duration = strtotime($entry['created']) - strtotime($prevEntry['created']);
				$stats[$peid]['duration_total_'.strtolower($state)] += $duration;
			}			

			$prevEntry = $entry;
		}

		if($prevEntry){
			$duration = strtotime(self::now()) - strtotime($prevEntry['created']);
			$state = self::getStateForAfterEvent($prevEntry['event'], $prevEntry['state']);
			$stats[$eid]['duration_total_'.strtolower($state)] += $duration;
		}
		return $stats;
	}

	static public function getPossibleEvents($state){
		$possibleEvents = array();

		switch($state){
			case self::STATE_MOVING:
				array_push($possibleEvents, self::EVENT_SET_ANCHOR, self::EVENT_DUTY_CHANGE, self::EVENT_COMMENT, self::EVENT_ALERT);
				break;

			case self::STATE_IDLE:
				array_push($possibleEvents, self::EVENT_RAISE_ANCHOR, self::EVENT_DUTY_CHANGE, self::EVENT_COMMENT, self::EVENT_ALERT);
				break;
		}

		return $possibleEvents;
	}

	
	static public function assertPossibleEvent($event, $state){
		$possibleEvents = self::getPossibleEvents($state);
        	if(!in_array($event, $possibleEvents)){
            		throw new Exception("Event $event cannot occur in state $state");
        	}
	}

	static public function getStateForAfterEvent($event, $currentState){
		if($currentState == null)return self::STATE_IDLE;

        	self::assertPossibleEvent($event, $currentState);

        	switch($event){
            		case self::EVENT_SET_ANCHOR:
                		return self::STATE_IDLE;

		        case self::EVENT_RAISE_ANCHOR:
                		return self::STATE_MOVING;

		        case self::EVENT_COMMENT:
            		case self::EVENT_DUTY_CHANGE:
			case self::EVENT_ALERT:
                		return $currentState;
        	}

        	return null;
	}

	public $resetUploaded = true;
		
	public function __construct($rowdata){
		parent::__construct($rowdata);
	}

	public function write($readAgain = false){
		$this->remove('created');
		if($this->resetUploaded){
			$this->set('uploaded', 0);
		}
		return parent::write($readAgain);
	}

	public function assertValidEvent(){
		$event = $this->get("event");
		$state = $this->get("state");
		if($event && $state){
			self::assertPossibleEvent($event, $state);
		}
	}

	public function assertStateTransition($nextState){
		$currentState = $this->get("state");
		$event = $this->get("event");
		if($event && $currentState){
			$state = self::getStateForAfterEvent($event, $currentState);
			if($state != $nextState){
				throw new Exception("Cannot transition from $currentState to $nextState for event $event");
			}
		}
	}

	public function isEvent($event){
		return $event == $this->get("event");
	}
}