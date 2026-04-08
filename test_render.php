<?php
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/public/index.php';

// We can't readily boot the kernel this way easily in a script without a controller script, so we'll just use curl to get the exact stack trace from HTML and parse the HTML to show the exception!
