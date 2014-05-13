<?php 
include('../connection.php');


class Mysql {
	private $con;

function __construct() {

//connecting to mysql database
$this->con = mysqli_connect("localhost", "admin_system", "MoxyOx4180!", "admin_system");
//checking connection
	if (mysqli_connect_errno($con))
	{
	echo "Failed to connect to database: ".mysqli_connect_error();

	}

}

function list_papers() {

$result = mysqli_query($this->con,"SELECT * FROM paper");

while($row = mysqli_fetch_array($result))
  {
  echo '<option value="'.$row['ID'].'">'.$row['name'].'</option>';
  }
  
}

function get_price($ID) {

$mysqli = new mysqli("localhost", "admin_system", "MoxyOx4180!", "admin_system");

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

if ($stmt = $mysqli->prepare("SELECT cost FROM paper WHERE ID=?")) {

    /* bind parameters for markers */
    $stmt->bind_param("i", $ID);

    /* execute query */
    $stmt->execute();

    /* bind result variables */
    $stmt->bind_result($price);

    /* fetch value */
    $stmt->fetch();

    /* close statement */
    $stmt->close();

}

/* close connection */
$mysqli->close();

return $price;
	
}

function get_size($ID) {

$mysqli = new mysqli("localhost", "admin_system", "MoxyOx4180!", "admin_system");

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

if ($stmt = $mysqli->prepare("SELECT size FROM paper WHERE ID=?")) {

    /* bind parameters for markers */
    $stmt->bind_param("i", $ID);

    /* execute query */
    $stmt->execute();

    /* bind result variables */
    $stmt->bind_result($size);

    /* fetch value */
    $stmt->fetch();

    /* close statement */
    $stmt->close();
    
}
    
    /* close connection */
$mysqli->close();

return $size;
	
}


function get_class($ID) {

$mysqli = new mysqli("localhost", "admin_system", "MoxyOx4180!", "admin_system");

/* check connection */
if (mysqli_connect_errno()) {
    printf("Connect failed: %s\n", mysqli_connect_error());
    exit();
}

if ($stmt = $mysqli->prepare("SELECT class FROM paper WHERE ID=?")) {

    /* bind parameters for markers */
    $stmt->bind_param("i", $ID);

    /* execute query */
    $stmt->execute();

    /* bind result variables */
    $stmt->bind_result($class);

    /* fetch value */
    $stmt->fetch();

    /* close statement */
    $stmt->close();
    
}
    
    /* close connection */
$mysqli->close();

return $class;
	
}


}

?>