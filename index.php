<!DOCTYPE HTML>
<html lang="en-US">
<head>
	<title>Bug-O-meter</title>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="fonts/fonts.css">
	<link rel="stylesheet" type="text/css" href="style.css">
</head>
<body class="home">
	<header class="caption">
		<a href="index.php"><img src="images/bug-o-meter.png" width="600" height="190" /></a>
	</header>
<?php
if (isset($_GET["add"])) {
	// if people have add an email-check it.
	if (isset($_POST["save"])) {
		if (!empty($_POST["bug"])) {
			$filename = "bugzilla.txt";
			$handle = fopen($filename, "r");
			$bug_emails = unserialize(fread($handle, filesize($filename)));
			fclose($handle);
			if (in_array($_POST["bug"], $bug_emails)) {
				echo "<p style=\"color:red;\">Bugzilla E-mail already added !</p>";
			} else {
				// test it
				$content =  file_get_contents("https://bugzilla.mozilla.org/page.cgi?id=user_activity.html&action=run&who=" . $_POST["bug"] . "&from=2014-10-17&to=2014-10-18&sort=when");
				if (strstr($content, "<font color=\"#FF0000\">did not match anything</font>")) {
					echo "<p style=\"color:red;\">This email is not a Bugzilla email</p>";
				} else {
					if (empty($bug_emails))
						$bug_emails = array();
					array_push($bug_emails, $_POST["bug"]);
					$handle = fopen($filename, "w+");
					fwrite($handle, serialize($bug_emails));
					fclose($handle);
					echo "<p style=\"color:green;\">Bugzilla email succefully added</p>";
				}
			}
		}
		if (!empty($_POST["git"])) {
			$filename = "github.txt";
			$handle = fopen($filename, "r");
			$git_emails = unserialize(fread($handle, filesize($filename)));
			fclose($handle);
			if (in_array($_POST["git"], $git_emails)) {
				echo "<p style=\"color:red;\">Github nickname already added !</p>";
			} else {
				// test it
				$curl = curl_init("https://api.github.com/users/" . $_POST["git"]);
				curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
				curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  
				curl_setopt($curl, CURLOPT_HTTPHEADER, array("User-Agent: Thegennok"));
				$get = curl_exec($curl);
				curl_close($curl);
				$get = utf8_encode($get); 
				$get = json_decode($get);
				if ($get->message == "Not Found") {
					echo "<p style=\"color:red;\">This is not a Github nickname</p>";
				} else {
					if (empty($git_emails))
						$git_emails = array();
					array_push($git_emails, $_POST["git"]);
					$handle = fopen($filename, "w+");
					fwrite($handle, serialize($git_emails));
					fclose($handle);
					echo "<p style=\"color:green;\">Github nickname succefully added</p>";
				}
			}
		}
	}

	// display box for get github and bugzilla account
	?>
	<p>Add your Bugzilla mail or/and your Github mail&nbsp;:<p>
	<form action="index.php?add" method="post">
		Bugzilla&nbsp;:&nbsp;<input type="text" name="bug" placeholder="e.g. mozilla@mozilla.com" /><br /><br />
		Github&nbsp;:&nbsp;<input type="text" name="git" placeholder="e.g. mozilla" /><br /><br />
		<input type="submit" value="Save" name="save">
	</form>
	<?php
	
	// show actually registered
	echo "<br /><br /><p>Bugzilla account already registered&nbsp;:</p>";
	$filename = "bugzilla.txt";
	$handle = fopen($filename, "r");
	$bug_emails = unserialize(fread($handle, filesize($filename)));
	fclose($handle);
	if (empty($bug_emails))
		echo "none.";
	else
		foreach($bug_emails as $mail)
			echo $mail . "<br />";
	echo "<p>Github account already registered&nbsp;:</p>";
	$filename = "github.txt";
	$handle = fopen($filename, "r");
	$git_emails = unserialize(fread($handle, filesize($filename)));
	fclose($handle);
	if (empty($git_emails))
		echo "none.";
	else
		foreach($git_emails as $mail)
			echo $mail . "<br />";
} else {

	$handle = fopen("bug.txt", "r");
	$b = unserialize(fread($handle, filesize("bug.txt")));
	fclose($handle);
	?>
	  <h2> Bugzilla </h2>
	    <ul>
	      <li>Bugs ouverts&nbsp;: <?=$b["zilla"]["new"];?></li>
	      <li>Patches&nbsp;: <?=$b["zilla"]["at"];?></li>
	      <li>Commentaires&nbsp;: <?=$b["zilla"]["co"];?></li>
	      <li>Revues demandées&nbsp;: <?=$b["zilla"]["ra"];?></li>
	      <li>Mise en CC&nbsp;: <?=$b["zilla"]["cc"];?></li>
	    </ul>
	    <h2>GitHub</h2>
	    <ul>
	      <li> Pull requests&nbsp;: <?=$b["git"]["pr"];?></li>
	      <li> Issues&nbsp;: <?=$b["git"]["is"];?></li>
	      <li> Fork&nbsp;: <?=$b["git"]["fo"];?></li>
	      <li> Other&nbsp;: <?=$b["git"]["ot"];?></li>
	    </ul>

	    <footer id=etherpad>
	    <p>
	      Add your Bugzilla or/and Github account <a href="?add">here</a>.
	    </p>
	    </footer>
	<?php
}
?>
</body>
</html>
