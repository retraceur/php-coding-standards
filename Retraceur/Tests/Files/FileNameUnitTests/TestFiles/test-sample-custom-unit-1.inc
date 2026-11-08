<!-- Annotation has to be on the second line as errors are thrown on line 1 and errors on annotation lines are ignored. -->
phpcs:set Retraceur.Files.FileName custom_test_classes[] My_TestClass
<?php

class TestSample extends My_TestClass {}
/* phpcs:set Retraceur.Files.FileName custom_test_classes[] */
