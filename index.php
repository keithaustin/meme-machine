<?php
    // Connect to the database
    require 'db_connect.php';

    // Get images and memes from the db
    $images = get_images($db);
    $memes = get_memes($db);

    // Creates an image thumbnail for meme creation form
    function create_image_button($id, $path, $alt)
    {
        $flag = "";
        if ($id == "1")
        {
            $flag = "checked";
        }
        return "
            <div class=\"thumbnail\">
                <input type=\"radio\" name=\"img\" value=\"$id\" id=\"img$id\" $flag>
                <label for=\"img$id\">
                    <span style=\"background-image:url('assets/images/$path')\" title=\"$alt\"></span>
                </label>
            </div>
        ";
    }

    // Creates a button to create and post for meme creation form
    function create_meme_link($img, $top_text, $bottom_text, $poster)
    {
        return "
            <li>
                <a href=\"makeCaption.php?photo=$img&caption1=$top_text&caption2=$bottom_text\">
                    <img src=\"makeCaption.php?photo=$img&caption1=$top_text&caption2=$bottom_text&width=320&height=240\">
                </a>(by $poster)
            </li>
        ";
    }
?>

<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>The Meme Machine</title>
<link rel="stylesheet" type="text/css" href="assets/styles/styles.css">
</head>

<body>
<h1>The Meme Machine</h1>
<div id="create">
  <h2>Dab on the haters!</h2>
  <form action="insert.php" method="POST">
    <?
        foreach ($images as $id => $img)
        {
            echo create_image_button($id, $img["path"], $img["alt"]);
        }
    ?>
    <label for="top-text">Top Text: </label>
    <input type="text" id="top-text" name="top-text" required><br>
    <label for="bottom-text">Bottom Text: </label>
    <input type="text" id="bottom-text" name="bottom-text" required><br>
    <label for="poster">Your name: </label>
    <input type="text" id="poster" name="poster" required><br>
    <input type="submit" value="Make my meme!">
  </form>
  
</div>
<div id="completed">
  <h2>Previously created memes:</h2>
  <p>(click to see a larger version)</p>
  <ul>
    <?
        foreach ($memes as $id => $meme)
        {
            $img_path = get_image_path($db, $meme["img"]);
            echo create_meme_link($img_path, $meme["top_text"], $meme["bottom_text"], $meme["poster"]);
        }
    ?>
  </ul>
</div>
</body>
</html>
