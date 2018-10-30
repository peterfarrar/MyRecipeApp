# MyRecipeApp
This is the first version of MyRecipeApp.  It satisfies two needs:
1) I wanted to improve my PHP
2) I wanted to store recipes on line.

Development has stopped, but I learned a lot.
MyRecipeApp version two development has started with nodejs on the back end.  The front end is expected to be a SPA, probably using React.

Not stored here is the config file.  Here are the details:
Name: config.php
Contents:
<?php
$dbHost='localhost';
$dbName='recipes';
$dbUser='recipeuser';
$dbPass='recipepass';

TO state the obvious the values for dbHost/dbName/dbUser/dbPass should be assigned to the values required by your environment.
