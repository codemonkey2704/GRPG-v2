<?php
declare(strict_types=1);
class grpg_install_header
{
    public static ?grpg_install_header $inst = null;

    public function __construct()
    {
        ?><!DOCTYPE html>
        <html lang="en">
            <head><?php
                if (defined('BASE_URL')) {
                    ?><base href="<?php echo BASE_URL; ?>/install" /><?php
                } ?>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <meta name="description" content="A web-based GUI for installing gRPG: PDO" />
                <!--[if lte IE 8]>
                <link rel="stylesheet" href="css/layouts/side-menu-old-ie.css" />
                <![endif]-->
                <!--[if gt IE 8]><!-->
                <link rel="stylesheet" href="css/layouts/side-menu.css" />
                <!--<![endif]-->
                <link rel="stylesheet" type='text/css' href="css/message.css" />
                <link rel="stylesheet" href="https://unpkg.com/purecss@0.6.2/build/pure-min.css" integrity="sha384-UQiGfs9ICog+LwheBSRCt1o5cbyKIHbwjWscjemyBMT9YCUMZffs6UqUTd0hObXD" crossorigin="anonymous">
                <title>gRPG: PDO - Installer</title>
            </head>
            <body>
                <div id="layout">
                    <a href="#menu" id="menuLink" class="menu-link"><span>&nbsp;</span></a>
                    <div id="menu">
                        <div class="pure-menu">
                            <a class="pure-menu-heading" href="#">Menu</a>
                            <ul class="pure-menu-list">
                                <li class="pure-menu-item"><a href="index.php" class="pure-menu-link">Home</a></li>
                                <li class="pure-menu-item"><a href="readme.php" class="pure-menu-link">README</a></li>
                                <li class="pure-menu-item"><a href="install.php" class="pure-menu-link">Install</a></li>
                                <li class="pure-menu-item menu-item-divided"><a href="mailto:support@thegrpg.com" class="pure-menu-link">Contact Support</a></li>
                            </ul>
                        </div>
                    </div>
                    <div id="main"><?php
    }

    public function __destruct()
    {
        ?>            </div>
                </div>
                <script src="js/ui.js"></script>
            </body>
        </html><?php
    }

    public static function getInstance(): ?grpg_install_header
    {
        return self::$inst = new self();
    }
}
$h = grpg_install_header::getInstance();
