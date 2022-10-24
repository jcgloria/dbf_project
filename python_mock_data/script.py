import mysql.connector
from random import choice, randint, random
from datetime import datetime

#If venv doesnt work, just run pip3 install mysql-connector-python. All other libraries are standard. 

#Connection to Mysql. Make sure port is correct. 
cnx = mysql.connector.connect(user='root', password='root',
                              host='localhost',
                              port=8889,
                              database='dbf_project')

cursor = cnx.cursor()                    

#Create users
users = []
file = open('users.csv', 'r')
for line in file:
    values = line.split(',')
    add_user = ("INSERT INTO Users "
               "(username, seller, pass, email) "
               "VALUES (%s, %s, %s, %s)")
    data_user = (values[0], True if values[1] == 'true' else False, values[2], values[3])
    cursor.execute(add_user, data_user)
    users.append(values[0])

#Create categories
file = open('categories.csv', 'r').read()
categories = file.split(',')
add_category = ("INSERT INTO Categories "
               "(category) "
               "VALUES (%s)")
for category in categories:
    cursor.execute(add_category, (category,))

#Create Auctions
file = open('auctions.csv', 'r')
auctions = []
for line in file:
    values = line.split(',')
    add_auctions = ("INSERT INTO Auctions "
               "(username, title, details, category, startingPrice, reservePrice, endDate) " 
               "VALUES (%s, %s, %s, %s, %s, %s, %s)")
    user = choice(users)
    category = choice(categories)
    data_auctions = (user, values[0], values[1], category, values[2], 0 if not values[3] else values[3], datetime.strptime(values[4].replace('\n', ''),"%Y-%m-%dT%H:%M:%SZ"))
    cursor.execute(add_auctions, data_auctions)
    auctions.append({'auctionId': cursor.lastrowid,'username': user, 'startingPrice':float(values[2])})

#Create Bids
for i in auctions:
    numBids = randint(0,15) #the amount of bids we are creatring for this specific auction (number from 0 to 14)
    currentBid = i['startingPrice']
    for j in range(numBids):
        currentBid = round(currentBid*(1+random()),2) #increase each bid by some random precentage
        add_bid = ("INSERT INTO Bids "
               "(username, auctionId, bidPrice) " 
               "VALUES (%s, %s, %s)")
        while True:
            user = choice(users)
            if user != i['username']: #pick a user thats not the auction's user. 
                break
        data_bid = (user, i['auctionId'], currentBid)
        cursor.execute(add_bid, data_bid) 

#Create watchlists
watchlists = []
for i in range(50): #lets create 50 watchlist records
    auction = choice(auctions)
    while True:
        user = choice(users)
        if user != auction['username']: #pick a user thats not the auction's user. 
            break
    add_watchlist = ("INSERT INTO Watchlist "
               "(auctionId, username) " 
               "VALUES (%s, %s)")
    data_watchlist = (auction['auctionId'], user)
    if data_watchlist in watchlists:
        break #do not add this if it's already in the database
    watchlists.append(data_watchlist)
    cursor.execute(add_watchlist, data_watchlist)
    

cnx.commit()
cnx.close()
