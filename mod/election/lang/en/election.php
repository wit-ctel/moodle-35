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
 * @package    mod_election
 * @copyright  2015 LTS.ie
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['continue'] = 'Next';
$string['confirmvote'] = 'Please confirm your vote';
$string['election'] = 'Election';
$string['election:addinstance'] = 'Add a new election';
$string['electionclose'] = 'Until';
$string['election:vote'] = 'Vote in a election';
$string['electionlist'] = 'Election list';
$string['electionlist_help'] = 'Add each candidate for this election on a separate line.';
$string['electionname'] = 'Election name';
$string['electionopen'] = 'Open';
$string['electioninprocess'] = 'The Election is still in process, please return later';
$string['electionlive'] = 'This election result is a running count only, as the election is still live';
$string['election:viewresults'] = 'View election results';
$string['election:viewprogress'] = 'View election progress';
$string['electionsaved'] = 'Your election has been saved';
$string['electiontext'] = 'election text';
$string['description'] = 'Description';
$string['modulename'] = 'Election';
$string['modulename_help'] = 'The election activity module allows students to vote for a range of specified candidates.

Election results may be published after the election closes, or not at all.

A election activity may be used to

* Elect one or more candidates for a position on a student union
* Vote for a preferred activity
* Vote for a holiday destination

This election plugin uses the single transferable vote (STV) voting system.';
$string['modulename_link'] = 'mod/election/view';
$string['modulenameplural'] = 'elections';
$string['opendate'] = 'Open Date';
$string['closedate'] = 'Close Date';
$string['candidate'] = 'Candidate';
$string['candidates'] = 'Candidates';
$string['preference'] = 'Preference';
$string['page-mod-election-x'] = 'Any election module page';
$string['pluginadministration'] = 'election administration';
$string['pluginname'] = 'election';
$string['privacy'] = 'Privacy of results';
$string['publish'] = 'Publish results';
$string['publishafteranswer'] = 'Show results to students after they answer';
$string['publishafterclose'] = 'Show results to students only after the election is closed';
$string['publishalways'] = 'Always show results to students';
$string['publishanonymous'] = 'Publish anonymous results, do not show student names';
$string['publishnames'] = 'Publish full results, showing names and their elections';
$string['publishnot'] = 'Do not publish results to students';
$string['rank'] = 'Ranking';
$string['removemyelection'] = 'Remove my election';
$string['removeresponses'] = 'Remove all responses';
$string['responses'] = 'Responses';
$string['responsesresultgraphheader'] = 'Graph display';
$string['responsesto'] = 'Responses to {$a}';
$string['results'] = 'Results';
$string['returntocourse'] = 'Return to course';
$string['savemyelection'] = 'Save my election';
$string['seats'] = 'Number of candidates that can be elected';
$string['showunanswered'] = 'Show column for unanswered';
$string['thankyou'] = 'You have voted for this election, thanks for participating';
$string['finalizemessage'] = 'Now click the Confirm Vote button to register your vote !';
$string['vote'] = 'Confirm Vote';
$string['votinglist'] = 'Voting List';
$string['linkednomination'] = 'Linked Nomination';

// Warnings and messages.
$string['cannotstore'] = 'Something is wrong, we can not store your vote. Please contact your administrator';
$string['electionnotactive'] = 'The election is not active at this moment.';
$string['cannotstoreelectionlist'] = 'The list of candidate provided is not valid, there should be more than one candidate and each should be on a separate line';
$string['opendatecannotbeinpast'] = 'The election start date can not be in the past';
$string['closedatecannotbeinpast'] = 'The election closing date can not be in the past';
$string['closedatecannotbebeforeopendate'] = 'The election end date can not be before the election start date';
$string['posistioncanonlybeusedonce'] = 'You can only assign each voting preference once';
$string['nosequentialnumbering'] = 'Your provided input is not sequential, please correct your vote';
$string['musthavenumberone'] = 'At least one of the candidates should have a number one preference';
$string['notallowedtovote'] = 'Your user role in this course does not allow you to vote';
$string['incorrectnumberofseats'] = 'You have chosen an incorrect number of seats';

// Result table.
$string['showballots'] = 'Show Ballots';
$string['elected'] = 'Elected';
$string['numcandidates'] = 'Number of Candidates';
$string['vacancies'] = 'Vacancies';
$string['ballot'] = 'Ballot';
$string['votes'] = 'Votes';
$string['ranking'] = 'Ranking';
$string['validballots'] = 'Valid Ballots';
$string['invalidballots'] = 'Invalid Ballots';
$string['quota'] = 'Quota';
$string['stage'] = 'Stage';
$string['stages'] = 'Stages';
$string['countmethod'] = 'Count method';
$string['totalvotes'] = 'Total Votes';
$string['ballotresults'] = 'Ballot Results';
$string['initialcount'] = 'Initial Count';
