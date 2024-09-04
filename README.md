# Ranks
**Free to use**
This ranks plugin is free to use for your very own server! A super simplistic ranks plugin even someone with no prior coding experience can create ranks!

# API
The API is super simple

**Grab the ranks manager**
```
use wavycraft\ranks\utils\RanksManager;

$ranksManager = RanksManager::getInstance();
```
**Get the rank of a player**
```
$ranksManager->getRank($player);
```

**Checking if the rank exist and setting a rank for a player
```
You could combine the 2 methods rankExist and setRank

Make sure the rank exist within the ranks.yml

if (!$ranksManager->rankExists($rank)) {
    $player->sendMessage("The rank $rank does not exist...");
    return false;
}

Set the rank if it exist

$ranksManager->setRank($player, $rank);

For an example look at the file RanksCommand.php
```

**Remove a players rank**
```
This will remove the players current rank and set it to the default rank specifed in the ranks.yml

$ranksManager->removeRank($player);
```

**Get the ranks display name**
```
This is useful for displaying the ranks name for example when you want to get the rank name without getting the actual rank name since the actual rank name is not modifable

$ranksManager->getPlayerRankDisplay($player);
```

**Get all the ranks from the ranks.yml**
```
This returns all the ranks defined in the config

$ranksManager->getAllRanks();
```

**Create a custom tag**
```
Create a custom tag to add to your player display name and chat format

First:
public function updateTag(Player $player) {
    $money = (int)"1000";

    //"your_custom_tag" can be named anything
    //You dont need to include {} as it already does it automatically
    //Make sure to include the (string)
    //Always call the method when your tag gets updated for example when a player money balance updates to ensure that the tag gets updated
    $ranksManager->createTag("your_custom_tag", (string)$money);
}

Second:
Is it very important that you call the method inside the event PlayerJoinEvent for the tag to appear

public function onJoin(PlayerJoinEvent $event) {
    $this->updateTag($player);
}

Your tag should now be: {your_custom_tag}

Third:
Include your tag to the ranks.yml and enjoy!
ranks:
  member:
    rank_display: Member
    rank_player_tag: "{your_custom_tag} Member {playerName}"
    rank_chat_format: "{your_custom_tag} Member {playerName}: {message}"
    permissions:
      - "example.permission3"
    inheritance: []

Its that simple!
```
