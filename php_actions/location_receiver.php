<?php
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['lat'], $_POST['lng'])){
    $lat = $_POST['lat'];
    $lng = $_POST['lng'];

    // echo "Received latitude : $lat, longitude: $lng\n";

    $url = "https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=$lat&lon=$lng&addressdetails=1&email=YOUR_EMAIL@example.com";
    $options = [
        'http' => [
            'header' => "User-Agent: lost-found\r\n"
        ]
    ];

    $context = stream_context_create($options);

    $response = file_get_contents($url, false, $context);

    if($response === false){
        echo "Error fetching geocoding data.";
        exit;
    }

    $json = json_decode($response, true);

    if($json && !empty($json['address'])){
       $address = $json['address'];
        $formatted_address = implode(', ', array_filter([
            $address['house_number'] ?? null,
            $address['road'] ?? null,
            $address['city'] ?? null, $address['town'] ?? null,$address['village'] ?? null,
            // $address['state_district'] ?? null,
            // $address['state'] ?? null,
            $address['postcode'] ?? null,
            $address['country'] ?? null
        ]));

        echo json_encode (["status" => "success","address"=> $formatted_address]);
    }else {
        echo json_encode(["status" => "error", "message" => "No address found."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "No valid location data received."]);
}
?>