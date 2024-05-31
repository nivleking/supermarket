<?php
require 'vendor/autoload.php';
use Laudis\Neo4j\ClientBuilder;

$client = new MongoDB\Client("mongodb://localhost:27017");
$clientNeo = ClientBuilder::create()
    ->withDriver('default', 'bolt://neo4j:password@localhost:7687')
    ->build();
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "supermarket";

// $conn = new mysqli($servername, $username, $password, $dbname);

// if ($conn->connect_error) {
//     die("Connection failed: " . $conn->connect_error);
// }
?>