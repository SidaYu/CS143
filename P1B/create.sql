create table Movie (
  id int not null , /* the primary key can not be null */
  title varchar(100) not null , /* every movie must have a title */
  year int ,
  rating varchar(10) ,
  company varchar(50) ,
  primary key (id) , /* set id as the primary key of table Movie */
  CHECK(year > 1900) /* the movie must be produced after 1900 */
  )ENGINE=INNODB;

create table Actor (
  id int not null , /* the primary key can not be null */
  last varchar(20) not null , /* every Actor must have a last name */
  first varchar(20) not null , /* every Actor must have a first name */
  sex varchar(6) not null , /* every Actor must have a feature of sex */
  dob date not null , /* every Actor must have a date of birth */
  dod date ,
  primary key (id) , /* set id as the primary key of table Movie */
  CHECK(sex in ('Male','Female')) /* the choice of sex must be 'Male' or 'Female' */
  )ENGINE=INNODB;

create table Director (
  id int not null , /* the primary key can not be null */
  last varchar(20) not null , /* every Director must have a last name */
  first varchar(20) not null , /* every Director must have a first name */
  dob date not null , /* every Director must have a date of birth */
  dod date ,
  primary key (id) /* set id as the primary key of table Movie */
  )ENGINE=INNODB;

create table MovieGenre (
  mid int not null , /* every MovieGenre relation must have mid and genre */
  genre varchar(20) not null , /* every MovieGenre relation must have mid and genre */
  primary key (mid, genre) , /* set mid,genre as the primary key of table MovieGenre */
  FOREIGN KEY (mid) references Movie(id) /* the key mid be referenced to the id in table Movie */
  ) ENGINE=INNODB;

create table MovieDirector (
  mid int not null , /* every MovieDirector relation must have mid and did */
  did int not null , /* every MovieDirector relation must have mid and did */
  primary key (mid, did) , /* set mid,did as the primary key of table MovieDirector */
  FOREIGN KEY (mid) references Movie(id) , /* the key mid be referenced to the id in table Movie */
  FOREIGN KEY (did) references Director(id) /* the key did be referenced to the id in table Director */
  ) ENGINE=INNODB;

create table MovieActor (
  mid int not null , /* every MovieActor relation must have mid and aid */
  aid int not null , /* every MovieActor relation must have mid and aid */
  role varchar(50) ,
  primary key (mid, aid) , /* set mid,aid as the primary key of table MovieActor */
  FOREIGN KEY (mid) references Movie(id) , /* the key mid be referenced to the id in table Movie */
  FOREIGN KEY (aid) references Actor(id) /* the key aid be referenced to the id in table Actor */
  ) ENGINE=INNODB;

create table Review (
  name varchar(20) ,
  time timestamp not null , /* every review must have a timestamp */
  mid int not null , /* every review must be related to a movie id */
  rating int ,
  comment varchar(500) ,
  FOREIGN KEY (mid) references Movie(id) , /* the key aid be referenced to the id in table Movie */
  CHECK(rating >= 0 and rating <= 5) /* the rating must be at the range of 0-5 */
  ) ENGINE=INNODB;

create table MaxPersonID (
  id int )ENGINE=INNODB;

create table MaxMovieID (
  id int )ENGINE=INNODB;