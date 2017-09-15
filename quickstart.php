<?php
require_once __DIR__ . '/vendor/autoload.php';


define('APPLICATION_NAME', 'Google Calendar API PHP Quickstart');
define('CREDENTIALS_PATH', '/home/maximillian66/.credentials/calendar-php-quickstart.json');
define('CLIENT_SECRET_PATH', __DIR__ . '/client_secret.json');
// If modifying these scopes, delete your previously saved credentials
// at ~/.credentials/calendar-php-quickstart.json
define('SCOPES', implode(' ', array(
  Google_Service_Calendar::CALENDAR_READONLY)
));

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient() {
  $client = new Google_Client();
  $client->setApplicationName(APPLICATION_NAME);
  $client->setScopes(SCOPES);
  $client->setAuthConfig(CLIENT_SECRET_PATH);
  $client->setAccessType('offline');

  // Load previously authorized credentials from a file.
  $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
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
    if(!file_exists(dirname($credentialsPath))) {
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
function expandHomeDirectory($path) {
  $homeDirectory = getenv('HOME');
  if (empty($homeDirectory)) {
    $homeDirectory = getenv('HOMEDRIVE') . getenv('HOMEPATH');
  }
  return str_replace('~', realpath($homeDirectory), $path);
}
?>


<!DOCTYPE html>
<html>
<head>
	<link type="text/css" href="css/jq.year_planner.css" rel="stylesheet" />
</head>
<body>
	<div class='yearViewContainer'>
		<div id='yearPlugin' class='yearViewDiv'></div>
	</div>
	<script src="http://code.jquery.com/jquery.js"></script>
	<script src="js/jquery.ezpz_tooltip.min.js"></script>
	<script src="js/date.js"></script>
	<script src="js/jq.year_planner.js"></script>
	<script>
		var YearModel = {
			//BankHolidays : [{day:1,month:0},{day:4,month:0},{day:2,month:3},{day:5,month:3},{day:3,month:4},{day:31,month:4},{day:30,month:7},{day:24,month:11},{day:27,month:11},{day:28,month:11}],
			Events:[<?php 
// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Calendar($client);
// Print the all events of user's calendars
$calendarId = 'primary';
$optParams = array(
  'orderBy' => 'startTime',
  'singleEvents' => TRUE,
  'timeMin' => date('c'),
);
    $results = $service->events->listEvents($calendarId, $optParams);
    $calendararray = [];
    foreach ($results->getItems() as $event) {
        $start = $event->start->date;
    
        if (empty($start)) {
            $start = $event->start->dateTime;
        }
        $end = $event->end->date;
        
        if (empty($end)) {
            $end = $event->end->dateTime;
        }
        
        $dt = new DateTime($start);
        $event->getSummary();
        $tday = $dt->format('d') - 1;
        $tmonth = $dt->format('m');
        $tyear = $dt->format('Y');
        
        $edt = new DateTime($end);
        $etday = $edt->format('d') -1;
        $etmonth = $edt->format('m');
        $etyear =   $edt->format('Y');
        
        $startdate = $dt->format('Y-m-d');
        $enddate = $edt->format('Y-m-d');
        
        //var_dump($startdate);
        
        $timecalc = abs((strtotime($enddate) - (60*60*24)) - (strtotime($startdate) - (60*60*24)));
        $years = floor($timecalc / (365*60*60*24));
        $months = floor(($timecalc - $years * 365*60*60*24) / (30*60*60*24));
        $days = floor(($timecalc / (60*60*24)));
        
        //Danke ben
        
        $diffdays = $days;
        
        $timediff = $days;
        
        if($diffdays == 0){
         $cmlength = $diffdays + 1;
        }else{
            $cmlength = $diffdays;
        }
        
        $caption = substr($event->getSummary(), 0,5);
       // var_dump($caption);
     $cm = array_push($calendararray , '{day:'.$tday.', month:'.$tmonth.', length:'.intval($cmlength).' ,caption:"'.$caption.'" ,note:"'.$event->getSummary().'"}');
    }
      $cm = implode(",",$calendararray);
     echo $cm;
?>]
		};

		
		$('.yearViewDiv').css( 'width', ($(window).width()-10) + 'px');
		$('.yearViewDiv').css( 'height', ($(window).height()-10) + 'px');
			
		$(function() {
			$("#yearPlugin").yearPlanner();
		});

		$(document).ready(function() {
			var hols = YearModel.BankHolidays;
			var events = YearModel.Events;
			for (var h in hols) $("#yearPlugin").yearPlanner('addBankHoliday', hols[h]);
			for (var e in events) $("#yearPlugin").yearPlanner('addEvent', events[e]);
			
		});

	</script>
	<script type="text/javascript">
	$(document).ready(function() {
		
	});
	</script>
</body>
</html>