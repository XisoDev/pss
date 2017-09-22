
<?php
if(isset($addHtmlFooter)){
    foreach($addHtmlFooter as $val){
        echo $val;
    }
}


if($_XISO_MESSAGE_){
    if($_XISO_ERROR_ < 0) echo "<script>toastem.error('".$_XISO_MESSAGE_."');</script>";
    else echo "<script>toastem.success('".$_XISO_MESSAGE_."');</script>";
}
?>

</body>
</html>
