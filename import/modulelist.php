<html lang="en">
  <head>
    <meta charset="utf-8">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="js/import.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>

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
	</div>
	<h4>Module Title:</h4>
<?php 	

	$servername = "localhost";
	//$username = "a565762_trklst";
	//$password = "trklst123er";
	//$dbname = "a565762_autoimport";

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
					echo "<button id='UID".$UID."' type='button' class='action btn btn-success'>Import Module Content</button>";
				}
				if($row['importflat'] == 1 && $row['importcomplete'] == 0){
					echo "<button id='UID".$UID."' type='button' class='action btn btn-danger'>Cancel Import</button>";
					//echo "<div id='UID".$UID."'  class='button action cancel'>Cancel</div>";
				}	
				if($row['importflat'] == 1 && $row['importcomplete'] == 1){
					echo "<button id='UID".$UID."' type='button' class='btn btn-primary completed'>Import Completed</button>";
					//echo "<div id='UID".$UID."'  class='button completed'>Completed</div>";
				}			
				?>
			
			</div>
		</div>

<?php										
	}			
	$conn->close();
?>
<br />
<br />





	<div class="jumbotron jumbotron-lower">
	  <p class="lead">The module listing above has been generated from the information contained in Moodle and the WIT timetabling system as of [insert date that data was generated here] and lists the modules that you are registered on as a lecturer for the current and previous academic years and that contain content in Moodle.<br /></p>
	  <hr class="my-4">	 
	  <ul> 
	  <li>The list does not include modules that have two or more lecturers assigned in a lecturing role.</li> 
	  <li>By selecting the import option, you are requesting for all content for that particular module to be imported from the previous year into the module area for that module in Moodle for the current academic year (2018-2019). </li> 
	  <li>If the above information is incorrect in any way or not related to you, please email <a href="mailto:moodle@wit.ie" target="_top">moodle@wit.ie</a>or contact our helpdesk on 051 304114. </li> 
	  <li>Further information on the importing process can be found here [insert link].</li>
		</ul>
	  <br />

	  <p>
  <a class="btn btn-info" data-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
    FAQs
  </a>
</p>
<div class="collapse" id="collapseExample">
  <div class="card card-body">
     <b>How long will it take for the import to complete? </b>
Imports will be scheduled to run every hour depending on the volume of requests and may take up to 12 hours to complete. </p>  

<p><b>How do I know that the import is complete?</b>
You can check back to this page and the status will change from ‘Import requested’ to ‘import completed’ or you can log into the module space in moodle for the current academic year and see if the content is in place </p>  

<p><b>What will be imported?</b>
All module content excluding assignments.  </p>
  </div>
</div>
	</div>
</div>
</div>


</body>
</html>