<?php

// Replace these parameters to match your database
$dbhost = 'localhost';
$dbuser = 'id895915_hololens';
$dbpass = 'qwerty123456';
$dbname = 'id895915_hololenszr';
$table  = 'position';

// Connect
$mysqli = new mysqli($dbhost, $dbuser, $dbpass, $dbname);

// If parameters are received
if(isset($_POST['longitude']) && isset($_POST['latitude'])){

    $error_messages = array();

    if ($mysqli->connect_errno) {
        $error_messages[] = "Couldn't connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    }

    // Prepare insert
    if (!($stmt = $mysqli->prepare("INSERT INTO `$table` (longitude,latitude) VALUES (?,?)"))) {
        $error_messages[] = "Couldn't prepare statement: (" . $mysqli->errno . ") " . $mysqli->error;
    }

    // Bind parameters x and y
    if (!$stmt->bind_param("dd", $_POST['longitude'], $_POST['latitude'])) {
        $error_messages[] = "Couldn't bind parameters: (" . $stmt->errno . ") " . $stmt->error;
    }

    // Execute the insert
    if (!$stmt->execute()) {
        $error_messages[] = "Couldn't execute the query: (" . $stmt->errno . ") " . $stmt->error;
    }

    // Prepare some data to return to the client (browser)
    $result = array(
        'success' => count($error_messages) == 0,
        'messages' => $error_messages
    );

    $stmt->close();

    // Send it
    echo json_encode($result);
    // Exit (do not execute the code below this line)
    exit();

} // end if


// Fetch all the coordinates to display them in the page
$res = $mysqli->query("SELECT longitude,latitude FROM `$table`");
$rows = array();
while ($row = $res->fetch_assoc()) {
    $rows[] = $row;
}

$mysqli->close();
?>


<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Test page</title>
    <style>
        table{ border-collapse: collapse; }
        th,td{ border: 1px solid #888; padding: .3em 2em; }
    </style>
</head>
<body>
<h1>sending data...</h1>

</table>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script>
    $(document).ready(function() {

        setInterval(function(){
            if(navigator.geolocation){
                navigator.geolocation.getCurrentPosition(saveLocation)
            }


        }, 10000);
    });

    function saveLocation(position){
        var longitude = position.coords.longitude;
        var latitude = position.coords.latitude;
        var url = window.location.href;
        $.ajax({
            'url': url,
            'method': 'post',
            'data': {'longitude': longitude, 'latitude': latitude},
            'dataType': 'json', // The server will return json
            'success': function(res){
                if(res.hasOwnProperty('success') && res.success){
                    $('#longitude, #latitude').val(''); // Empty the inputs
                    $('#coordinates').append('<tr><td>'+longitude+'</td><td>'+latitude+'</td></tr>');
                } else if(res.hasOwnProperty('messages')) {
                    alert( res.messages.join('\n') );
                }
            },
            'error': function(x, s, e){
                alert( 'Error\n' + s + '\n' + e );
            }
        });
    }

</script>

</body>
</html>