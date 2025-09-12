<?php
// Test file yang sangat sederhana
echo "PHP Test: " . PHP_VERSION;
echo "<br>Date: " . date('Y-m-d H:i:s');
echo "<br>Directory: " . __DIR__;
echo "<br>File exists test: " . (file_exists(__FILE__) ? 'YES' : 'NO');
?>