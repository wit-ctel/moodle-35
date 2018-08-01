## Mod election

This is the Election plugin for Moodle.

The Election plugin allows teachers and course managers to setup an election activity.
An election activity has a list of candidates that users with the student role in a
course can vote on.

The election needs to be configured with an open and closing date. While the election
is in progress students can vote for their preferred candidates by giving each of the
candidates a preference. Students can only vote once. The plugin keeps track of who has
voted separately of what the vote was. Voting is always anonymous.

This election plugin uses the STV voting system.

Quote wikipedia:

The single transferable vote (STV) is a voting system designed to achieve proportional
representation through ranked voting in multi-seat constituencies (voting districts).
[1] Under STV, an elector has a single vote that is initially allocated to their most
preferred candidate and, as the count proceeds and candidates are either elected or
eliminated, is transferred to other candidates according to the voter's stated preferences,
in proportion to any surplus or discarded votes.


In the single transferable voting system the results are calculated using this logic:

1. In order to win, each candidate needs a minimum amount of votes to reach a quota. 
The quotum is calculated using this logic

quotum = ( votes / ( candidates +1 ) ) + 1

2. The first winner(s) are those that have enough votes to 
exceed the quota.

3. For each of the winners the quotum is substracted from their total number of votes.
The remainder is then re-distributed to the other users that have not reached the quotum yet. 

4. If this results in a winner we repeat the above process. If this does not result in
a new winner the candidate with the least amount of votes gets eliminated. This candidates
votes are then re-distributed to the other candidates untill we have a winner.

## Changing Settings

Once the elections have started teachers and manager can not change any of the election
settings. To stop an election the activity needs to be removed from a course.

## Coding

The coding logic for the STV voting system was inspired by
[DrooPHP](https://github.com/pjcdawkins/DrooPHP). Created by Patrick Dawkins which in turn
was inspire by the Python project "Droop". See: http://code.google.com/p/droop/.

Icon by Re Jean Soo
