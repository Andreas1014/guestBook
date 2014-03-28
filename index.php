<?php

// värden för pdo
$host = "localhost";
$dbname = "guestbook";
$username = "guestbook";
$password = "123456";

// gör pdo
$dsn = "mysql:host=$host;dbname=$dbname";
$attr = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);

$pdo = new PDO($dsn, $username, $password, $attr);

?>

<?php
    $query = $_GET['query']; 
    // gets value sent over search form
     
    $min_length = 3;
    // you can set minimum length of the query if you want
     
    if(strlen($query) >= $min_length){ // if query length is more or equal minimum length then
         
        $query = htmlspecialchars($query); 
        // changes characters used in html to their equivalents, for example: < to &gt;
         
        $query = mysql_real_escape_string($query);
        // makes sure nobody uses SQL injection
         
        $raw_results = mysql_query("SELECT * FROM posts
            WHERE (`title` LIKE '%".$query."%') OR (`text` LIKE '%".$query."%')") or die(mysql_error());
             
        // * means that it selects all fields, you can also write: `id`, `title`, `text`
        // articles is the name of our table
         
        // '%$query%' is what we're looking for, % means anything, for example if $query is Hello
        // it will match "hello", "Hello man", "gogohello", if you want exact match use `title`='$query'
        // or if you want to match just full word so "gogohello" is out use '% $query %' ...OR ... '$query %' ... OR ... '% $query'
         
        if(mysql_num_rows($raw_results) > 0){ // if one or more rows are returned do following
             
            while($results = mysql_fetch_array($raw_results)){
            // $results = mysql_fetch_array($raw_results) puts data from database into array, while it's valid it does the loop
             
                echo "<p><h3>".$results['title']."</h3>".$results['text']."</p>";
                // posts results gotten from database(title and text) you can also show id ($results['id'])
            }
             
        }
        else{ // if there is no matching rows do following
            echo "No results";
        }
         
    }
    else{ // if query length is less than minimum
        echo "Minimum length is ".$min_length;
    }
?>

<?php

if($pdo)
{
	//har något postats? skriv till databasen
	if (!empty($_POST)) 
	{
		$_POST = null;
		$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT); 
		$post = htmlspecialchars(filter_input(INPUT_POST, 'post'));
		$statement = $pdo->prepare("INSERT INTO posts (date,post,user_id) VALUES (NOW(), :post, :user_id)");
		$statement->bindParam(":post", $post);
		$statement->bindParam(":user_id", $user_id);
		$statement->execute();

	}


	// visa formulär
	?>

	<form action="index.php" method="POST">
	<p>
		<label for="user_id">User: </label>
		<select name="user_id">
		<option value=0></option>
		<?php
			// <option value=1>gugge</option>
			foreach ($pdo->query("SELECT * FROM users ORDER BY name") as $row) 
			{
				echo "<option value={$row['id']}>{$row['name']}</option>";
			
			}
		?>
		</select> 
	</p>
	<p>
		<label for="post">Post: </label>
		<input type="text" name="post"/>	
	</p>
	<input type="submit" value="Post"/>
	</form>



	<?php
	// visa alla användare (ul)
	echo "<ul>";
	echo "<li><a href=\"index.php\">All users</a></li>";
	foreach ($pdo->query("SELECT * FROM users") as $row) {
		echo "<li><a href=\"?user_id={$row['id']}\">{$row['name']}</a></li>";
	} 
	echo "</ul>";
	echo "<hr />";
	// om user klickade på user så visa bara hens inlägg

	if(!empty($_GET))
	{
		$_GET = null;
		$user_id = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
		$statement = $pdo->prepare("SELECT posts.*,users.name AS username FROM posts JOIN users ON users.id=posts.user_id WHERE posts.user_id=:user_id ORDER BY posts.date DESC");
		$statement->bindParam(":user_id", $user_id);
		if($statement->execute())
		{
			while ($row = $statement->fetch()) 
			{
				echo "<p>{$row['date']} {$row['username']} <br /> {$row['post']}</p>";
			}
		}
	}
	else
	{
		foreach ($pdo->query("SELECT posts.*,users.name AS username FROM posts JOIN users ON users.id=posts.user_id ORDER BY posts.date DESC") as $row) 
		{
			echo "<p>{$row['date']} {$row['username']} <br /> {$row['post']}</p>";
		}
		// annars visa alla inlägg
	}

} 
else
{
	echo "Not Connected";
}

?>