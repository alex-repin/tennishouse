<?php

if (class_exists('Memcached')) {
    echo "class Memcached found";
} else {
    echo "Memcached not found";
}
exit;