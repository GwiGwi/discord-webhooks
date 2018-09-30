<?php
	require_once(dirname(__FILE__) . "/Config.php");
	require_once(dirname(__FILE__) . "/Client.php");
	require_once(dirname(__FILE__) . "/Embed.php");
	
	use \DiscordWebhooks\Config as Config;
	use \DiscordWebhooks\Client as Client;
	use \DiscordWebhooks\Embed as Embed;
	
	if ($_SERVER['HTTP_X_GITLAB_TOKEN'] != Config::$SecretToken)
		return;

	$JsonPayload = file_get_contents('php://input');
	$JsonObject = json_decode($JsonPayload);
	
	if (!$JsonObject)
		return;
	
	$UserName = $JsonObject->{"user_name"};
	$Repository = $JsonObject->{"repository"}->{"name"};
	$Project = $JsonObject->{"project"}->{"name"};
		
	if ($JsonObject->{"commits"} != null)
	{
		$Commits = $JsonObject->{"commits"};
		
		$OldCommitStr = "";
		$CommitStr = "";
		foreach ($Commits as $Commit)
		{			
			$NewMessage = "- " . $Commit->{"message"} . "\n";
			if (strlen($NewMessage) > 2047)		//discord limit description size
			{
				//discard it
				continue;
			}
			
			$CommitStr = $CommitStr . $NewMessage;
			if (strlen($CommitStr) > 2047)		//discord limit description size
			{
				//send OldCommitStr
				$webhook = new Client(Config::$TargetDiscordUrl);
				$embed = new Embed();
				$embed->description($OldCommitStr);
				$webhook->message($Repository . " : New push by " . $UserName)->embed($embed)->send();

				$CommitStr = $NewMessage;
			}
			$OldCommitStr = $CommitStr;
		}
		
		if (strlen($CommitStr) > 0)
		{
			$webhook = new Client(Config::$TargetDiscordUrl);
			$embed = new Embed();
			$embed->description($CommitStr);
			$webhook->message($Repository . " : New push by " . $UserName)->embed($embed)->send();
		}
	}
 ?>