<?php
function BadName() {}
do_action( 'my-hook' ); // phpcs:ignore Retraceur.NamingConventions.ValidHookName.UseUnderscores
do_action( 'my-other-hook' );
error_log( 'x' );
$r = @file_get_contents( 'x' );
