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
2. Install dependencies using composer. `php composer.phar install`
3. Edit `config.php` with the correct installation directory and database login details
4. Run `importer/importExcel.php` from a web browser to create the database and import all the data
5. You now have the same installation that I do. If you are running on data that is not from UR, then you may have to change some things.

## PHP API Documentation
Available at https://md100play.github.io/university-schedule-generator/html/index.html

## Theory of Operation
Our algorithm is essentially a recursive tree, which is explained more in the image below.
![Theory of Operation](http://mikedombrowski.com/wp-content/uploads/2015/10/illustration.png)
