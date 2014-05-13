<?php
$q=$_GET["q"];

$con = mysqli_connect('localhost','admin_system','MoxyOx4180!','admin_system');
if (!$con)
  {
  die('Could not connect: ' . mysqli_error($con));
  }


$sql="SELECT * FROM paper WHERE name = '".$q."'";

$result = mysqli_query($con,$sql);

echo "<table border='1'>
<tr>
<th>Firstname</th>
<th>Lastname</th>
<th>Age</th>
<th>Hometown</th>
<th>Job</th>
</tr>";

while($row = mysql_fetch_array($result))
  {
  echo "<tr>";
  echo "<td>" . $row['name'] . "</td>";
  echo "<td>" . $row['retail_name'] . "</td>";
  echo "<td>" . $row['size'] . "</td>";
  echo "<td>" . $row['cost'] . "</td>";
  echo "<td>" . $row['gsm'] . "</td>";
  echo "</tr>";
  }
echo "</table>";

mysqli_close($con);
?>