<?
    // Connect to database
    require 'db_connect.php';

    // Get meme data from POST header
    $img = $_POST["img"];
    $top_text = $_POST["top-text"];
    $bottom_text = $_POST["bottom-text"];
    $poster = $_POST["poster"];

    // Attempt to add meme to database
    if (insert_meme($db, $img, $top_text, $bottom_text, $poster))
    {
        $img_path = get_image_path($db, $img);
        header("Location: complete.php?img=$img_path&top=$top_text&bottom=$bottom_text");
    } else 
    {
        header("Location: complete.php");
    }
?>
