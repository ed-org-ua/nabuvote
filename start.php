<?php

@readfile("cached/".basename(__FILE__)) and exit;

require("system/__init__.php");

require(get_template('start'));
