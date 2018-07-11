<?php
class CalendarController 
{
    public $my_calendar;
    public $my_workplace;
    public $gf_calendar;
    public $gf_workplace;

    /**
     * Class constructor
     *
     * @param string    $my_calendar    My Google Calendar ID 
     * @param string    $my_workplace   The name of my workplace (Name of Google event for work)
     * @param string    $gf_calendar    My girlfriend's calendar (Her email)
     * @param string    $gf_workplace   My girlfriend's worplace (Name of Google event for work)
     */
    public function __construct($my_calendar, $my_workplace, $gf_calendar, $gf_workplace) {
        $this->my_calendar = $my_calendar;
        $this->my_workplace = $my_workplace;
        $this->gf_calendar = $gf_calendar;
        $this->gf_workplace = $gf_workplace;
    }

    /**
     * This will return the date night with my any free time between the end of 
     * my shift and start of my girlfriend's shift
     *
     * @return void
     */
    public function getTodaysPossiblity() {
        // Start Google service object
        $client = getClient();
        $service = new Google_Service_Calendar($client);

        // Get the end time of my shift
        $bf_results = $this->queryGoogleCalendarAPI($service, $this->my_calendar, $this->my_workplace);
        $bf_end_time = $this->lastShiftSinceYesterday($bf_results, 'end');

        // Get the start time of my girlfriend's shift
        $gf_results = $this->queryGoogleCalendarAPI($service, $this->gf_calendar, $this->gf_workplace);
        $gf_start_time = $this->lastShiftSinceYesterday($gf_results);
        
        // Print results
        $this->printDateNightResult($bf_end_time, $gf_start_time);
    }

    /**
     * Undocumented function
     *
     * @param   string  $service                            GoogleResource from API
     * @param   string  $calendarId                         Calendar Name
     * @param   string  $workplace                          The workplace name (Google event name)
     * @return  string  GoogleResource in the form of JSON
     */
    private function queryGoogleCalendarAPI($service, $calendarId, $workplace) {
        // Initialize parameters
        $optParams = array(
            'maxResults' => 2,
            'orderBy' => 'startTime',
            'singleEvents' => true,
            // Include yesterday to make sure we capture work event when at work
            'timeMin' => date('c', strtotime("-1 days")), 
            'q' => $workplace
        );

        // Return JSON of Google Resource
        return $service->events->listEvents($calendarId, $optParams);
    }

    /**
     * Returns either the end time or start time of shift based on person
     *
     * @param   string  $google_calendar_results    The search results from Google API
     * @param   string  $shift_time                 The type of shift time ('start' or 'end')
     * @return  string  The UTC of shift            
     */
    private function lastShiftSinceYesterday($google_calendar_results, $shift_time = 'start') {
        $temp = [];

        // Today's day is what we're checking against
        // In the form of YYYY-DD-MM
        $todays_date = explode('T', date('c'))[0];

        // Check to see if the query didn't come up empty
        if (!empty($google_calendar_results->getItems())) {
            foreach ($google_calendar_results->getItems() as $event) {

                // Check if we're looking for the start or end of shift
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

                // Parse Google's object
                $response_json = explode('T', $chose_shift);
                $time = explode('-', $response_json[1]);

                // Add it to temporary array for further parsing
                if (strpos($todays_date, $response_json[0]) !== false) {
                    array_push($temp, $time[0]);
                }
            }
        }

        // Choose either the start of a shift using the first possible element
        // Or the last possible element for a end of shift
        if ($shift_time == 'start') {
            return reset($temp);
        } else {
            return end($temp);
        }
    }

    /**
     * Let the user (me) know the times for a date night
     *
     * @param   string  $start_time Start time for final message
     * @param   string  $end_time   End time for final message
     * @return  void    
     */
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