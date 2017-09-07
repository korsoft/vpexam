<?php
/**
 * Script que tiene el footer de las paginas de physician
 *
 * PHP version 5
 *
 * @category  Footer_physician
 * @package   Includes
 * @author    Francisco Eliseo Navarro Lara <francisco.navarro@korsoftcorp.com>
 * @copyright 2017 Korsoft Corp All Rights Reserved
 * @link      .
 */
?>
        </main>
        <?php 
            foreach( $_arrScripts as $strScripts ){
                echo '<script type="text/javascript" src="' . $strScripts . '"></script>';
            }
        ?>
        <script type="text/javascript">
            setPhysicianId(<?php echo $_SESSION['user_id'] ?>);
            $(document).ready(function() {
                var slideout = new Slideout({
                    'panel': $('#panel')[0],
                    'menu': $('#menu')[0],
                    'padding': 256,
                    'tolerance': 70
                });
                $('.toggle-button').on('click', function() {
                    slideout.toggle();
                });
            });
        </script>
    </body>
</html>
