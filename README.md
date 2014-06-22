bug-o-meter
===========

bug-o-meter for mozilla bug squasing event


## Requirement

Yous need to have BeatifulSoup 4 on your computer
``` sh
sudo apt-get install python-setuptools && sudo easy_install beautifulsoup4
```

## How to use

You have to create two differents files (in this code, whe use etherpad). One with Bugzilla mail and one width Github nickname, one by line each time.

Put this file online (or not) and change line 74 and 85 of bugzilla-collect.py

Then launch this script :

``` sh
 while : ; do python bugzilla-collect.py 2014-06-21 2014-06-22 > bugs.json.tmp && mv bugs.json.tmp bugs.json ; sleep 60; done
``` 

You have to put the adresse of bugs.json on line 42 of index.html (you may have to protect of CORS).

It's all done !
