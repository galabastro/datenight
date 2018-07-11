<?php
require_once __DIR__ . '/.env.php';
require_once __DIR__ . '/calendar_controller.php';
require __DIR__ . '/vendor/autoload.php';

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Google Calendar API PHP Quickstart');
    $client->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
    $client->setAuthConfig('client_secret.json');
    $client->setAccessType('offline');

    // Load previously authorized credentials from a file.
    $credentialsPath = expandHomeDirectory('credentials.json');
    if (file_exists($credentialsPath)) {
        $accessToken = json_decode(file_get_contents($credentialsPath), true);
    } else {
        // Request authorization from the user.
        $authUrl = $client->createAuthUrl();
        printf("Open the following link in your browser:\n%s\n", $authUrl);
        print 'Enter verification code: ';
        $authCode = trim(fgets(STDIN));

        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

        // Store the credentials to disk.
        if (!file_exists(dirname($credentialsPath))) {
            mkdir(dirname($credentialsPath), 0700, true);
        }
        file_put_contents($credentialsPath, json_encode($accessToken));
        printf("Credentials saved to %s\n", $credentialsPath);
    }
    $client->setAccessToken($accessToken);

    // Refresh the token if it's expired.
    if ($client->isAccessTokenExpired()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
    }
    return $client;       
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path)
{
    $homeDirectory = getenv('HOME');
    if (empty($homeDirectory)) {
        $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
    }
    return str_replace('~', realpath($homeDirectory), $path);
}

// // Get the API client and construct the service object.
// $client = getClient();
// $service = new Google_Service_Calendar($client);

// // Print the next 10 events on the user's calendar.
// $calendarId = 'primary';
// $optParams = array(
//   'maxResults' => 2,
//   'orderBy' => 'startTime',
//   'singleEvents' => true,
//   'timeMin' => date('c', strtotime("-1 days")),
//   'q' => $ENV['my_work']
// );
// $bf_results = $service->events->listEvents($calendarId, $optParams);

// $bf_end_times = [];
// $todays_date = explode('T', date('c'))[0];

// if (empty($bf_results->getItems())) {
//     // print "No events found.\n";
// } else {
//     // print "My Events:<br>";
//     foreach ($bf_results->getItems() as $event) {
//         $end = $event->end->dateTime;
//         if (empty($end)) {
//             $end = $event->end->date;
//         }
//         $response_json = explode('T', $end);
//         $time = explode('-', $response_json[1]);
//         // print($response_json[0]);
//         // print($time[0]);
//         if (strpos($todays_date, $response_json[0]) !== false) {
//             array_push($bf_end_times, ['end_time' => $time[0]]);
//         }
//         // printf("%s (%s)<br>", $event->getSummary(), $end);
//     }
// }

// // print_r($bf_end_times);
// // print('<br />');

// $calendarId = $ENV['girlfriend_gmail'];
// $optParams = array(
//   'maxResults' => 2,
//   'orderBy' => 'startTime',
//   'singleEvents' => true,
//   'timeMin' => date('c', strtotime("-1 days")),
//   'q' => $ENV['girlfriend_work']
// );
// $gf_results = $service->events->listEvents($calendarId, $optParams);

// $gf_start_times = [];

// if (empty($gf_results->getItems())) {
//     // print "No events found.<br>";
// } else {
//     // print "Girlfriend's Events:<br>";
//     foreach ($gf_results->getItems() as $event) {
//         $start = $event->start->dateTime;
//         if (empty($start)) {
//             $start = $event->start->date;
//         }
//         $response_json = explode('T', $start);
//         $time = explode('-', $response_json[1]);
//         // print($response_json[0]);
//         // print($time[0]);
//         if (strpos($todays_date, $response_json[0]) !== false) {
//             array_push($gf_start_times, ['start_time' => $time[0]]);
//         }
//         // printf("%s (%s)<br>", $event->getSummary(), $start);
//     }
// }

// $todays_date = explode('T', date('c'))[0];
// $gf_start_times = array_filter($gf_start_times, function($item) use ($todays_date) {
//     return strpos($item, $todays_date) !== false;
// });

// print('You can have date night ');
// if (count($bf_end_times) > 0) {
//     print('starting at ' . $bf_end_times[0]['end_time']);
// } else {
//     print('as soon as you wake up.');
// }

// print(' \'til ');

// if (count($gf_start_times) > 0) {
//     print($gf_start_times[0]['start_time']);
// } else {
//     print('the rest of the night.');
// }

// print('<br />========================================<br />');

// $calendarController = new CalendarController();
// $calendarController->getTodaysPossiblity();
