<?php
// This file is part of the Election plugin for Moodle
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    mod_nomination
 * @copyright  2015 LTS.ie
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


$string['pluginname'] = 'Nomination';
$string['modulename'] = 'Nomination';
$string['modulenameplural'] = 'Nominations';
$string['results'] = 'Reports';
$string['managepositions'] = 'Manage Positions';

// General info.
$string['nominationinfo'] = 'Nomination Schedule';
$string['start'] = 'Start: ';
$string['stop'] = 'Stop: ';
$string['selfnominationactive'] = 'Self Nomination Period';
$string['votingactive'] = 'Nomination Period';
$string['timenow'] = 'Current Time';

// Module User Capabilities.
$string['nomination:addinstance'] = 'Add a Nomination instance';
$string['nomination:manage'] = 'Manage a Nomination run';
$string['nomination:vote'] = 'Nominate Candidates';
$string['nomination:selfdeclare'] = 'Nominate Candidates';
$string['nomination:viewresults'] = 'View Nomination run results';
$string['nomination:viewprogress'] = 'View the Nomination progress';

// Mod Form.
$string['nominationname'] = 'Nomination Name';
$string['anonymous'] = 'Anonymous Nominations';
$string['selfstart'] = 'Self Declaration Start Date';
$string['selfstop'] = 'Self Declaration End Date';
$string['runstart'] = 'Nomination Start Date';
$string['runstop'] = 'Nomination End Date';
$string['withdrawstop'] = 'Withdraw End Date';
$string['policy'] = 'Self Declaration Nomination Policy';
$string['policy_help'] = '';
$string['datesinpasterror'] = 'Dates cannot be in the past';

$string['pluginadministration'] = 'nomination administration';

// User messages.
$string['nominationnotactive'] = 'This Nomination is not active';
$string['positionsaved'] = 'Position {$a} Saved';
$string['positionfailed'] = 'Saving Position {$a} Failed';

// Management form.
$string['positions'] = 'Positions';
$string['position'] = 'Position';
$string['minrunners'] = 'Mininum number of runners';
$string['quotum'] = 'The amount of nominees required';
$string['absquotum'] = '1. The absolute amount of nominations required';
$string['percquotum'] = '2. The number of nominations based on group percentage';
$string['quotumtype'] = 'Active quotum type';
$string['absolute'] = '1. Absolute';
$string['percentage'] = '2. Percentage';
$string['save'] = 'Save Positions';
$string['management'] = 'Manage Nomination';
$string['selquotumtype'] = 'Select the active quotum type';

// Signup form.
$string['signup'] = 'Sign up for Nomination';
$string['policyagree'] = 'Do you agree to this policy?';
$string['position'] = 'Position';
$string['runneradded'] = 'Added to Nomination list';
$string['runneraddfailed'] = 'Adding Nominee failed';
$string['runnerexists'] = 'This runner has already been added for this position';
$string['runnerupdated'] = 'Nominee updated';

// Nominee actions
$string['withdraw'] = 'Withdraw';
$string['editsettings'] = 'Edit';
$string['deleterunner'] = 'Delete';
$string['confirmdelete'] = 'Confirm Delete';
$string['confirmwithdraw'] = 'Confirm Withdraw';
$string['areyousuredelete'] = 'Are you sure you want to delete this Nominee?';
$string['areyousurewithdraw'] = 'Are you sure you want to withdraw from Nomination?';
$string['mustagree'] = 'You have to agree to the policy to be a runner in this Nomination';
$string['managerunners'] = 'Manage Nominees';
$string['nopositions'] = 'Please add positions first';
$string['norunnersyet'] = 'No available Nominees';
$string['addnominee'] = 'Add Nominee';
$string['nominationremoved'] = 'Nominee removed <a href="{$a}"> Return </a>';

// Vote actions
$string['voteposition'] = 'Nominate {$a}';
$string['nominate'] = 'Nominate';
$string['candidates'] = 'Candidates';
$string['selectcandidate'] = 'Select a Candidate';
$string['nominatie'] = 'Nominate';
$string['thankyou'] = 'You have nominated your preferred candidates, thanks for participating';
$string['returntocourse'] = 'Return to course';
$string['musthavevote'] = 'You have to select a candidate';
$string['novoteself'] = 'Nominating yourself is not allowed';

// Reporting
$string['nominationinprocess'] = 'Nomination in progress';
$string['winner'] = 'Nomination winner';
$string['nominee'] = 'Nominee';
$string['nominees'] = 'Nominees';
$string['numnominations'] = 'Nr of nominations';
$string['nominated'] = 'Provisional Candidates';
$string['groupsize'] = 'Group size: ';
