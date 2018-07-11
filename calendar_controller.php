<?php
class CalendarController 
{
    public $my_calendar;
    public $my_workplace;
    public $gf_calendar;
    public $gf_workplace;

    public function __construct($my_calendar, $my_workplace, $gf_calendar, $gf_workplace) {
        $this->my_calendar = $my_calendar;
        $this->my_workplace = $my_workplace;
        $this->gf_calendar = $gf_calendar;
        $this->gf_workplace = $gf_workplace;
    }

    public function getTodaysPossiblity() {
        $client = getClient();
        $service = new Google_Service_Calendar($client);

        $bf_results = $this->queryGoogleCalendarAPI($service, $this->my_calendar, $this->my_workplace);
        $bf_end_time = $this->lastShiftSinceYesterday($bf_results, 'end');

        $gf_results = $this->queryGoogleCalendarAPI($service, $this->gf_calendar, $this->gf_workplace);
        $gf_start_time = $this->lastShiftSinceYesterday($gf_results);
        
        $this->printDateNightResult($bf_end_time, $gf_start_time);
    }

    private function queryGoogleCalendarAPI($service, $calendarId, $workplace) {
        $optParams = array(
            'maxResults' => 2,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            'timeMin' => date('c', strtotime("-1 days")),
            'q' => $workplace
        );
        return $service->events->listEvents($calendarId, $optParams);
    }

    private function lastShiftSinceYesterday($google_calendar_results, $shift_time = 'start') {
        $temp = [];
        $todays_date = explode('T', date('c'))[0];

        if (!empty($google_calendar_results->getItems())) {
            foreach ($google_calendar_results->getItems() as $event) {
                if ($shift_time == 'start') {
                    $chose_shift = $event->start->dateTime;
                    if (empty($chose_shift)) {
                        $chose_shift = $event->start->date;
                    }
                } else {
                    $chose_shift = $event->end->dateTime;
                    if (empty($chose_shift)) {
                        $chose_shift = $event->end->date;
                    }
                }

                $response_json = explode('T', $chose_shift);
                $time = explode('-', $response_json[1]);

                if (strpos($todays_date, $response_json[0]) !== false) {
                    array_push($temp, $time[0]);
                }
            }
        }

        if ($shift_time == 'start') {
            return reset($temp);
        } else {
            return end($temp);
        }
    }

    private function printDateNightResult($start_time, $end_time) {
        print('You can have date night ');
        if (!empty($start_time)) {
            print('starting at ' . $start_time);
        } else {
            print('as soon as you wake up.');
        }

        print(' \'til ');

        if (!empty($end_time)) {
            print($end_time);
        } else {
            print('the rest of the night.');
        }
    }
}