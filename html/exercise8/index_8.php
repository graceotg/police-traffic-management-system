<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title></title>
</head>
<body>
	<form method = "POST">
		Name: <input type = "text" name = "name" ><br/>
		Phone: <input type = "text" name="phone"><br/>
		<input type = "submit" value= "Add Record">
	</form>
	
	<?php
		// MySQL database information
		$servername = "mariadb";
		$username = "root";
		$password = "rootpwd";
		$dbname = "mydatabase";


		// opening the connection to the database
		$conn = mysqli_connect($servername, $username, $password, $dbname);		
		
		// mysqli_connect_errno() will return zero if there is a connection, and an error number if no connection 
		if (mysqli_connect_errno())
		{
			echo "Failed to connect to MySQL:". mysqli_connect_error();
			die();
		}

		// else
		// 	echo "MySQL connection OK<br/><br/>";
		// useful for testing



		// construct the SELECT query

		$sql = "SELECT * FROM People ORDER BY Name;";
		
		// send query to database

		$result = mysqli_query($conn, $sql);

		//return the number of rows
		echo mysqli_num_rows($result)." rows<br/>";

		if (!$result) 
		{
			echo "Database is empty";
		} 
		else
		{

			echo "<ul>";
				while($row = mysqli_fetch_assoc($result))
				{

					echo "<li>";
					echo $row["Name"];
					echo " (phone: ".$row["PhoneNumber"]. ")";
					$id = $row["ID"];
					echo " <a href='?del=$id'>delete</a>";
					echo "<br/>";
					echo "</li>";
				} 
			echo "</ul>";
		}

		if (isset($_POST['name']) && isset($_POST['phone']) && $_POST['name'] != "" && $_POST['phone'] != "")
		{
			$sql = "INSERT INTO People(Name, PhoneNumber) VALUES 
			('".$_POST['name']."',".$_POST['phone'].");";
			$result = mysqli_query($conn,$sql);
		}

		if ($_GET['del']!="")
		{
			$sql = "DELETE FROM PEOPLE 
					WHERE ID =". $_GET['del'].";";
			$result = mysqli_query($conn,$sql);
		}

		// closing the connection to the database
		mysqli_close($conn);


	?>
</body>
</html>