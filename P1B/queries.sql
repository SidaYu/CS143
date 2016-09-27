/*the names of all the actors in the movie 'Die Another Day'*/
select CONCAT_WS(" ",first,last) as ActorName from Movie M,Actor A,MovieActor MA where M.title = 'Die another Day' and A.id=MA.aid and M.id = MA.mid;

/*the count of all the actors who acted in multiple movies*/
select count(*) as numberOfActor from (select aid,count(*) from MovieActor group by aid having count(*)>1) S;

/*the name of the actor who acted in most movies*/
select S.aid, CONCAT_WS(" ",Actor.first,Actor.last) as ActorName, S.movieNumber
from (select aid, count(*) as movieNumber from MovieActor group by aid) S, Actor
where S.movieNumber = (select max(M.movieNumber) from (select aid, count(*) as movieNumber from MovieActor group by aid) M)
and S.aid = Actor.id; 

