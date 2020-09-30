<?php
// During our own internal (CCL) compilation, the file may not exist (or may be broken).
// But during subsequent requests, the stubs should be available.
@include __DIR__ . '/stubs-dynamic.php';
