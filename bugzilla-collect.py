#!/usr/bin/env python

import sys
import re
import urllib2
from bs4 import BeautifulSoup
from collections import Counter
import json

def collectReports(doc):
    tableElem = doc.find_all(id="report")[0]
    headerElem = doc.find_all(id="report-header")[0]
    headerElems = headerElem.find_all("th")
    header = [''.join(list(col.stripped_strings)) for col in headerElems]

    rowElems = tableElem.find_all("tr");
    last = None
    reports = []
    stripper = re.compile(r'\s+')
    for row in list(rowElems)[1:]:
        line = {}
        for col, colElem in enumerate(row.find_all("td")):
            content = ''.join(list(colElem.strings))
            if content == u"\xa0" : # "&nbsp;"
                content = last[header[col]]
            else:
                content = content.encode('utf-8')
            line[header[col]] = stripper.sub(' ', content.strip()) # .trim().replace(/[ \n][ \n]+/, ' ');
        last = line
        reports.append(line)

    return reports

def removeUnregisteredEmails(doc, emails):
    missingElems = doc.find_all(attrs={"class": "user_match"})
    if len(missingElems) == 0:
        return emails
    missing = [m.find_all("b")[0] for m in missingElems]
    missing = [''.join(list(e.stripped_strings)) for e in missing]
    emails = [e for e in emails if e not in missing]
    return emails

def queryReport(emails, startDate, endDate):
    base_url = "https://bugzilla.mozilla.org/page.cgi?id=user_activity.html&action=run&who="
    startTag = "&from="
    endTag = "&to="
    sortKind = "&sort=when"
    urlEmails = ''.join(emails)
    urlEmails2 = urlEmails.replace("+", "%2B")
    url = base_url + urlEmails2 + startTag + startDate + endTag + endDate + sortKind
    content = urllib2.urlopen(url).read()
    return BeautifulSoup(content)

def loadValidReport(emails, startDate, endDate):
    doc = queryReport(emails, startDate, endDate)
    newEmails = removeUnregisteredEmails(doc, emails)
    if emails == newEmails:
        return doc
    emails = newEmails
    print emails
    doc = queryReport(emails, startDate, endDate)
    newEmails2 = removeUnregisteredEmails(doc, emails)
    if emails == newEmails2:
        return doc
    print "Cannot reach fix point of valid emails"
    return None
    ### For testing.
    # f = open("fake-replay.html")
    # doc = BeautifulSoup(f.read())
    # f.close()
    # return doc

def readEmails():
    response = urllib2.urlopen('http://etherpad.mozilla.org/ep/pad/export/mbsp-paris-juin-14/latest?format=txt')
    html = response.readlines()
    emails = []
    for line in html:
        if "@" in line and "." in line and "#" not in line:
            emails.append(line.strip())
    response.close()
    lalala = ",".join(emails)
    return lalala

def readEmails2():
    response = urllib2.urlopen('http://etherpad.mozilla.org/ep/pad/export/mbsp-paris-juin-14-gh/latest?format=txt')
    html = response.read()
    f = open("gh-names", "w")
    f.write(html)
    f.close

matchDate = re.compile('^\d{4}-\d{2}-\d{2}$')
if len(sys.argv) < 3:
    raise Exception('./bugzilla-community.py <start-date> <end-date>')

startDate = sys.argv[1]
endDate = sys.argv[2]
if not matchDate.match(startDate) or not matchDate.match(endDate):
    raise Exception('Expected date format: yyyy-mm-dd')

doc = loadValidReport(readEmails(), startDate, endDate)
 
reports = collectReports(doc)

commentRe = re.compile('^Comment')
askReviewRe = re.compile('^review[?]')
giveReviewRe = re.compile('^review[-+]')
ccre = re.compile('^CC')

newAttachement = [r for r in reports if r["Removed"] == "(new attachment)"]
newBugs = [r for r in reports if r["Removed"] == "(new bug)"]
askReview = [r for r in reports if askReviewRe.match(r["Added"])]
giveReview = [r for r in reports if giveReviewRe.match(r["Added"]) or askReviewRe.match(r["Removed"])]
comment = [r for r in reports if commentRe.match(r["What"])]
cc = [r for r in reports if ccre.match(r["What"])]

def printWhoLists(name, reports, out):
  out[name].extend(reports);

out = {"attachments": [], "new":[], "review_asked":[], "review_given": [], "comments":[], "cc": []}
printWhoLists("attachments", newAttachement, out)
printWhoLists("new", newBugs, out)
printWhoLists("review_asked", askReview, out)
printWhoLists("review_given", giveReview, out)
printWhoLists("cc", cc, out)
printWhoLists("comments", comment, out)
print json.dumps(out)

readEmails2()
