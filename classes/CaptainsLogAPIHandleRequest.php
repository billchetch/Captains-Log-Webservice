<?php
use chetch\api\APIException as APIException;

class CaptainsLogAPIHandleRequest extends chetch\api\APIHandleRequest{
	
	protected function processGetRequest($request, $params){
		$data = array();
		$requestParts = explode('/', $request);
		switch($requestParts[0]){
			case 'test':
				$data = array('response'=>"Employees test Yeah baby");
				break;

			case 'about':
				$data = static::about();
				break;
				
			case 'entries':
				$filter = isset($params['employee_id']) && $params['employee_id'] ? "employee_id=".$params['employee_id'] : null;
				$limit = self::getLimitFromParams($params);
				$data = LogEntry::createCollectionAsRows($params, $filter, "created DESC", $limit);
				break;

			case 'latest-entry':
				$entry = LogEntry::getLatestEntry($params);
				$data = $entry->getRowData();
				break;

			case 'crew-stats':
				$year = isset($params['year']) ? $params['year'] : null;
				$data = LogEntry::getCrewStats($year);
				break;

			case 'possible-events':
				$state = isset($params['state']) ? strtoupper($params['state']) : null;
				$data = LogEntry::getPossibleEvents($state);
				break;

			case 'state-after-event':
				$event = isset($params['event']) ? strtoupper($params['event']) : null;
				$state = isset($params['state']) ? strtoupper($params['state']) : null;
				$data = LogEntry::getStateForAfterEvent($event, $state);
				break;
			default:
				throw new Exception("Unrecognised api request $request");
				break;
			
		}
		return $data;
	}

	protected function processPutRequest($request, $params, $payload){
		
		$data = array();
		$requestParts = explode('/', $request);
		
		switch($requestParts[0]){
			case 'entry':
				if(!isset($payload['latitude']) || !isset($payload['longitude'])){
					$payload['latitude'] = 0;
					$payload['longitude'] = 0;
				}
				$entry = LogEntry::createInstance($payload);
				$entry->assertValidEvent();
				$prevEntry = LogEntry::getLatestEntry($params, $entry->getID());
				
				if($prevEntry){
					$prevEntry->assertStateTransition($entry->get("state"));
					if($entry->isEvent(LogEntry::EVENT_DUTY_CHANGE) && $prevEntry->get("employee_id") == $entry->get("employee_id")){
						throw new Exception("Cannot change duty with same person");
					}
				}

				$entry->write(true);
				$data = $entry->getRowData();
				break;

			default:
				throw new Exception("Unrecognised api request $request");
		}

		return $data;
	}

	protected function processDeleteRequest($request, $params){
		
		$data = array();
		$requestParts = explode('/', $request);
		
		switch($requestParts[0]){
			case 'entry':
				$data = LogEntry::deleteByID($requestParts[1]);
				break;
		}

		return $data;
	}
}
?>