<form enctype="multipart/form-data" method="POST">
    <input type="file" name="file" />
    <input type="submit" value="Send" />
</form>
<br><br><br>
<?php
var_dump($_FILES);
var_dump($_POST);
?>