<?php
  require("db.php");
  
  // Get parameters from URL
  $center_lat = $_GET["lat"];
  $center_lng = $_GET["lng"];
  $radius     = $_GET["radius"];
  $mapType    = $_GET["type"];


  // Start XML file, create parent node
  $dom = new DOMDocument("1.0");
  $node = $dom->createElement("markers");
  $parnode = $dom->appendChild($node);
  
  // Opens a connection to a mySQL server
  $connection = mysqli_connect("localhost", $username, $password, $database);
  if (!$connection) {
    die("Not connected : " . mysqli_connect_errno());
  }
  
  mysqli_query($connection, "SET NAMES 'utf8'");

  $first = sprintf("SELECT * FROM markers WHERE type = '%s' ", mysqli_real_escape_string($connection, $mapType) );

// Search the rows in the markers table
  $query = sprintf("SELECT a.id, a.name, a.address, a.lat, a.lng, a.phone, a.note, a.city, ( 3959 * acos( cos( radians('%s') ) * cos( radians( a.lat ) ) * cos( radians( a.lng ) - radians('%s') ) + sin( radians('%s') ) * sin( radians( a.lat ) ) ) ) AS distance FROM (".$first.") AS a WHERE type = '%s' HAVING distance < '%s'",
    mysqli_real_escape_string($connection, $center_lat),
    mysqli_real_escape_string($connection, $center_lng),
    mysqli_real_escape_string($connection, $center_lat),
    mysqli_real_escape_string($connection, $mapType),
    mysqli_real_escape_string($connection, $radius));
 
  #var_dump($query); die();
  $result = mysqli_query($connection, $query);
  if (!$result) {
    die("Invalid query: " . mysqli_error($connection));
  }
  
  $results = array();

  // Iterate through the rows, adding XML nodes for each
  while ($row = $result->fetch_assoc()){

    array_push($results, $row);
  }

header('Content-Type: application/json');
echo json_encode($results);
?>