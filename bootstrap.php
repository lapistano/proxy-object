<?php

namespace lapistano\ProxyObject {

    // unfortunately the autoloader is not just require a file.
    require_once __DIR__.'/Tests/Fixtures/Dummy.php';

    // for the rest use the autoloader.
    // Use
    //   $loader->add($ns, $dir) to make a NS match to a dir not meeting PSR-0
    //   $loader->addClassMap($dirList)  to add a specific file or dir to the autolaoder.

    $loader = require_once __DIR__ . "/vendor/autoload.php";

}
