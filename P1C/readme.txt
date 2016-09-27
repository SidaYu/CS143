In the last part created a practical Movie Database system accessed by users exclusively through a Web interface.
Based on the knowledge of last two parts of project A, it is not difficult to build a essentially functional website with php and mysql. However, I added some styles to my website and some more user-friendly functions to make it more descent.

The details of all my pages are as follows:

Four input pages:

addMovie.php : A page that lets users to add actor and/or director information. some "reasonable" names like Chu-Cheng Hsieh, J'son Lee, etc can also be added without any problem.

addMovie.php : A page that lets users to add movie information including movieGenre(multi-choice).

addComment.php: A page that lets users to add comments to movies. This page is fixed to one movie ID, thus, if u want to add comment to a movie as you choose, u should choose it on the navComment.php and will automatically jump to the right comment-add page.

addMA.php: A page that lets users to add "actor to movie" relation(s).

addMD.php: A page that lets users to add "director to movie" relation(s).

Two browsing pages:

ActorInfo.php : A page that shows actor information.the links to the movies that the actor was in are shown.

MovieInfo.php : A page that shows movie information. links to the actors/actresses that were in this movie, the average score of the movie based on user feedbacks, all user comments and a ”Add Comment" button which links to addComment.php where users can add comments are all included.

One search page:

Search.php : A page that lets users search for an actor/actress/movie through a keyword search interface. As for the output, I sort them in two table that you could find an item easily.


the Index.php is the main page of my web side. It is not tough to operate my web system. all the functions and links to them are listed both on the sidebar and navigation bar. you can just follow it and do some job on the website.

Thanks~