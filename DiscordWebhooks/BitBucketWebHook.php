<?php
	require_once(dirname(__FILE__) . "/Config.php");
	require_once(dirname(__FILE__) . "/Client.php");
	require_once(dirname(__FILE__) . "/Embed.php");
	
	use \DiscordWebhooks\Config as Config;
	use \DiscordWebhooks\Client as Client;
	use \DiscordWebhooks\Embed as Embed;

	$JsonPayload = file_get_contents('php://input');
	$JsonObject = json_decode($JsonPayload);
	
	if ($JsonObject != null)
	{
		$UserName = $JsonObject->{"actor"}->{"username"};
		$Repository = $JsonObject->{"repository"}->{"name"};
		$Project = $JsonObject->{"project"}->{"name"};
		
		if ($JsonObject->{"push"} != null)
		{
			$Changes = $JsonObject->{"push"}->{"changes"}[0];
			$Commits = $Changes->{"commits"};
			
			foreach ($Commits as $Commit)
			{
				$CommitStr = $CommitStr . "- " . $Commit->{"message"} . "\n";
			}
			
			$webhook = new Client(Config::$TargetDiscordUrl);
			$embed = new Embed();
			$embed->description($CommitStr);
			$webhook->message($Repository . " : New push by " . $UserName)->embed($embed)->send();
		}
	}
 ?>