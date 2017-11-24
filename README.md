# University of Richmond - Schedule Generator
[![build status](http://git.home.mikedombrowski.com/mdombrowski/university-schedule-generator/badges/master/build.svg)](http://git.home.mikedombrowski.com/mdombrowski/university-schedule-generator/commits/master)
[![coverage report](http://git.home.mikedombrowski.com/mdombrowski/university-schedule-generator/badges/master/coverage.svg)](http://git.home.mikedombrowski.com/mdombrowski/university-schedule-generator/commits/master)

## What It Is
Our app accepts as many courses as a student may want to take along with all the offered sections of each course.  Then, with
our algorithm, generates all possible non-conflicting schedules.

## Where It Is
Right now the PHP version is available at http://mikedombrowski.com/ur/.

## Run Your Own
Although there are many customizations that are probably specific to only the University of Richmond, if you wanted to run your own here's how.
1. Clone this repository
2. Install dependencies using composer. `php composer.phar install` or `composer install`
3. Edit `config.php` with the correct installation directory and database login details
4. Run `importer/importExcel.php` from a web browser to create the database and import all the data
5. You now have the same installation that I do. If you are running on data that is not from UR, then you may have to change some things.

## PHP API Documentation
Available at https://MikeDombo.github.io/university-schedule-generator/html/index.html

## Theory of Operation
The new algorithm implemented by commit 9102865 is the Bron-Kerbosch maximal clique finding algorithm. I realized that the scheduling program could be thought of as a graph where vertices represent a section of a class and edges exist between vertices that are compatible (can be taken together).

Representing the problem as a graph means that possible non-conflicting schedules are maximal cliques. Therefore, to find all possible schedules, I implemented the Bron-Kerbosch maximal clique finding algorithm. This does run faster than my old algorithm and generates fewer total schedules because the old algorithm generated some schedules that were included in larger ones (sub-graphs). 

## Old Theory of Operation
Prior to commit 9102865 this is the algorithm that was used.
Our algorithm is essentially a recursive tree, which is explained more in the image below.
![Theory of Operation](http://mikedombrowski.com/wp-content/uploads/2015/10/illustration.png)
