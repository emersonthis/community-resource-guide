<html>
    <head>
        <link rel="stylesheet" href="<?= plugin_dir_url(dirname(__FILE__)) . 'resource-guide.css' ?>" />
        <link rel="stylesheet" href="<?= plugin_dir_url(dirname(__FILE__)) . 'resource-guide-printmode.css' ?>" />
    </head>
    <body>
    <?php

    echo rg_list_of_resources(true);

    ?>
    </body> 
</html>