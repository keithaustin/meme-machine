<?
    // Connect to database
    require 'db_connect.php';

    // Initialize error flag
    $error = FALSE;
    
    // If we have an image, meme creation must have been successful
    if (isset($_GET["img"]))
    {
        $img = $_GET["img"];
        $top_text = $_GET["top"];
        $bottom_text = $_GET["bottom"];
    } else
    {
        // Else we must have gotten an error
        $error = TRUE;
    }

    // Captions an image
    function make_caption_img($img, $top_text, $bottom_text)
    {
        return "
            <img src=\"makeCaption.php?photo=$img&caption1=$top_text&caption2=$bottom_text\">
        ";
    }
?>
<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<title>Meme Complete</title>
</head>
<body>
    <h1>This meme's for you!</h1>
    <p><a href="index.php">Go home</a></p>

    <?
        // If we did not get an error, print our meme to the screen
        if ($error === FALSE)
        {
            echo make_caption_img($img, $top_text, $bottom_text);
        } else {
            echo "<p>We had some trouble making your meme. Guess you're just too witty and relatable.</p>";
        }
    ?>
</body>
</html>