### Summary
I'm an avid fan of Nerd Nite and I evangelize it at every opportunity. Often people come up to me and ask "What are the topics for the next talk?" I usually can't remember, so I have to look it up manually. On the phone, this is a pain in the ass!

Furthermore, each of the Nerd Nite sites are laid out slightly differently, and it's difficult to find only the most relevant info: dates, topics and ticket links.

To that end, I've developed an API for Nerd Nite that essentially just scrapes all of the known Nerd Nite sites and searches for the relevant info. It's not perfect, but it works for my needs.

### Goals
Eventually, I'd like to be able to develop a mobile app or something that does push notifications to notify me when:

* New events are posted
* Advance tickets are available (if at all)
* Topics are announced or changed
* Locations change

### How it works
Right now we basically pull in the HTML for each page and then run it through a series of regular expressions to determine topics, dates, etc. There are huge problems with this, but since the Nerd Nite administrators aren't exactly uber web developers, it's the best I can do at the moment. Totally open to better ideas.

### How to use 
Simply go to nerdnite.brianseitel.com and you will see the API results in JSON format.

### Running this locally
It's pretty straightforward:

* Set up a *AMP stack
* Set up a database named whatever you want (I use oasis_nerdnite to follow my naming conventions)
* Create a nerdnitepass.php file one level up from repo path. It should contain the following constants: ```DB_HOST```, ```DB_USER```, ```DB_PASS```, ```DB_NAME```, ```DB_PORT```. These are how we connect to the DB
* ```api.php``` displays the API results in JSON format
* ```report.php``` displays an HTML report of the data
* ```scrape.php``` scrapes all known Nerd Nite sites and pulls the data together
* ```setup.php``` sets up the tables in the DB for you

### Caveats
* I wrote this in about an hour and a half on a Friday afternoon. This is not a "best practices" thing. It simply works. If you feel that the file structure could be improved, feel free to fork and send me a pull request with your changes. I'm open to OOP or using a microframework or wahtever. I don't really care, as long as it improves the efficiency of the API.
* If you are a mobile developer and want to develop a sexy looking app using this API, please let me know and I'll plug the shit out of it. I'll even use the app personally!

### Credits
Special thanks to @dancrew32 for inspiration and support.
