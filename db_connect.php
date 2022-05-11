<?php
    // Connect to the database
    $db = new mysqli('localhost', 'myusername', 'password', 'mydb');

    // Handle connection error
    if ($db->connect_error)
    {
        die("Connection failed: " . $db->connect_error);
    }

    // Gets all images from the database
    function get_images($db)
    {
        // Initialize array and query database
        $images = array();
        $res = $db->query("SELECT * FROM memegen_img");

        // If there are results, add them to the array
        if ($res->num_rows > 0) 
        {
            while ($row = $res->fetch_assoc())
            {
                $images[$row["id"]] = [
                    "path" => $row["path"],
                    "alt" => $row["alt"]
                ];
            }
        }
        
        return $images;
    }

    // Gets all memes from the database
    function get_memes($db) 
    {
        // Initialize array and query database
        $memes = array();
        $res = $db->query("SELECT * FROM memegen_memes");

        // If there are results, add them to the array
        if ($res->num_rows > 0)
        {
            while ($row = $res->fetch_assoc())
            {
                $memes[$row["id"]] = [
                    "img" => $row["img"],
                    "top_text" => $row["top_text"],
                    "bottom_text" => $row["bottom_text"],
                    "poster" => $row["poster"]
                ];
            }
        }

        return $memes;
    }

    // Adds a new meme to the database
    function insert_meme($db, $img, $top_text, $bottom_text, $poster)
    {
        // Create query
        $query = "INSERT INTO memegen_memes (img, top_text, bottom_text, poster)
                VALUES (?, ?, ?, ?)";

        // Bind parameters to the query (to help prevent SQL injection)
        $statement = $db->prepare($query);
        $statement->bind_param("isss", $img, $top_text, $bottom_text, $poster);

        // Run query
        return $statement->execute();
    }

    // Gets the filepath to an image in the database
    function get_image_path($db, $id)
    {
        $res = $db->query("SELECT * FROM memegen_img WHERE id=$id LIMIT 1");
        return $res->fetch_assoc()["path"];
    }

?>