/**
* There are totally 15 constraints as follows.
*/

/*--------------------------------------------------------------------------------------------*/
/* 6 primary key constraints, which means this key cannot be duplicated or be null*/

INSERT INTO Movie VALUES (2,'Test',2015,'R','Test');
/**
* Violate the primary key constraint of id in Movie;
* ERROR 1062 (23000): Duplicate entry '2' for key 'PRIMARY'
*/

INSERT INTO Actor VALUES (10, 'Test','Test','Male', 19770315 , null);
/**
* Violate the primary key constraint of id in Actor;
* ERROR 1062 (23000): Duplicate entry '10' for key 'PRIMARY'
*/

INSERT INTO Director VALUES (null, 'Test','Test', 19770315 , null);
/**
* Violate the primary key constraint of id in Director;
* ERROR 1048 (23000): Column 'id' cannot be null
*/

INSERT INTO MovieGenre VALUES (3,'Drama');
/**
* Violate the primary key constraint of mid,genre in MovieGenre;
* ERROR 1062 (23000): Duplicate entry '3-Drama' for key 'PRIMARY'
*/

INSERT INTO MovieDirector VALUES (3,112);
/**
* Violate the primary key constraint of mid,did in MovieDirector;
* ERROR 1062 (23000): Duplicate entry '3-112' for key 'PRIMARY'
*/

INSERT INTO MovieActor VALUES (100,10208,'Test');
/**
* Violate the primary key constraint of mid,aid in MovieActor;
* ERROR 1062 (23000): Duplicate entry '100-10208' for key 'PRIMARY'
*/


/*--------------------------------------------------------------------------------------------*/
/* 6 foreign key constraints, which means a referencing and referenced relation*/

INSERT INTO MovieGenre VALUES (4735,'Test');
/**
* Violate the foreign key constraint of mid in Director(referencing to Movie.id);
* ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails 
* (`TEST`.`MovieGenre`, CONSTRAINT `MovieGenre_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))
*/

INSERT INTO MovieDirector VALUES (4735,16);
/**
* Violate the foreign key constraint of mid in MovieDirector(referencing to Movie.id);
* ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails 
* (`TEST`.`MovieDirector`, CONSTRAINT `MovieDirector_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))
*/

INSERT INTO MovieDirector VALUES (4734,1);
/**
* Violate the foreign key constraint of did in MovieDirector(referencing to Director.id);
* ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails 
* (`TEST`.`MovieDirector`, CONSTRAINT `MovieDirector_ibfk_2` FOREIGN KEY (`did`) REFERENCES `Director` (`id`))
*/

INSERT INTO MovieActor VALUES (4735,10,'Test');
/**
* Violate the foreign key constraint of mid in MovieActor(referencing to Movie.id);
* ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails
* (`TEST`.`MovieActor`, CONSTRAINT `MovieActor_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))
*/

INSERT INTO MovieActor VALUES (4734,5,'Test');
/**
* Violate the foreign key constraint of aid in MovieActor(referencing to Actor.id);
* ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails 
* (`TEST`.`MovieActor`, CONSTRAINT `MovieActor_ibfk_2` FOREIGN KEY (`aid`) REFERENCES `Actor` (`id`))
*/

INSERT INTO Review VALUES ('Test',20021114093723,4735,5,'Test');
/**
* Violate the foreign key constraint of mid in Review(referencing to Movie.id);
* ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails 
* (`TEST`.`Review`, CONSTRAINT `Review_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))
*/

DELETE FROM Movie WHERE id = 4734; 
/**
* Violate the foreign key constraint, this is a referenced key and cannot be deleted);
* ERROR 1451 (23000): Cannot delete or update a parent row: a foreign key constraint fails 
* (`TEST`.`MovieGenre`, CONSTRAINT `MovieGenre_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))
*/

/*--------------------------------------------------------------------------------------------*/
/* 3 CHECK constraints, which are omited by this sql version, thus there are no ERROR info.*/

INSERT INTO Movie VALUES (5000,'Test',1800,'R','Test');
/**
* Violate the check constraint, 1800 < 1900, false);
*/

INSERT INTO Actor VALUES (70000, 'Test','Test','malefemale', 19770315 , null);
/**
* Violate the check constraint, sex key must be 'Male' or 'Female');
*/

INSERT INTO Review VALUES ('Test',20021114093723,4734,10,'Test');
/**
* Violate the check constraint, the rating 10 > 5, false);
*/






