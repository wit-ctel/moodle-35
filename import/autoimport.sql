create database autoimport;

create table modulelist(UID int, crn int, moduletitle varchar(255), deptname varchar(255), lecturername varchar(255), lecturerid int, importflag int, importcomplete int);

create table lecturerlist(UID int, username varchar(255), email varchar(255), alphanumeric varchar(255));
