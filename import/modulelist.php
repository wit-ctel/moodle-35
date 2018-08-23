<html lang="en">
  <head>
    <meta charset="utf-8">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="js/import.js"></script>

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

	<link rel="stylesheet" href="css/import.css">

</head>
<body>
	<nav>
		<img src="https://moodle.wit.ie/pluginfile.php/1/core_admin/logocompact/0x70/1534931879/moodlewit_small.png" alt="Moodle">	
	</nav>
	<div class="wrapper">
	<div class="container">
	<div class="jumbotron">
	  <h1 class="display-4">Moodle Module Importer</h1>
	  <p class="lead"><b>Note:</b> When you click import, your module will be queued for import and content will appear in your 2018-2019 module in Moodle within 24 hours.</p>
	  <hr class="my-4">
	  <p>This is the full disclaimer for people importing their modules</p>
	</div>
<?php 	
	$servername = "localhost";
	$username = "root";
	$password = "!conpw2018!";	
	$dbname = "autoimport";

	// Create connection
	$conn = new mysqli($servername, $username, $password, $dbname);
	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}
	$importUser = $_GET['import'];

	//$sql = "select * from modulelist where lecturername='". $importUser ."'";

	$sql = "SELECT modulelist.UID, modulelist.crn, modulelist.moduletitle, modulelist.lecturerid, modulelist.importflat, modulelist.importcomplete, lecturerlist.username, lecturerlist.alphanumeric FROM modulelist INNER JOIN lecturerlist ON lecturerlist.UID = modulelist.lecturerid where lecturerlist.alphanumeric = '". $importUser ."'";

	$result = $conn->query($sql);	
	// output data of each row
	while($row = $result->fetch_assoc()) {	
		$UID = $row['UID']; 	
?>
		<div class="module">
			<div class="module-title"><?php echo $row['moduletitle']; ?></div>			
			<div class="select">

				<?php
				if($row['importflat'] ==0 ){	
					//echo "<div id='UID".$UID."' class='button action import'>Import</div>";
					echo "<button id='UID".$UID."' type='button' class='action btn btn-success'>Import</button>";
				}
				if($row['importflat'] == 1 && $row['importcomplete'] == 0){
					echo "<button id='UID".$UID."' type='button' class='action btn btn-danger'>Cancel</button>";
					//echo "<div id='UID".$UID."'  class='button action cancel'>Cancel</div>";
				}	
				if($row['importflat'] == 1 && $row['importcomplete'] == 1){
					echo "<button id='UID".$UID."' type='button' class='btn btn-primary completed'>Completed</button>";
					//echo "<div id='UID".$UID."'  class='button completed'>Completed</div>";
				}			
				?>
			
			</div>
		</div>

<?php										
	}			
	$conn->close();
?>
</div>
</div>


</body>
</html>