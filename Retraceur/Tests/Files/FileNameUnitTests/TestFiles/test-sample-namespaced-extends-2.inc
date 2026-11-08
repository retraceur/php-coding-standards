<!-- Annotation has to be on the second line as errors are thrown on line 1 and errors on annotation lines are ignored. -->
phpcs:set Retraceur.Files.FileName custom_test_classes[] Some\Name\TestSample
<?php

namespace Some\OtherName;

class TestCase extends TestSample {}
/* phpcs:set Retraceur.Files.FileName custom_test_classes[] */
