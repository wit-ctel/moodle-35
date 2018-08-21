<?php 

define('CLI_SCRIPT', true);

// Nothing to do if config.php does not exist
$configfile = dirname(dirname(dirname(dirname(__FILE__)))).'/config.php';
if (!file_exists($configfile)) {
    fwrite(STDERR, 'config.php does not exist, cannot continue'); // do not localize
    fwrite(STDERR, "\n");
    exit(1);
}



require_once($configfile);
require_once($CFG->libdir.'/clilib.php');
require_once(dirname(dirname(__FILE__)).'/lib.php');

list($options, $unrecognized) = cli_get_params(array('title'=>null, 'startyear'=>null, 'category'=>null, 'help'=>false),
                                               array('t'=>'title', 'y'=>'startyear', 'c'=>'category'));

if ($unrecognized) {
   $unrecognized = implode("\n  ", $unrecognized);
   cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help =
    "WIT New Academic Year tool.
Set up a new academic year; copy categories and configure category structure in LMB enrolment plugin.

Options:
-t, --title             Title of the new academic year that will be created, e.g. 2013-2014.
-y, --startyear         Starting year of the new academic year, e.g. 2013.
-c, --category          The idnumber of the category from which to copy this new academic year's categories, e.g. 2012.
-h, --help              Print out this help.


Example:
\$sudo -u www-data /usr/bin/php new_acaemic_year.php
";

    echo $help;
    die;
}

$title = $options['title'];
$startyear = $options['startyear'];
$categoryyear = $options['category'];

$academic_year_cli = new academic_year_cli($title, $startyear, $categoryyear);
$academic_year_cli->perform_academic_year_rollover();
