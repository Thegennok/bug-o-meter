<?php

$from = "2014-04-26";
$to = "2014-04-27";

// get buzilla count
$r = exec("python bugzilla-collect.py " . $from . " " . $to);
$r = json_decode($r);
$bug = array();
$bug["zilla"] = array();
$bug["git"] = array();
$bug["zilla"]["ra"] = sizeof($r->review_asked);
$bug["zilla"]["rg"] = sizeof($r->review_given);
$bug["zilla"]["at"] = sizeof($r->attachments);
$bug["zilla"]["cc"] = sizeof($r->cc);
$bug["zilla"]["co"] = sizeof($r->comments);
$bug["zilla"]["new"] = sizeof($r->new);

// get github count
$filename = "github.txt";
$handle = fopen($filename, "r");
$nicks = unserialize(fread($handle, filesize($filename)));
fclose($handle);
$from = strtotime($from);
$to = strtotime($to);

$bug["git"]["is"] = 0;
$bug["git"]["fo"] = 0;
$bug["git"]["pr"] = 0;
$bug["git"]["ot"] = 0;

foreach($nicks as $nick) {
	$curl = curl_init("https://api.github.com/users/" . $nick . "/events");
	curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_HTTPHEADER, array("User-Agent: Thegennok"));
	$get = curl_exec($curl);
	curl_close($curl);
	$get = utf8_encode($get);
	$get = json_decode($get, true);
	foreach($get as $event) {
		$date = substr($event["created_at"], 0, strpos($event["created_at"], "T"));
		$date = strtotime($date);
		if ($date >= $from && $date <= $to) {
			switch ($event["type"]) {
				case "IssuesEvent":
					$bug["git"]["is"]++;
					break;
				case "PullRequestEvent":
					$bug["git"]["pr"]++;
					break;
				case "ForkEvent":
					$bug["git"]["fo"]++;
					break;
				default:
					$bug["git"]["ot"]++;
					break;
			}
		}
	}
}

$handle = fopen("bug.txt", "w+");
fwrite($handle, serialize($bug));
fclose($handle);
